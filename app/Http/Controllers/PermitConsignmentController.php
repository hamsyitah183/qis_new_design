<?php

namespace App\Http\Controllers;

use App\Events\ApplicationDeleted;
use App\Events\PublicUserEvent;
use App\Models\InternalUser;
use App\Models\IpConsignmentPermit;
use App\Models\PublicUser;
use App\Notifications\ApplicationNotification;
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

        $permit->permit_number = 'IP' . now()->format('YmdHis');

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

        $users = InternalUser::role(['admin', 'officer'])->get();
        Notification::send($users, new ApplicationNotification('A permit with application ID ' . $permit->application->application_id . ' has been ' . $status, authUser()['user']->fullname, $url));

        $user = PublicUser::where('uuid', $permit->application->user_id)->first();

        try {
            // Events & notifications
            event(new ApplicationDeleted('Permit in ' . $permit->application->application_id . ' is ' . $status));

            event(new PublicUserEvent('A permit in application with ID ' . $permit->application->application_id . ' has been ' . $status, $user->uuid));
        } catch (\Exception $e) {
            Log::warning('Pusher connection failed but continuing permit acceptance: ' . $e->getMessage());
        }

        Notification::send($user, new ApplicationNotification('A permit in application with ID ' . $permit->application->application_id . ' has been ' . $status, authUser()['user']->fullname, $url));

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
                'officer verification completed by DOA',
                "Your application's permit has been officer verification completed by DOA. Please reapply a permit that has been rejected if theres any",
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
        return view('pages.public.permit.permit_list', [
            'title' => 'Permit List',
        ]);
    }

    public function getAllPermitList()
    {
        $userUuid = authUser()['user']->uuid;
        $type = authUser()['type'];

        $query = IpConsignmentPermit::query()->with('application'); // eager load application if needed

        // Apply filter for public users
        if ($type !== 'internal') {
            $query->where('public_user_uuid', $userUuid);
        }

        return DataTables::eloquent($query)
            ->addIndexColumn()

            ->editColumn('item_name', fn($row) => $row->item_name)
            ->filterColumn('item_name', function ($query, $keyword) {
                $query->whereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(consignment_detail, '$.item_name'))) LIKE ?", ['%' . strtolower($keyword) . '%']);
            })

            ->addColumn('importer', fn($row) => $row->application->importer->fullname)

            ->addColumn('action', function ($row) use ($type) {
                // Use application_id (correct)
                $viewUrl = '/permit/import/' . $row->permit_number;

                $view =
                    '
                <a href="' .
                    $viewUrl .
                    '" class="btn btn-sm btn-primary">
                    <i class="ti ti-eye"></i>
                </a>
            ';

                $downloadPermit = '';

                if ($type === 'internal') {
                    $downloadPermit =
                        '
                    <button 
                        class="btn btn-sm btn-success btn-wave generatePermit ms-2"
                        data-permit="' .
                        $row->id .
                        '">
                        Download Permit
                    </button>
                ';
                }

                return $view . $downloadPermit;
            })

            ->rawColumns(['action'])
            ->make(true);
    }

    function permitDetails($permitNumber)
    {
        return $permitNumber;
    }
}
