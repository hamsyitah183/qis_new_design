<?php

namespace App\Http\Controllers\internal;

use App\Events\ApplicationDeleted;
use App\Events\PublicUserEvent;
use App\Http\Controllers\Controller;
use App\Models\InternalUser;
use App\Models\IpApplication;
use App\Models\IpCondition;
use App\Models\IpConsignmentPermit;
use App\Models\IpEntryPoint;
use App\Models\PublicCode;
use App\Models\PublicUser;
use App\Notifications\ApplicationNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class MiscController extends Controller
{
    public function showcontrolpanel()
    {
        return view('pages.internal.misc.control_panel');
    }

    public function showStateDistrictManagement()
    {
        return view('pages.internal.misc.state_district_management');
    }

    public function getpbdata($cate)
    {
        if ($cate === 'district_entry') {
            $pbdata = PublicCode::where('cate_name', $cate)
                ->where('is_del', false)
                ->get()
                ->map(function ($district) {
                    $district->places = IpEntryPoint::where('district', $district->cate_code)->where('is_del', false)->get();

                    return $district;
                });

            // dd('district', $pbdata);
        } else {
            $pbdata = PublicCode::select('id', 'cate_name', 'cate_code', 'description')->where('cate_name', $cate)->where('is_del', false)->get();
        }

        return response()->json([
            'status' => 'success',
            'data' => $pbdata,
        ]);
    }

    public function getspecificpbdata($id)
    {
        $pbdata = PublicCode::select('id', 'cate_name', 'cate_code', 'description')->findorFail($id);

        return response()->json([
            'status' => 'success',
            'data' => $pbdata,
        ]);
    }

    public function updatepbdata(Request $request)
    {
        $id = $request->input('id');
        $code = $request->input('item_code');
        $desc = $request->input('item_desc');

        $pbdata = PublicCode::findorFail($id);
        $pbdata->cate_code = $code;
        $pbdata->description = $desc;
        $pbdata->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Public code updated successfully.',
        ]);
    }

    public function deletepbdata($id)
    {
        $pbdata = PublicCode::findorFail($id);
        $pbdata->is_del = true;
        $pbdata->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Public code deleted successfully.',
        ]);
    }

    public function addpbdata(Request $request)
    {
        // dd($request->all());
        $cate = $request->input('category');
        $code = $request->input('item_code');
        $desc = $request->input('item_desc');
        // dd($cate,$code,$desc);

        if ($code == null || $code == '') {
            $getcode = PublicCode::where('cate_name', $cate)->max('cate_code');
            $code = $getcode + 1;
        }

        if($cate != 'unit_measurement') {
            $pbdata = new PublicCode();
            $pbdata->cate_name = $cate;
            $pbdata->cate_code = $code;
            $pbdata->description = $desc;
            $pbdata->is_del = false;
            $pbdata->save();
        }

       

        return response()->json([
            'status' => 'success',
            'message' => 'Public code added successfully.',
        ]);
    }

    public function showpermitcondition()
    {
        return view('pages.internal.misc.permit_list');
    }

    public function permitaddcondition()
    {
        $pbdata = PublicCode::select('id', 'cate_name', 'cate_code', 'description')->where('cate_name', 'condition_category')->where('is_del', false)->get();

        return view('pages.internal.misc.permit_add', compact('pbdata'));
    }

    public function saveCondition(Request $request)
    {
        $request->validate([
            'itemName' => 'required|string',
            'itemCategory' => 'required|integer',
            'permit_condition' => 'required|string',
        ]);

        // Decode Tagify arrays
        $countryArr = json_decode($request->countryTag, true) ?? [];
        $usageArr   = json_decode($request->usageTags, true) ?? [];

        $countryValues = array_map(fn($i) => $i['value'] ?? ($i['name'] ?? null), $countryArr);
        $usageValues   = array_map(fn($i) => $i['value'] ?? ($i['name'] ?? null), $usageArr);

        $data = [
            'category'           => $request->itemCategory,
            'item_name'          => $request->itemName,
            'addional_condition' => $request->permit_condition,
            'quantity_limit'     => $request->quanLimit ?: null,
            'date_limit'         => $request->spedate ?: null,
            'country'            => $countryValues,
            'usage'              => $usageValues,
        ];

        // UPDATE
        if ($request->filled('id')) {
            $ipCondition = IpCondition::find($request->id);

            if (!$ipCondition) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'IP Condition not found'
                ], 404);
            }

            $ipCondition->update($data);

            return response()->json([
                'status' => 'success',
                'message' => 'IP Condition updated successfully'
            ]);
        }

        // CREATE
        $ipCondition = IpCondition::create($data);

        return response()->json([
            'status' => 'success',
            'message' => 'IP Condition created successfully',
            'data' => $ipCondition
        ]);
    }


    public function editCondition($id)
    {
        $condition = IpCondition::with(['code'])->findOrFail($id);

        $pbdata = PublicCode::select('id', 'cate_name', 'cate_code', 'description')->where('cate_name', 'consignment_purpose')->where('is_del', false)->get();

        return view('pages.internal.misc.permit_edit', compact('condition', 'pbdata'));
    }

    public function getpermitconditiondata()
    {
        $conditions = IpCondition::with(['code', 'condcategory'])
            ->select('id', 'item_name', 'category', 'usage', 'country')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $conditions,
        ]);
    }

    public function getpermitconditionbyid($id)
    {
        $conditions = IpCondition::with(['code', 'condcategory'])
            ->select('id', 'item_name', 'category', 'usage', 'addional_condition', 'quantity_limit', 'date_limit', 'country')
            ->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data' => $conditions,
        ]);
    }

    function accept_permit($id, Request $request)
    {
        $accepted = $request->input('accepted');
        $status = '';

        $permit = IpConsignmentPermit::findOrFail($id);

        $permit->permit_number = 'IPO/' . now()->format('ymd') . rand(1000, 9999);

        $application = $permit->application;

        if ($accepted == 1) {
            $permit->status = 'pending for payment';
            $status = 'Pending for Payment';
        } else {
            $permit->status = 'rejected';
            $status = 'Rejected';
            $permit->remark = $request['reason'];
        }
        $permit->save();

        $allStatuses = IpConsignmentPermit::where('application_id', $permit->application->id)->pluck('status'); // gets a collection of all statuses

        $url = '/view_application' . '/' . $permit->application->application_id;

        // Events & notifications
        event(new ApplicationDeleted('Permit in ' . $permit->application->application_id . ' is ' . $status));

        $users = InternalUser::role(['admin', 'officer', 'superadmin'])->get();
        Notification::send($users, new ApplicationNotification('A permit with application ID ' . $permit->application->application_id . ' has been ' . $status, authUser()['user']->fullname, $url));

        $user = PublicUser::where('uuid', $permit->application->user_id)->first();

        event(new PublicUserEvent('A permit in application with ID ' . $permit->application->application_id . ' has been ' . $status, $user->uuid));

        Notification::send($user, new ApplicationNotification('A permit in application with ID ' . $permit->application->application_id . ' has been ' . $status, authUser()['user']->fullname, $url));

        $application->logActivity(action: 'Officer Verification', remark: $request['reason'] ?? 'Permit approved by officer', status: 'Officer Verified');

        // Check if no status is 'processing'
        if (!$allStatuses->contains('processing')) {
            // dd($allStatuses);
            $application->logActivity(action: 'Fully Processed', remark: 'Fully Processed', status: 'Fully Processed');

            // dd($application);

            $application->status = 'Fully Processed';
            $application->save();
        }

        $permit->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Permit condition updated successfully.',
        ]);
    }
    public function updateEntry(Request $request)
    {
        $districtId = $request->input('district_id');
        $places = $request->input('places', []);
        $transportTypes = $request->input('transport_types', []);

        if (!$districtId) {
            return response()->json(
                [
                    'status' => 'error',
                    'message' => 'District ID is required.',
                ],
                422,
            );
        }

        DB::transaction(function () use ($districtId, $places, $transportTypes) {
            // 1️⃣ Soft delete all existing entry points for this district
            IpEntryPoint::where('district', $districtId)->update(['is_del' => true]);

            // 2️⃣ Insert/update the new list of places with transport type
            foreach ($places as $index => $place) {
                $place = trim($place);
                $type = $transportTypes[$index] ?? 'land'; // default to land if empty

                if (empty($place)) {
                    continue;
                }

                // Check if the place already exists (soft-deleted)
                $entry = IpEntryPoint::where('district', $districtId)->where('entry_name', $place)->first();

                if ($entry) {
                    // Restore if it was soft-deleted and update transport type
                    $entry->is_del = false;
                    $entry->transport_type = $type;
                    $entry->save();
                } else {
                    // Create new entry
                    IpEntryPoint::create([
                        'district' => $districtId,
                        'entry_name' => $place,
                        'transport_type' => $type,
                        'is_del' => false,
                    ]);
                }
            }
        });

        return response()->json([
            'status' => 'success',
            'message' => 'Entry points updated successfully.',
        ]);
    }
}
