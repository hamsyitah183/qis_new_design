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
use App\Models\InternalUser;
use App\Models\PublicUser;
use App\Models\TempAttachment;
use App\Notifications\ApplicationNotification;
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
            $isNewApplication = false;
            
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
                $isNewApplication = true;
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

            // inspection activity log
            if ($isDraft) {
                $application->logActivity(
                    action: $isNewApplication ? 'Draft Created' : 'Draft Updated',
                    remark: $isNewApplication ? 'Inspection application saved as draft' : 'Inspection application draft updated',
                    status: 'Draft'
                );
            } else {
                $application->logActivity(
                    action: $isNewApplication ? 'Submitted' : 'Updated',
                    remark: $isNewApplication ? 'Inspection application submitted' : 'Inspection application updated and submitted',
                    status: 'Pending'
                );
            }

            DB::commit();

            // inspection send notifications
            $notificationUrl = route('public.viewInspectionApplication', ['id' => $application->application_id]);

            $internalUsers = InternalUser::role(['admin', 'clerk'])->get();
            $internalMsg = $isDraft
                ? ($isNewApplication ? 'New Inspection Certificate draft created' : 'Inspection Certificate draft updated')
                : ($isNewApplication ? 'New Inspection Certificate application submitted' : 'Inspection Certificate application updated');

            Notification::send(
                $internalUsers,
                new ApplicationNotification($internalMsg, Auth::user()->fullname, $notificationUrl)
            );

            event(new InternalUserAdminEvent($internalMsg . ' by ' . (Auth::user()->fullname ?? 'Unknown User')));
            event(new InternalUserClerkEvent($internalMsg . ' by ' . (Auth::user()->fullname ?? 'Unknown User')));

            $applicant = PublicUser::where('uuid', $application->user_id)->first();
            if ($applicant) {
                $applicantMsg = $isDraft
                    ? 'Your Inspection Certificate Application with id ' . $application->application_id . ' is saved as draft'
                    : 'Your Inspection Certificate Application with id ' . $application->application_id . ' is submitted';

                $applicant->notify(new ApplicationNotification($applicantMsg, 'QIS', $notificationUrl));
                event(new PublicUserEvent($applicantMsg, $applicant->uuid));
            }

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
            ->addColumn('category', function ($row) {
                return $row->category_application == 1 ? 'Apply For Others' : 'Self Apply';
            })
            ->addColumn('importer', function ($row) {
                if (!empty($row->importer_detail) && is_array($row->importer_detail)) {
                    return $row->importer_detail['fullname'] ?? $row->importer_detail['name'] ?? '-';
                }
                if ($row->importer) {
                    return $row->importer->fullname ?? '-';
                }
                return '-';
            })
            ->addColumn('exporter', fn ($row) => $row->exporter->name ?? '-')
            ->addColumn('eta', fn ($row) => $row->eta ? $row->eta->format('d M Y') : '-')
            ->addColumn('transport_type', fn ($row) => ucfirst($row->transport_type) ?? '-')
            ->addColumn('entry_point', fn ($row) => $row->entryPoint->entry_name ?? '-')
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
            ->addColumn('action', function ($row) use ($type) {
                $status = ucfirst($row->status);

                // Determine if we should show Edit or View
                // Internal users ALWAYS view. Public users view unless Draft/Pending.
                $showEdit = ($type === 'public' && ($status === 'Draft' || $status === 'Pending'));

                if ($showEdit) {
                    if ($row->category_application == 1) {
                        $url = route('public.inspectionApplicationOthers', ['id' => $row->application_id]);
                    } else {
                        $url = route('public.inspectionApplicationSelf', ['id' => $row->application_id]);
                    }
                    $icon = 'ti ti-edit';
                    $viewEditTitle = 'Edit';
                } else {
                    // Use internal route for internal users, public route for public users
                    if ($type === 'internal') {
                        $url = route('internal.viewInspectionApplication', ['id' => $row->application_id]);
                    } else {
                        $url = route('public.viewInspectionApplication', ['id' => $row->application_id]);
                    }
                    $icon = 'ti ti-eye';
                    $viewEditTitle = 'View';
                }

                $buttons = '<a class="btn btn-sm btn-primary me-1" href="' . $url . '" title="' . $viewEditTitle . '" data-bs-toggle="tooltip" data-bs-placement="top">
                                <i class="' . $icon . '"></i>
                            </a>';

                // Extra admin controls for internal users
                if ($type === 'internal') {
                    if ($status === 'Pending') {
                        $buttons .= '<button class="btn btn-sm btn-success me-1 inspection-approve" data-id="' . $row->application_id . '" title="Approve" data-bs-toggle="tooltip" data-bs-placement="top">
                                        <i class="ti ti-check"></i>
                                     </button>';
                        $buttons .= '<button class="btn btn-sm btn-warning me-1 inspection-reject" data-id="' . $row->application_id . '" title="Reject" data-bs-toggle="tooltip" data-bs-placement="top">
                                        <i class="ti ti-x"></i>
                                     </button>';
                    }
                }

                $buttons .= '<button class="btn btn-sm btn-danger deleteInspection" data-id="' . $row->application_id . '" title="Delete" data-bs-toggle="tooltip" data-bs-placement="top">
                                <i class="ti ti-trash"></i>
                             </button>';

                return $buttons;
            })
            ->rawColumns(['status', 'action'])
            ->make(true);
    }

        /**
     * Update inspection application status (Approved / Rejected) for internal users.
     */
    public function updateStatus($id, Request $request)
    {
        $status = $request->input('status');

        if (!in_array($status, ['Approved', 'Rejected'], true)) {
            return response()->json([
                'message' => 'Invalid status value.',
            ], 422);
        }

        $application = InspectionApplication::where('application_id', $id)->firstOrFail();
        $application->status = $status;
        $application->save();

        // activity log
        $application->logActivity(
            action: $status,
            remark: "Inspection application {$status} by internal user",
            status: $status
        );

        // notifications
        $notificationUrl = route('public.viewInspectionApplication', ['id' => $application->application_id]);
        $internalUsers = InternalUser::role(['admin', 'clerk'])->get();
        $internalMsg = "Inspection application {$application->application_id} has been {$status}";
        Notification::send($internalUsers, new ApplicationNotification($internalMsg, authUser()['user']->fullname, $notificationUrl));

        $applicant = PublicUser::where('uuid', $application->user_id)->first();
        if ($applicant) {
            $applicantMsg = "Your inspection application with id {$application->application_id} has been {$status}";
            $applicant->notify(new ApplicationNotification($applicantMsg, 'QIS', $notificationUrl));
            event(new PublicUserEvent($applicantMsg, $applicant->uuid));
        }

        return response()->json([
            'message' => "Inspection application {$status} successfully.",
        ]);
    }

    /**
     * Delete an inspection application and its related data (internal only).
     */
    public function deleteApplication($id)
    {
        $userData = authUser();
        $user = $userData['user'];
        $type = $userData['type'];

        return DB::transaction(function () use ($id, $user, $type) {
            $application = InspectionApplication::where('application_id', $id)->firstOrFail();
            $applicationId = $application->application_id;
            $applicantUuid = $application->user_id;

            // Authorization: public users can only delete their own
            if ($type === 'public' && $application->user_id !== $user->uuid && $application->importer_id !== $user->uuid) {
                return response()->json([
                    'message' => 'Unauthorized to delete this application.',
                ], 403);
            }

            $items = \App\Models\InspectionItem::where('application_id', $application->id)->get();

            if ($items->isNotEmpty()) {
                $itemIds = $items->pluck('id');

                $attachments = \App\Models\InspectionAttachment::whereIn('item_id', $itemIds)->get();

                foreach ($attachments as $attachment) {
                    if ($attachment->file_path) {
                        $path = str_replace('/storage/', '', $attachment->file_path);

                        if (Storage::disk('public')->exists($path)) {
                            Storage::disk('public')->delete($path);
                        }
                    }

                    $attachment->delete();
                }

                \App\Models\InspectionItem::whereIn('id', $itemIds)->delete();
            }

            $application->delete();

            // activity log and notifications
            $application->logActivity(
                action: 'Deleted',
                remark: 'Inspection application deleted',
                status: 'Deleted'
            );

            $notificationUrl = route('public.showallinspectionlist');
            $internalUsers = InternalUser::role(['admin', 'clerk'])->get();
            Notification::send(
                $internalUsers,
                new ApplicationNotification("Inspection application {$applicationId} has been deleted", authUser()['user']->fullname, $notificationUrl)
            );

            $applicant = PublicUser::where('uuid', $applicantUuid)->first();
            if ($applicant) {
                $applicantMsg = "Your inspection application with id {$applicationId} has been deleted";
                $applicant->notify(new ApplicationNotification($applicantMsg, 'QIS', $notificationUrl));
                event(new PublicUserEvent($applicantMsg, $applicant->uuid));
            }

            return response()->json([
                'message' => 'Inspection application and all attachments deleted successfully.',
            ]);
        });
    }

    public function viewApplication($id)
    {
        $application = InspectionApplication::with(['exporter', 'importer', 'entryPoint', 'inspectionItems.attachments'])
            ->where('application_id', $id)
            ->firstOrFail();

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
