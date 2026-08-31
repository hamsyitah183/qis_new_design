<?php

namespace App\Http\Controllers;

use App\Events\ApplicationDeleted;
use App\Events\PublicUserEvent;
use App\Models\ConsignmentPermit;
use App\Models\InternalUser;
use App\Models\IpConsignmentPermit;
use App\Models\PublicUser;
use App\Notifications\ApplicationNotification;
use App\Services\ApplicationActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Spatie\Activitylog\Models\Activity;
use Yajra\DataTables\Facades\DataTables;

class PermitConsignmentController extends Controller
{
    //
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

        $users = InternalUser::role(['admin', 'officer', 'superadmin'])->get();
        Notification::send($users, new ApplicationNotification('A permit with application ID ' . $permit->application->application_id . ' has been ' . $status, 'A permit with application ID ' . $permit->application->application_id . ' has been ' . $status, authUser()['user']->fullname, $url));

        $user = PublicUser::where('uuid', $permit->application->user_id)->first();

        try {
            // Events & notifications
            event(new ApplicationDeleted('Permit in ' . $permit->application->application_id . ' is ' . $status));

            event(new PublicUserEvent('A permit in application with ID ' . $permit->application->application_id . ' has been ' . $status, $user->uuid));
        } catch (\Exception $e) {
            Log::warning('Pusher connection failed but continuing permit acceptance: ' . $e->getMessage());
        }

        Notification::send($user, new ApplicationNotification('A permit in application with ID ' . $permit->application->application_id . ' has been ' . $status, 'A permit in application with ID ' . $permit->application->application_id . ' has been ' . $status, authUser()['user']->fullname, $url));

        activity()
            ->tap(function (Activity $activity) {
                $activity->log_name = 'user_activity';
            })
            ->event(strtolower($status) . ' consignment permit conditions')
            ->causedBy(authUser()['user'])
            ->performedOn(authUser()['user'])
            ->withProperties([
                'permit' => $permit,
                'application_id' => $permit->application->application_id,
            ])
            ->log(authUser()['user']['fullname'] . ' has ' . strtolower($status) . ' permit conditions for application ' . $permit->application->application_id);

        $application->logActivity(
            'Officer Verification',
            $request['reason'] ?? 'Permit approved by officer and pending for payment',
            $accepted ? 'Officer Verified' : 'Officer Rejected'
        );


        // dd($application->importer_detail);

        // Check if no status is 'processing'
        if (!$allStatuses->contains('processing') && !$allStatuses->contains('reapplied')) {
            // dd($allStatuses);
            $application->logActivity(action: 'Officer Verification Completed', remark: 'Officer Verification Completed', status: 'Officer Verification Completed');

            // dd($application);

            $application->status = 'Officer Verification Completed';
            $application->save();

            $notificationController = new NotificationController();

            $notificationController->sendStatusMessage(
                $application->importer_detail['fullname'] ?? 'User',
                'Import Permit',
                $application->application_id,
                'checked and verified by DOA',
                "All your application's permit has been verified by DOA. Please reapply a permit that has been rejected if theres any",
                $application->importer->phone_number ?? '+60143290092', // recipient number
            );
        }

        $permit->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Permit condition updated successfully.',
        ]);
    }

    public function getView()
    {
        if (auth()->user()->hasRole('boundary officer')) {
            abort(403, 'Unauthorized action. Boundary Officers are restricted from this area.');
        }

        return view('pages.public.permit.permit_list', [
            'title' => 'Permit List',
        ]);
    }

    public function getAllPermitList()
    {
        if (auth()->user()->hasRole('boundary officer')) {
            abort(403, 'Unauthorized action. Boundary Officers are restricted from this area.');
        }

        $userUuid = authUser()['user']->uuid;
        $type = authUser()['type'];

        $query = IpConsignmentPermit::query()->with(['application.importer', 'application.exporter.countryInfo', 'application.entryPoint.districtCode']); // eager load relations

        // Apply filter for public users
        if ($type !== 'internal') {
            $query->where('public_user_uuid', $userUuid);
        }

        return DataTables::eloquent($query)
            ->addIndexColumn()

            // Importer column
            ->addColumn('importer', fn($row) => $row->application->importer->fullname ?? 'N/A')

            // Exporter column
            ->addColumn('exporter', fn($row) => $row->application->exporter->name ?? 'N/A')

            // Country of origin
            ->addColumn('country', fn($row) => $row->application->exporter->countryInfo->name ?? 'N/A')

            // Entry point
            ->addColumn('entry_point', function ($row) {
                if ($row->application->entryPoint) {
                    $entryPoint = $row->application->entryPoint;
                    $district = $entryPoint->districtCode->district_name ?? '';
                    return $entryPoint->entry_point_name . ($district ? " ({$district})" : '');
                }
                return 'N/A';
            })

            // Item name from consignment_detail JSON
            ->editColumn('item_name', fn($row) => $row->item_name)
            ->filterColumn('item_name', function ($query, $keyword) {
                $query->whereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(consignment_detail, '$.item_name'))) LIKE ?", ['%' . strtolower($keyword) . '%']);
            })

            ->addColumn('action', function ($row) use ($type) {
                $downloadPermit = '';

                if ($type === 'internal') {
                    // URL-safe slug: remove '/' entirely (e.g. IPO/260... -> IPO260...)
                    $slugPermitNumber = str_replace('/', '', $row->permit_number);

                    $downloadPermit =
                        '
                    <button
                        class="btn btn-sm btn-success btn-wave generatePermit ms-2"
                        data-permit="' .
                        $slugPermitNumber .
                        '">
                        Download Permit
                    </button>
                ';
                }

                return $downloadPermit;
            })

            ->rawColumns(['action'])
            ->make(true);
    }

    function permitDetails($permitNumber)
    {
        // Find import permit by permit number and redirect to application detail
        $importPermit = IpConsignmentPermit::where('permit_number', $permitNumber)
            ->with('application')
            ->firstOrFail();

        return redirect('/view_application/' . $importPermit->application->application_id);
    }

    public function linkCondition(Request $request, $id)
    {
        $request->validate([
            'ip_condition_id' => 'required|integer|exists:ip_condition,id',
        ]);

        $permit = ConsignmentPermit::with('application')->findOrFail($id);

        // Ensure we're working with a real array, not null/stdClass
        $detail = is_array($permit->consignment_detail) ? $permit->consignment_detail : [];
        $originalItemName = $detail['item_name'] ?? null;
        $wasCustom = $detail['isCustom'] ?? null;

        $detail['item_id']  = (int) $request->ip_condition_id;
        $detail['isCustom'] = false;

        \DB::transaction(function () use ($permit, $detail) {
            $permit->consignment_detail = $detail; // explicit attribute assignment
            $permit->status = 'processing';
            $permit->save();
        });

        $permit->refresh();

        // ─── Activity Log ─────────────────────────────────
        $application = $permit->application;

        if ($application) {
            $user = authUser()['user'] ?? null;

            ApplicationActivityLogger::log(
                application: $application,
                event: 'custom_item_linked',
                description: ($user->fullname ?? 'System')
                    . " linked custom item \"{$originalItemName}\" to permit condition ID {$request->ip_condition_id} for application {$application->application_id}",
                properties: [
                    'permit_id'        => $permit->id,
                    'ip_condition_id'  => (int) $request->ip_condition_id,
                    'original_item'    => $originalItemName,
                    'is_custom_before' => $wasCustom,
                    'is_custom_after'  => $permit->consignment_detail['isCustom'] ?? null,
                ],
            );
        }

        return response()->json([
            'message' => 'Item linked successfully.',
            'permit'  => $permit->consignment_detail, // return the persisted state for the frontend to verify
        ]);
    }
}
