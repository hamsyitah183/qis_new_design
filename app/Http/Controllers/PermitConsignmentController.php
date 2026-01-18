<?php

namespace App\Http\Controllers;

use App\Events\ApplicationDeleted;
use App\Events\PublicUserEvent;
use App\Models\InternalUser;
use App\Models\IpConsignmentPermit;
use App\Models\PublicUser;
use App\Notifications\ApplicationNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;

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

        // Events & notifications
        event(new ApplicationDeleted('Permit in ' . $permit->application->application_id . ' is ' . $status));

        $users = InternalUser::role(['admin', 'officer'])->get();
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
}
