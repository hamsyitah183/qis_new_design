<?php

namespace App\Http\Controllers;

use App\Models\ConsignmentApplication;
use App\Models\ConsignmentImporter;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\PublicCode;
use App\Models\Country;
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
use App\Services\ApplicationActivityLogger;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;
use Str;
use Spatie\Activitylog\Models\Activity;

class ConsignmentController extends Controller
{
    //
    public function showallconsignmentlist()
    {
        return view('pages.public.consignment_list');
    }

    /**
     * Show consignment certificate list for internal users (admin)
     */
    public function showInternalConsignmentList()
    {
        return view('pages.internal.consignment_list');
    }

    public function getallconsignmentlist()
    {
        $userData = authUser();
        $user = $userData['user'];
        $userUuid = $user->uuid;
        $type = $userData['type'];

        $query = ConsignmentApplication::with([
            'user',
            'importer',
            'exporter',
            'entryPoint.districtCode',
            'consignmentPermits',
            'latestLog.causer'
        ]);

        // Filter for public users
        if ($type === 'public') {
            $query->where(function ($q) use ($userUuid) {
                $q->where('user_id', $userUuid)->orWhere('exporter_id', $userUuid);
            });
        }

        $datatable = DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('importer', fn($row) => $row->exporter->fullname ?? '-')
            ->addColumn('exporter', fn($row) => $row->importer->name ?? '-')
            ->addColumn('status', function ($row) {
                $status = strtolower($row->status ?? 'pending');
                $latestLog = $row->latestLog;
                $id = $row->application_id;

                $latestTime = $latestLog?->updated_at?->format('d M Y, h:i A') ?? '-';
                $causerName = $latestLog?->causer?->fullname ?? '-';

                return match (true) {
                    str_contains($status, 'pending') =>
                    $this->badge('warning', 'Pending', $latestTime, $causerName, $id),

                    str_contains($status, 'rejected') =>
                    $this->badge('danger', 'Rejected', $latestTime, $causerName, $id),

                    str_contains($status, 'not approved') =>
                    $this->badge('danger', 'Not Approved', $latestTime, $causerName, $id),

                    str_contains($status, 'accepted') =>
                    $this->badge('success', 'Accepted', $latestTime, $causerName, $id),

                    str_contains($status, 'fully processed') =>
                    $this->badge('success', 'Fully Processed', $latestTime, $causerName, $id),

                    str_contains($status, 'clerk verified') =>
                    $this->badge('info', 'Clerk Verified', $latestTime, $causerName, $id),

                    str_contains($status, 'submitted') =>
                    $this->badge('primary', 'Submitted', $latestTime, $causerName, $id),

                    default =>
                    '<span class="badge bg-secondary fs-12 p-1 activityLog" data-log="' . $id . '">' . ucfirst($status) . '</span>',
                };
            })
            ->addColumn('permit_status', function ($row) {
                $statusColors = [
                    'processing' => 'bg-info',
                    'pending for payment' => 'bg-warning',
                    'rejected' => 'bg-danger',
                    'paid' => 'bg-success',
                ];

                $permit_statuses = $row->consignmentPermits->pluck('status')
                    ->map(fn($status) => strtolower($status))
                    ->toArray();

                $statusCounts = array_fill_keys(array_keys($statusColors), 0);

                foreach ($permit_statuses as $status) {
                    if (isset($statusCounts[$status])) {
                        $statusCounts[$status]++;
                    }
                }

                $boxesHtml = '';
                foreach ($statusColors as $status => $color) {
                    $count = $statusCounts[$status] ?? 0;
                    $boxesHtml .= '<div class="badge ' . $color . ' text-white text-center" data-bs-toggle="tooltip"
                            data-bs-placement="top" title="' . ucfirst($status) . '"
                           style="height:20px; width:20px; display:inline-flex; align-items:center; justify-content:center; margin-right:5px;">
                           ' . $count . '
                       </div>';
                }

                return $boxesHtml;
            })
            ->addColumn('action', function ($row) use ($type) {
                $status = ucfirst($row->status);
                $isPublic = $type === 'public';
                $showEdit = $isPublic && ($status === 'Draft' || $status === 'Pending');

                $buttons = '';

                if ($showEdit) {
                    if ($row->category_application == 1) {
                        $url = route('public.consignmentOther.app', ['id' => $row->application_id]);
                    } else {
                        $url = route('public.consignment.app', ['id' => $row->application_id]);
                    }
                    $buttons .= '<a class="btn btn-sm btn-primary me-1" href="' . $url . '" title="Edit"> <i class="ti ti-edit"></i> </a>';
                } else {
                    $viewUrl = '/view_consignment/' . $row->application_id;
                    $buttons .= '<a class="btn btn-sm btn-primary me-1 viewApplication" href="' . $viewUrl . '" title="View"> <i class="ti ti-eye"></i> </a>';
                }

                if ($type === 'internal') {
                    $buttons .= '<button class="btn btn-sm btn-danger deleteApplication" data-id="' . $row->application_id . '" title="Delete"> <i class="ti ti-trash"></i> </button>';
                }

                return $buttons;
            });

        if ($type === 'internal') {
            $datatable->addColumn('submitted_by', fn($row) => $row->user->fullname ?? '-');
        }

        return $datatable
            ->rawColumns(['status', 'permit_status', 'action'])
            ->make(true);
    }


    public function getApplicationDetails($id)
    {
        $type = authUser()['type']; // 'public' or 'internal'
        $user = authUser()['user']; // authenticated user object

        // Fetch application and eager load relationships
        $application = ConsignmentApplication::where('application_id', $id)
            ->with([
                'user',
                'importer',
                'exporter',
                'entryPoint.districtCode',
                'consignmentPermits.attachments',
            ])
            ->firstOrFail();

        if ($type === 'internal') {
            return response()->json($application);
        }

        if ($type === 'public') {
            // Check if user is either the submitter or the importer
            if ($application->user_id !== $user->uuid && $application->importer_id !== $user->uuid) {
                return response()->json(
                    [
                        'message' => 'You do not have authority to view this application',
                    ],
                    403,
                );
            }

            return response()->json($application);
        }

        // Default fallback
        return response()->json(
            [
                'message' => 'User type not recognized',
            ],
            400,
        );
    }

    /**
     * Update consignment application status following the status flow:
     * 1. Application Submitted → Clerk accepts → Clerk Review In-Progress
     * 2. Clerk Review In-Progress → Clerk verifies → Clerk Verified / Clerk Rejected
     * 3. Clerk Verified → Officer verifies → Officer Verified / Officer Rejected
     * 4. Officer Verified → Fully Processed (handled separately)
     */
    public function updateStatus($id, Request $request)
    {
        try {
            $action = $request->input('status'); // 'Approved' or 'Rejected'

            if (!in_array($action, ['Approved', 'Rejected'], true)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid status value.',
                ], 422);
            }

            $application = ConsignmentApplication::where('application_id', $id)->firstOrFail();
            $currentStatus = strtolower($application->status ?? '');
            $isApproved = $action === 'Approved';
            $newStatus = '';
            $actionLabel = '';

            // Determine new status based on current status and action
            if (str_contains($currentStatus, 'application submitted') || (str_contains($currentStatus, 'submitted') && !str_contains($currentStatus, 'clerk') && !str_contains($currentStatus, 'officer'))) {
                // Step 1: Application Submitted → Clerk accepts → Clerk Review In-Progress
                if ($isApproved) {
                    $newStatus = 'Clerk Review In-Progress';
                    $actionLabel = 'Application accepted by clerk';
                } else {
                    $newStatus = 'Clerk Rejected';
                    $actionLabel = 'Application rejected by clerk';
                }
            } elseif (str_contains($currentStatus, 'clerk review in progress') || str_contains($currentStatus, 'clerk review in-progress')) {
                // Step 2: Clerk Review In-Progress → Clerk verifies → Clerk Verified / Clerk Rejected
                if ($isApproved) {
                    $newStatus = 'Clerk Verified';
                    $actionLabel = 'Application verified by clerk';
                } else {
                    $newStatus = 'Clerk Rejected';
                    $actionLabel = 'Application rejected by clerk';
                }
            } elseif (str_contains($currentStatus, 'clerk verified')) {
                // Step 3: Clerk Verified → Officer verifies → Officer Verified / Officer Rejected
                if ($isApproved) {
                    $newStatus = 'Officer Verified';
                    $actionLabel = 'Application verified by officer';
                } else {
                    $newStatus = 'Officer Rejected';
                    $actionLabel = 'Application rejected by officer';
                }
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Cannot update status from current state: ' . $application->status,
                ], 422);
            }

            $application->status = $newStatus;
            $application->save();

            activity()
                ->tap(function (Activity $activity) {
                    $activity->log_name = 'user_activity';
                })
                ->event(strtolower($isApproved ? 'approve' : 'reject') . ' consignment application')
                ->causedBy(authUser()['user'])
                ->performedOn(authUser()['user'])
                ->withProperties([
                    'status' => $newStatus,
                    'action_label' => $actionLabel,
                    'reason' => $request->input('reason')
                ])
                ->log(authUser()['user']['fullname'] . ' has ' . ($isApproved ? 'approved' : 'rejected') . ' a consignment application (ID: ' . $application->application_id . ') as ' . $newStatus);

            // Log activity (wrap in try-catch to prevent breaking the response)
            try {
                $application->logActivity(
                    action: $newStatus,
                    remark: $actionLabel . ' by ' . authUser()['user']->fullname . ($request->input('reason') ? ' - ' . $request->input('reason') : ''),
                    status: $newStatus
                );
            } catch (\Exception $e) {
                \Log::warning('Failed to log activity for consignment application: ' . $e->getMessage());
            }

            return response()->json([
                'status' => 'success',
                'message' => $actionLabel . ' successfully.',
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Error updating consignment status: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update status: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a consignment application and its related data (internal only).
     */
    public function deleteApplication($id)
    {
        try {
            $userData = authUser();
            $type = $userData['type'];
            $user = $userData['user'];

            // Only internal users can delete through this controller
            if ($type !== 'internal') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized to delete this application.',
                ], 403);
            }

            $application = ConsignmentApplication::where('application_id', $id)->firstOrFail();
            $applicationId = $application->application_id;
            $userName = $user->fullname ?? 'Unknown User';

            DB::beginTransaction();

            // 1. Log activity before deletion
            activity()
                ->tap(function (Activity $activity) {
                    $activity->log_name = 'user_activity';
                })
                ->event('delete consignment application')
                ->causedBy(authUser()['user'])
                ->performedOn(authUser()['user'])
                ->withProperties([
                    'application_id' => $applicationId
                ])
                ->log($userName . ' has deleted a consignment application (ID: ' . $applicationId . ')');

            // 2. Delete attachments from storage
            $permits = $application->consignmentPermits()->with('attachments')->get();
            foreach ($permits as $permit) {
                foreach ($permit->attachments as $attachment) {
                    try {
                        if ($attachment->file_path) {
                            $path = str_replace('/storage/', '', $attachment->file_path);
                            if (Storage::disk('public')->exists($path)) {
                                Storage::disk('public')->delete($path);
                            }
                        }
                        $attachment->delete();
                    } catch (\Exception $e) {
                        \Log::warning('Failed to delete attachment: ' . $e->getMessage());
                    }
                }
                $permit->delete();
            }

            // 3. Delete application
            $application->delete();

            DB::commit();

            // 4. Send Notifications for deletion
            $notificationUrl = url('/view_consignment/' . $applicationId);

            // Notify internal users (admins/clerks)
            try {
                $users = InternalUser::role(['admin', 'clerk'])->get();
                Notification::send($users, new ApplicationNotification('Consignment certificate application deleted by ' . $userName, $userName, $notificationUrl));
            } catch (\Exception $e) {
                \Log::warning('Failed to send notification to internal users: ' . $e->getMessage());
            }

            // Notify the public user who owned the application
            try {
                // Fetch owner before sending (we still have application properties in memory even if deleted from DB)
                $ownerUuid = $application->user_id;
                $owner = PublicUser::where('uuid', $ownerUuid)->first();
                if ($owner) {
                    $owner->notify(new ApplicationNotification('Your consignment application with id ' . $applicationId . ' has been deleted by an administrator', 'QIS', $notificationUrl));
                }
            } catch (\Exception $e) {
                \Log::warning('Failed to send notification to owner: ' . $e->getMessage());
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Consignment application deleted successfully.',
            ], 200);

        } catch (\Exception $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            \Log::error('Error deleting consignment application: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete application: ' . $e->getMessage(),
            ], 500);
        }
    }
    private function badge($color, $label, $time, $user, $id)
    {
        return '
        <span class="badge bg-' . $color . ' fs-12 p-1 activityLog"  data-log="' . $id . '">' . $label . '</span>
        <br class="mt-1">
        <small class="text-muted">at ' . $time . '</small><br>
        <small class="text-muted">by ' . e($user) . '</small>
        ';
    }
}

