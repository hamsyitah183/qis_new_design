<?php

namespace App\Http\Controllers\internal;

use App\Events\ApplicationDeleted;
use App\Events\PublicUserEvent;
use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\InternalUser;
use App\Models\IpApplication;
use App\Models\IpCondition;
use App\Models\IpConsignmentPermit;
use App\Models\IpEntryPoint;
use App\Models\MeasurementUnit;
use App\Models\News;
use App\Models\PublicCode;
use App\Models\PublicUser;
use App\Models\ConsignmentCondition;
use App\Notifications\ApplicationNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Mail;
use App\Mail\QISNewsMail;
use App\Models\Branch;
use Illuminate\Support\Facades\Gate;

class MiscController extends Controller
{
    public function showcontrolpanel()
    {
        // if (auth()->user()->hasRole('boundary officer')) {
        //     abort(403, 'Unauthorized action. Boundary Officers are restricted from this area.');
        // }
        
        Gate::authorize('manage settings');


        return view('pages.internal.misc.control_panel');
    }

    public function showStateDistrictManagement()
    {
        Gate::authorize('manage settings');

        return view('pages.internal.misc.state_district_management');
    }

    public function showBranchManagement()
    {
        return view('pages.internal.misc.branch_management');
    }

    public function getpbdata($cate)
    {
        Gate::authorize('manage settings');

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
        $pbdata = PublicCode::with(['conversion'])
            ->select('id', 'cate_name', 'cate_code', 'description')
            ->findorFail($id);

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
        $conversion = $request->input('conversion');

        $pbdata = PublicCode::with('conversion')->findOrFail($id);

        $pbdata->cate_code = $code;
        $pbdata->description = $desc;
        $pbdata->save(); // save public_code first

        // ✅ Only handle conversion if measurement unit
        if ($pbdata->cate_name === 'unit_measurement') {
            // If conversion record exists → update
            if ($pbdata->conversion) {
                $pbdata->conversion->update([
                    'conversion' => $conversion,
                ]);
            }
            // If not exists → create
            else {
                $pbdata->conversion()->create([
                    'measurement_id' => $pbdata->id,
                    'conversion' => $conversion,
                ]);
            }
        }

        

        activity()
            ->useLog('user_activity')
            ->event('update data')
            ->causedBy(authUser()['user'])
            ->log(authUser()['user']['fullname'] . ' updated ' . $pbdata->description . ' of ' . $pbdata->cate_name);

        return response()->json([
            'status' => 'success',
            'message' => 'Public code updated successfully.',
        ]);
    }

    public function deletepbdata($id)
    {
        Gate::authorize('manage settings');

        $pbdata = PublicCode::findorFail($id);
        $pbdata->is_del = true;
        $pbdata->save();

        activity()
            ->useLog('user_activity')
            ->event('delete data')
            ->causedBy(authUser()['user'])
            ->log(
                authUser()['user']['fullname'] . ' is deleted ' . $pbdata->description . ' from ' . $pbdata->cate_name
            );

        return response()->json([
            'status' => 'success',
            'message' => 'Public code deleted successfully.',
        ]);
    }

    public function addpbdata(Request $request)
    {
        Gate::authorize('manage settings');

        // dd($request->all());
        $cate = $request->input('category');
        $code = $request->input('item_code');
        $desc = $request->input('item_desc');

        if ($code == null || $code == '') {
            $getcode = PublicCode::where('cate_name', $cate)->max('cate_code');
            $code = $getcode + 1;
        }

        if ($cate === 'unit_measurement') {
            $conversion = $request->input('conversion');
        }

        $pbdata = new PublicCode();
        $pbdata->cate_name = $cate;
        $pbdata->cate_code = $code;
        $pbdata->description = $desc;
        $pbdata->is_del = false;
        $pbdata->save();

        if ($cate === 'unit_measurement') {
            $pbdata->conversion()->create([
                'measurement_id' => $pbdata->id,
                'conversion' => $conversion,
            ]);
        }

        activity()
            ->useLog('user_activity')
            ->event('add data')
            ->causedBy(authUser()['user'])
            ->log(
                authUser()['user']['fullname'] . ' is added ' . $pbdata->description . ' to ' . $pbdata->cate_name
            );

        return response()->json([
            'status' => 'success',
            'message' => 'Public code added successfully.',
        ]);
    }

    public function showpermitcondition()
    {
        Gate::authorize('manage settings');

        return view('pages.internal.misc.permit_list');
    }

    public function permitaddcondition()
    {
        Gate::authorize('manage settings');

        $pbdata = PublicCode::select('id', 'cate_name', 'cate_code', 'description')->where('cate_name', 'condition_category')->where('is_del', false)->get();

        $measurementUnit = MeasurementUnit::with('publicCode')->get();

        return view('pages.internal.misc.permit_add', compact('pbdata', 'measurementUnit'));
    }



    public function saveCondition(Request $request)
    {
        Gate::authorize('manage settings');

        $request->validate([
            'itemName' => 'required|string',
            'itemCategory' => 'required|integer',
            'permit_condition' => 'required|string',
        ]);

        // Decode Tagify arrays
        $countryArr = json_decode($request->countryTag, true) ?? [];
        $usageArr = json_decode($request->usageTags, true) ?? [];

        $countryValues = array_map(fn($i) => $i['value'] ?? ($i['name'] ?? null), $countryArr);
        $usageValues = array_map(fn($i) => $i['value'] ?? ($i['name'] ?? null), $usageArr);

        $data = [
            'category' => $request->itemCategory,
            'item_name' => $request->itemName,
            'scientific_name' => $request->scientificName,
            'addional_condition' => $request->permit_condition,
            'quantity_limit' => $request->quanLimit ?: null . ' ' . $request->measurement ?: null,
            // 'date_limit' => $request->spedate ?: null,
            'start_date' => $request->start_date ?: null,
            'end_date' => $request->end_date ?: null,
            'country' => $countryValues,
            'usage' => $usageValues,
            'measurement_unit' => $request->quanmunit,
        ];
        // dd($data);
        // UPDATE
        if ($request->filled('id')) {
            $ipCondition = IpCondition::find($request->id);

            if (!$ipCondition) {
                return response()->json(
                    [
                        'status' => 'error',
                        'message' => 'IP Condition not found',
                    ],
                    404,
                );
            }

            $ipCondition->update($data);

            return response()->json([
                'status' => 'success',
                'message' => 'IP Condition updated successfully',
                'condition_id' => $ipCondition->id,
            ]);
        }

        // CREATE
        $ipCondition = IpCondition::create($data);

        return response()->json([
            'status' => 'success',
            'message' => 'IP Condition created successfully',
            'data' => $ipCondition,
            'condition_id' => $ipCondition->id,
        ]);
    }

    public function deleteCondition($id)
    {
        Gate::authorize('manage settings');

        $condition = IpCondition::findOrFail($id);

        // Capture details BEFORE deleting so we can notify/email
        $snapshot = [
            'id' => $condition->id,
            'item_name' => $condition->item_name,
            'country' => is_array($condition->country) ? $condition->country : (json_decode($condition->country ?? '[]', true) ?? []),
            'quantity_limit' => $condition->quantity_limit,
            'measurement_unit' => $condition->measurement_unit,
            'start_date' => $condition->start_date,
            'end_date' => $condition->end_date,
            'addional_condition' => $condition->addional_condition,
        ];

        $condition->delete();

        // Notify + email that this permit condition was deleted (best-effort; don't block deletion)
        try {
            $title = 'Permit Condition News';
            $itemName = $snapshot['item_name'] ?? 'Unknown Item';

            $countryCodes = $snapshot['country'] ?? [];
            $countries = Country::whereIn('code', $countryCodes)->pluck('name')->toArray();
            $countryList = implode(', ', $countries);

            $detailsMessage = "A permit condition for item <strong>{$itemName}</strong> has been deleted.<br>";
            if (!empty($countryList)) {
                $detailsMessage .= "It applied to the following countries: <strong>{$countryList}</strong>.";
            }
            if (!empty($snapshot['quantity_limit'])) {
                $detailsMessage .= "<br>Quantity Limit: {$snapshot['quantity_limit']} {$snapshot['measurement_unit']}<br>";
            }
            if (!empty($snapshot['start_date'])) {
                $startDate = Carbon::parse($snapshot['start_date'])->format('d M Y');
                $endDate = !empty($snapshot['end_date']) ? Carbon::parse($snapshot['end_date'])->format('d M Y') : null;
                if ($endDate) {
                    $detailsMessage .= "Valid From: {$startDate} to {$endDate}";
                } else {
                    $detailsMessage .= "Valid From: {$startDate}";
                }
            }
            if (!empty($snapshot['addional_condition'])) {
                $detailsMessage .= "<br><span class='mt-2'>Additional Condition:<span>{$snapshot['addional_condition']}</span></span><br>";
            }

            $news = new News();
            $news->title = $title;
            $news->news = $detailsMessage;
            $news->expired_date = now()->addDays(7); // keep message around briefly
            $news->show = 1;
            $news->save();

            $publicUsers = PublicUser::get();
            $internalUsers = InternalUser::get();
            $url = '#';

            foreach ($publicUsers as $user) {
                Notification::send($user, new ApplicationNotification('A permit condition of item ' . $itemName . ' has been deleted', $title, $url));
            }

            foreach ($internalUsers as $user) {
                Notification::send($user, new ApplicationNotification('A permit condition of item ' . $itemName . ' has been deleted', $title, '/internal/permit_condition'));
            }

            // same behavior as shareNews() (emails to Gmail)
            // Mail::to('rowanyee79@gmail.com')->send(new QISNewsMail($title, $detailsMessage));
            Mail::to('hamsyitahnur@gmail.com')->send(new QISNewsMail($title, $detailsMessage));
        } catch (\Throwable $e) {
            // Swallow errors so deletion still succeeds
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Permit condition deleted successfully.',
        ]);
    }

    public function editCondition($id)
    {
        Gate::authorize('manage settings');

        $condition = IpCondition::with(['code'])->findOrFail($id);

        $pbdata = PublicCode::select('id', 'cate_name', 'cate_code', 'description')->where('cate_name', 'consignment_purpose')->where('is_del', false)->get();
        $measurements = PublicCode::select('id', 'cate_name', 'cate_code', 'description')->where('cate_name', 'unit_measurement')->where('is_del', false)->get();

        return view('pages.internal.misc.permit_edit', compact('condition', 'pbdata', 'measurements'));
    }

    public function getpermitconditiondata()
    {
        Gate::authorize('manage settings');

        $query = IpCondition::with(['condcategory'])->select('id', 'item_name', 'scientific_name', 'category', 'usage', 'country');

        return DataTables::of($query)->make(true);
    }

    public function getpermitconditionbyid($id)
    {
        Gate::authorize('manage settings');

        $conditions = IpCondition::with(['code', 'condcategory'])
            ->select('id', 'scientific_name', 'item_name', 'category', 'usage', 'addional_condition', 'quantity_limit', 'start_date', 'end_date', 'country', 'measurement_unit')
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
        Gate::authorize('manage settings');

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

    public function shareNews(Request $request)
    {
        $action = $request->input('action'); // 'released' | 'updated' (optional)
        $verb = $action === 'updated' ? 'updated' : 'released';

        if ($request->type == 'Import Permit') {
            $item = IpCondition::where('id', $request['condition_id'])->first();
            $title = 'Permit Condition News';
            $conditionTypeText = 'permit condition';
        } elseif ($request->type == 'Consignment') {
            $item = ConsignmentCondition::where('id', $request['condition_id'])->first();
            $title = 'Consignment Condition News';
            $conditionTypeText = 'consignment condition';
        }

        DB::beginTransaction();

        try {



            // Suppose $item is your consignment condition object
            $itemName = $item->item_name; // "AVOCADO"

            // Get the country names from the stored codes in JSON
            $countryCodes = $item['country'];// ["AU","KE","MX",...]
            // dd($countryCodes);
            $countries = Country::whereIn('code', $countryCodes)->pluck('name')->toArray();

            // Convert country array to comma-separated string
            $countryList = implode(', ', $countries);

            $actionText = ($request->action === 'edit') ? 'has been updated' : 'has been released';

            // If you want HTML instead of plain text:
            $detailsMessage = "A permit condition for item <strong>{$itemName}</strong> has been {$verb}.<br>";
            $detailsMessage .= "It applies to the following countries: <strong>{$countryList}</strong>.";
            if ($item->quantity_limit) {
                $detailsMessage .= "<br>Quantity Limit: {$item->quantity_limit} {$item->measurement_unit}<br>";
            }

            if ($item->start_date) {
                // Format dates
                $startDate = Carbon::parse($item->start_date)->format('d M Y'); // 10 Mar 2026
                $endDate = Carbon::parse($item->end_date)->format('d M Y'); // 28 Mar 2026
                $detailsMessage .= "Valid From: {$startDate} to {$endDate}";
            }




            // Build second message
            $detailsMessage .= "<br><span class = 'mt-2'>Additional Condition:
            
                <span>{$item->addional_condition}</span>

            </span><br>";



            // dd($detailsMessage);

            $news = new News();
            $news->title = $title;
            $news->news = $detailsMessage;
            $news->expired_date = $item->end_date;
            $news->show = 1;
            $news->save();

            $publicUsers = PublicUser::get();
            $internalUsers = InternalUser::get();

            $url = '#';

            $notificationActionText = ($request->action === 'edit') ? 'has been updated' : 'has been released';

            foreach ($publicUsers as $user) {
                // Mail::to($user->email)->send(
                //     new QISNewsMail($title, $detailsMessage)
                // );

                Notification::send($user, new ApplicationNotification('A condition of item ' . $item->item_name . ' ' . $notificationActionText, $title, $url));
                Mail::to($user->email)->send(
                    new QISNewsMail($title, $detailsMessage)
                );

            }

            foreach ($internalUsers as $user) {
                Notification::send($user, new ApplicationNotification(
                    'A condition of item ' . $item->item_name . ' ' . $notificationActionText,
                    $title,
                    '/internal/permit_edit_condition/' . $item->id
                ));
            }



            // Mail::to('hamsyitahnur@gmail.com')->send(
            //     new QISNewsMail($title, $detailsMessage)
            // );




            DB::commit();

            return response()->json([
                'status'  => 'success',
                'message' => 'News shared successfully.',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(
                [
                    'status' => 'error',
                    'message' => $e->getMessage(),
                ],
                500,
            );
        }


    }

    public function getBranches()
    {
        $branches = Branch::orderBy('name')->get();

        return response()->json([
            'status' => 'success',
            'data' => $branches,
        ]);
    }

    public function addBranch(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:branches,name',
        ]);

        $branch = Branch::create([
            'name' => $request->input('name'),
        ]);

        activity()
            ->useLog('user_activity')
            ->event('add branch')
            ->causedBy(authUser()['user'])
            ->log(
                authUser()['user']['fullname'] . ' added branch: ' . $branch->name
            );

        return response()->json([
            'status' => 'success',
            'message' => 'Branch added successfully.',
        ]);
    }

    public function updateBranch(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:branches,id',
            'name' => 'required|string|max:255|unique:branches,name,' . $request->input('id'),
        ]);

        $branch = Branch::findOrFail($request->input('id'));
        $oldBranchName = $branch->name;
        $branch->name = $request->input('name');
        $branch->save();

        if ($oldBranchName !== $branch->name) {
            InternalUser::where('branch', $oldBranchName)->update(['branch' => $branch->name]);
        }

        activity()
            ->useLog('user_activity')
            ->event('update branch')
            ->causedBy(authUser()['user'])
            ->log(
                authUser()['user']['fullname'] . ' updated branch: ' . $branch->name
            );

        return response()->json([
            'status' => 'success',
            'message' => 'Branch updated successfully.',
        ]);
    }

    public function deleteBranch($id)
    {
        $branch = Branch::findOrFail($id);
        $branchName = $branch->name;
        $branch->delete();

        activity()
            ->useLog('user_activity')
            ->event('delete branch')
            ->causedBy(authUser()['user'])
            ->log(
                authUser()['user']['fullname'] . ' deleted branch: ' . $branchName
            );

        return response()->json([
            'status' => 'success',
            'message' => 'Branch deleted successfully.',
        ]);
    }

    public function getDistinctUsage()
    {
        $rows = IpCondition::whereNotNull('usage')->pluck('usage');

        $usages = collect();
        foreach ($rows as $row) {
            $values = is_array($row) ? $row : json_decode($row, true);
            if (is_array($values)) {
                $usages = $usages->merge($values);
            }
        }

        $distinct = $usages->map(fn($v) => trim($v))
            ->filter()
            ->unique()
            ->values();

        return response()->json(['data' => $distinct]);
    }

    public function measurementUnit()
    {
        $measurement_unit = PublicCode::with(['conversion'])->where('cate_name', 'unit_measurement')->get();

        return response()->json([
            'unit' => $measurement_unit
        ]);
    }


    public function getspecificitem($id)
    {
        $item = IpCondition::where('id', $id)->first();

        return response()->json([
            'data' => $item,
        ]);
    }
}

