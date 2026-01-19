<?php

namespace App\Http\Controllers;

use App\Models\InspectionApplication;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Models\country;
use App\Models\PublicCode;

use App\Events\ApplicationCreatedInternalUser;
use App\Events\ApplicationCreatedPublicUser;
use App\Events\InternalUserAdminEvent;
use App\Events\InternalUserClerkEvent;
use App\Events\PublicUserEvent;
use App\Models\ImportPermitLog;
use App\Models\IpCondition;
use App\Models\IpConsignmentAttachment;
use App\Models\IpConsignmentPermit;
use App\Models\PublicUser;
use App\Models\TempAttachment;
use App\Notifications\ApplicationNotification;
use App\Services\ApplicationActivityLogger;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class InspectionController extends Controller
{
    //
    function getInspectionSelf($id = null)
    {
        $pubmeasure = PublicCode::where('cate_name', 'unit_measurement')->get();
        $pubpurpose = PublicCode::where('cate_name', 'consignment_purpose')->get();
        $country = Country::where('is_del', false)->get();
        return view('pages.public.inspection_self', compact('pubmeasure', 'pubpurpose', 'country', 'id'));
    }

    function getInspectionOthers($id = null)
    {
        $pubmeasure = PublicCode::where('cate_name', 'unit_measurement')->get();
        $pubpurpose = PublicCode::where('cate_name', 'consignment_purpose')->get();
        $country = Country::where('is_del', false)->get();
        return view('pages.public.inspection_others', compact('pubmeasure', 'pubpurpose', 'country', 'id'));
    }

    public function saveApplication(Request $request)
    {
        DB::beginTransaction();
        $movedFiles = [];

        try {
            $applicationUuid = $request->input('applicationId');
            $isDraft = $request->boolean('is_draft');
            
            // Decode JSON data from frontend
            $exporter = $request->exporterData ? json_decode($request->exporterData, true) : null;
            $importer = $request->importerData ? json_decode($request->importerData, true) : null;
            $permit   = $request->permitDetails ? json_decode($request->permitDetails, true) : [];

            // Determine status
            if ($isDraft) {
                $status = 'Draft';
            } else {
                $status = 'Pending';
            }

            if ($applicationUuid) {
                $application = InspectionApplication::where('application_id', $applicationUuid)->firstOrFail();
                $application->update([
                    'eta'                  => $permit['eta'] ?? null,
                    'transport_type'       => $permit['tranType'] ?? null,
                    'entry_point'          => $permit['entrypoint'] ?? null,
                    'category_application' => $permit['applCate'] ?? null,
                    'user_id'              => Auth::user()->uuid,
                    'exporter_id'          => $exporter['id'] ?? null,
                    'importer_id'          => $importer['uuid'] ?? null,
                    'importer_detail'      => $importer ?? [],
                    'status'               => $status,
                ]);
            } else {
                $application = InspectionApplication::create([
                    'application_id'       => Str::uuid(),
                    'eta'                  => $permit['eta'] ?? null,
                    'transport_type'       => $permit['tranType'] ?? null,
                    'entry_point'          => $permit['entrypoint'] ?? null,
                    'category_application' => $permit['applCate'] ?? null,
                    'user_id'              => Auth::user()->uuid,
                    'exporter_id'          => $exporter['id'] ?? null,
                    'importer_id'          => $importer['uuid'] ?? null,
                    'importer_detail'      => $importer ?? [],
                    'status'               => $status,
                ]);
            }

            $appId = $application->id;

            // Handle items (clear existing if updating?) - for simplicity and based on similar patterns
            if ($applicationUuid) {
                \App\Models\InspectionItem::where('application_id', $appId)->delete();
            }

            if ($request->has('items')) {
                $itemArray = [];
                foreach ($request->items as $index => $item) {
                    $itemData = json_decode($item['data'], true);
                    
                    $inspectionItem = \App\Models\InspectionItem::create([
                        'application_id'     => $appId,
                        'consignment_detail' => $itemData,
                        'quantity'           => $itemData['quantity'] ?? 0,
                        'unit_measurement'   => $itemData['measure'] ?? null,
                        'value'              => $itemData['value'] ?? 0,
                        'purpose'            => $itemData['purpose'] ?? null,
                        'status'             => 'submitted',
                    ]);
                    $itemArray[$index] = $inspectionItem->id;
                }

                // Handle files
                if ($request->hasFile('files')) {
                    foreach ($request->file('files') as $i => $file) {
                        $itemIndex = $request->input('file_item_index')[$i] ?? null;
                        if (isset($itemArray[$itemIndex])) {
                            $name = uniqid() . '_' . $file->getClientOriginalName();
                            $path = $file->storeAs('inspection', $name, 'public');
                            $movedFiles[] = $path;

                            \App\Models\InspectionAttachment::create([
                                'item_id'   => $itemArray[$itemIndex],
                                'file_name' => $file->getClientOriginalName(),
                                'file_path' => "/storage/{$path}",
                                'file_type' => $file->getClientOriginalExtension(),
                            ]);
                        }
                    }
                }
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => $isDraft ? 'Draft saved successfully' : 'Application submitted successfully',
                'application_id' => $application->application_id
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            foreach ($movedFiles as $file) {
                Storage::disk('public')->delete($file);
            }
            \Log::error("Error saving inspection application: " . $e->getMessage());
            
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to save application: ' . $e->getMessage()
            ], 500);
        }
    }

    public function showAllInspectionList()
    {
        return view('pages.public.inspection_list');
    }

    public function getAllInspectionList()
    {
        $userData = authUser();
        $user = $userData['user'];
        $userUuid = $user->uuid;
        $type = $userData['type'];
        
        $query = InspectionApplication::with(['exporter', 'user', 'entryPoint']);

        // Filter for public users
        if ($type === 'public') {
             $query->where(function ($q) use ($userUuid) {
                $q->where('user_id', $userUuid)
                  ->orWhere('importer_id', $userUuid);
             });
        }

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('category', function($row) {
                return $row->category_application == 1 ? 'Apply For Others' : 'Self Apply';
            })
            ->addColumn('importer', function($row) {
                if (!empty($row->importer_detail) && is_array($row->importer_detail)) {
                    return $row->importer_detail['fullname'] ?? $row->importer_detail['name'] ?? '-';
                }
                if ($row->importer) {
                    return $row->importer->fullname ?? '-';
                }
                return '-';
            })
            ->addColumn('exporter', fn($row) => $row->exporter->name ?? '-')
            ->addColumn('eta', fn($row) => $row->eta ? $row->eta->format('d M Y') : '-')
            ->addColumn('transport_type', fn($row) => ucfirst($row->transport_type) ?? '-')
            ->addColumn('entry_point', fn($row) => $row->entryPoint->entry_name ?? '-')
            ->addColumn('status', function ($row) {
                $status = ucfirst($row->status);
                $badgeClass = 'bg-secondary';
                
                if ($status === 'Draft') $badgeClass = 'bg-purple';
                elseif ($status === 'Pending') $badgeClass = 'bg-warning';
                elseif ($status === 'Approved') $badgeClass = 'bg-success';
                elseif ($status === 'Rejected') $badgeClass = 'bg-danger';

                $style = '';
                if ($status === 'Draft') {
                    $style = 'style="background-color: #9e5cf7 !important;"';
                }
                // #ffc658
                if ($status === 'Pending') {
                    $style = 'style="background-color: #ffc658 !important;"';
                }
                //#fb4242
                if ($status === 'Approved') {
                    $style = 'style="background-color: #5cf79e !important;"';
                }
                //#fb4242
                if ($status === 'Rejected') {
                    $style = 'style="background-color: #f75c5c !important;"';
                }

                return '<span class="badge ' . $badgeClass . '" ' . $style . '>' . $status . '</span>';
            })
            ->addColumn('action', function ($row) {
                $status = ucfirst($row->status);
                
                if ($status === 'Draft' || $status === 'Pending') {
                    if ($row->category_application == 1) {
                        $url = route('public.inspectionApplicationOthers', ['id' => $row->application_id]);
                    } else {
                        $url = route('public.inspectionApplicationSelf', ['id' => $row->application_id]);
                    }
                    $icon = 'ti ti-edit';
                } else {
                    $url = route('public.viewInspectionApplication', ['id' => $row->application_id]);
                    $icon = 'ti ti-eye';
                }
                
                $view = '<a class="btn btn-sm btn-primary viewApplication" href="' . $url . '">
                            <i class="' . $icon . '"></i>
                         </a>';
                 return $view;
            })
            ->rawColumns(['status', 'action'])
            ->make(true);
    }

    public function viewApplication($id)
    {
        // Placeholder for now, user asked for list but the action needs to go somewhere.
        // I will return a view that I will create next.
        $application = InspectionApplication::where('application_id', $id)->firstOrFail();
        // reusing or creating a new view 'pages.public.inspection_view'
        return view('pages.public.inspection_view', compact('application'));
    }

    public function getApplicationData($id)
    {
        $application = InspectionApplication::with(['exporter', 'importer', 'entryPoint', 'inspectionItems.attachments'])
            ->where('application_id', $id)
            ->firstOrFail();

        return response()->json([
            'status' => 'success',
            'data' => $application
        ]);
    }
}
