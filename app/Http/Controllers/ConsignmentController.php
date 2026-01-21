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

        $query = ConsignmentApplication::with(['user', 'importer', 'exporter', 'entryPoint.districtCode', 'consignmentPermits']);

        // Filter for public users
        if ($type === 'public') {
            $query->where(function ($q) use ($userUuid) {
                $q->where('user_id', $userUuid)->orWhere('importer_id', $userUuid);
            });
        }

        $datatable = DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('category_application', function ($row) {
                $cat = (int) $row->category_application;
                return $cat === 1 ? 'Apply For Others' : 'Self Apply';
            })
            ->addColumn('importer', fn($row) => $row->importer->name ?? '-') // Swap: Importer = Partner (Wait, User-requested Importer = Me, Exporter = Guest?)
            // RE-READING: "add a feature where when its pending or draft the public user can still be edited by them and once rejected or approved public user can only view"
            // Screenshot shows: Importer (Aaron Chin - likely User), Exporter (yong - likely Partner)
            // My code previously: importer = Partner, exporter = User
            // So: Importer column should show $row->exporter->fullname?
            // No, User = Exporter in my current logic.
            // Let's check my previously viewed code learnings: "exporter_id: Refers to User (PublicUser, UUID). importer_id: Refers to Partner (ConsignmentImporter, int ID)."
            // So in the UI:
            // "Importer" column should show User (PublicUser) -> $row->exporter->fullname
            // "Exporter" column should show Partner (ConsignmentImporter) -> $row->importer->name
            ->addColumn('importer', fn($row) => $row->exporter->fullname ?? '-')
            ->addColumn('exporter', fn($row) => $row->importer->name ?? '-')
            ->addColumn('eta', fn($row) => $row->eta ? $row->eta->format('d M Y') : '-')
            ->addColumn('transport_type', fn($row) => ucfirst($row->transport_type) ?? '-')
            ->addColumn('entry_point', function ($row) {
                if ($row->entryPoint) {
                    $district = $row->entryPoint->districtCode->name ?? '';
                    $point = $row->entryPoint->entry_name ?? '';
                    return $district . ($district && $point ? ' - ' : '') . $point;
                }
                return '-';
            })
            ->addColumn('category_application', function ($row) {
                return $category = $row->category_application == 1 ? 'Others' : 'Self';

            })
            ->addColumn('status', function ($row) {
                $status = strtolower($row->status ?? '');
                $originalStatus = $row->status ?? '';
                $statusLabel = '';
                $badgeClass = 'bg-secondary';
                $style = '';

                // Application status flow based on flowchart:
                // 1. Application Submitted → application submitted
                // 2. Clerk Accept → clerk review in progress
                // 3. Officer Review → clerk verified / clerk rejected
                // 4. After payment → officer verified / officer rejected
                // 5. Final → Fully Processed
    
                if (str_contains($status, 'draft')) {
                    $badgeClass = 'bg-purple';
                    $style = 'style="background-color:rgb(0, 102, 255) !important;"';
                    $statusLabel = 'Draft';
                } elseif (str_contains($status, 'application submitted') || (str_contains($status, 'submitted') && !str_contains($status, 'clerk') && !str_contains($status, 'officer'))) {
                    $badgeClass = 'bg-warning';
                    $style = 'style="background-color: #ffc658 !important;"';
                    $statusLabel = 'Application Submitted';
                } elseif (str_contains($status, 'clerk review in progress') || str_contains($status, 'clerk review in-progress')) {
                    $badgeClass = 'bg-warning';
                    $style = 'style="background-color: #9e5cf7 !important;"';
                    $statusLabel = 'Clerk Review In-Progress';
                } elseif (str_contains($status, 'clerk verified')) {
                    $badgeClass = 'bg-info';
                    $style = 'style="background-color: #5cf79e !important;"';
                    $statusLabel = 'Clerk Verified';
                } elseif (str_contains($status, 'clerk rejected')) {
                    $badgeClass = 'bg-danger';
                    $style = 'style="background-color: #dc3545 !important;"';
                    $statusLabel = 'Clerk Rejected';
                } elseif (str_contains($status, 'officer verified')) {
                    $badgeClass = 'bg-success';
                    $style = 'style="background-color: #5cf79e !important;"';
                    $statusLabel = 'Officer Verified';
                } elseif (str_contains($status, 'officer rejected')) {
                    $badgeClass = 'bg-danger';
                    $style = 'style="background-color: #dc3545 !important;"';
                    $statusLabel = 'Officer Rejected';
                } elseif (str_contains($status, 'fully processed')) {
                    $badgeClass = 'bg-success';
                    $style = 'style="background-color: #5cf79e !important;"';
                    $statusLabel = 'Fully Processed';
                } else {
                    // Fallback for any other statuses
                    $statusLabel = ucfirst($originalStatus);
                }

                return '<span class="badge ' . $badgeClass . '" ' . $style . '>' . $statusLabel . '</span>';
            })
            ->addColumn('importer_verify', function ($row) {
                $status = ucfirst($row->status);
                $badgeClass = 'bg-secondary';
                $style = '';

                // Match InspectionController colors
                if ($status === 'Draft') {
                    $badgeClass = 'bg-purple';
                    $style = 'style="background-color: #9e5cf7 !important;"';
                } elseif ($status === 'Pending' || $status === 'Submitted') { // Consignment uses 'Submitted' typically
                    $badgeClass = 'bg-warning';
                    $style = 'style="background-color: #ffc658 !important;"';
                } elseif ($status === 'Approved') {
                    $badgeClass = 'bg-success';
                    $style = 'style="background-color: #5cf79e !important;"';
                } elseif ($status === 'Rejected') {
                    $badgeClass = 'bg-danger';
                    $style = 'style="background-color: #f75c5c !important;"';
                }

                return '<span class="badge ' . $badgeClass . '" ' . $style . '>' . $status . '</span>';
            })
            ->addColumn('permit_status', function ($row) {
                // Map statuses to colors
                $statusColors = [
                    'draft' => 'bg-secondary',
                    'submitted' => 'bg-primary',
                    'processing' => 'bg-info',
                    'pending for payment' => 'bg-warning',
                    'pending for verification' => 'bg-warning',
                    'approved' => 'bg-success',
                    'paid' => 'bg-success',
                    'rejected' => 'bg-danger',
                ];

                // Get all permit statuses for this row, lowercase
                $permit_statuses = $row->consignmentPermits->pluck('status')->map(fn($status) => strtolower($status))->toArray();

                // Count how many of each status
                $statusCounts = array_fill_keys(array_keys($statusColors), 0);

                foreach ($permit_statuses as $status) {
                    if (isset($statusCounts[$status])) {
                        $statusCounts[$status]++;
                    }
                }

                // Build HTML boxes with count inside
                $boxesHtml = '';
                foreach ($statusColors as $status => $color) {
                    $count = $statusCounts[$status] ?? 0;
                    if ($count > 0) {
                        $boxesHtml .=
                            '<div class="badge ' . $color . ' text-white text-center" data-bs-toggle="tooltip"
                            data-bs-placement="top" title="' . ucfirst($status) . '"
                           style="height:20px; width:20px; display:inline-flex; align-items:center; justify-content:center; margin-right:5px;">
                           ' . $count . '
                       </div>';
                    }
                }

                return $boxesHtml;
            })
            ->addColumn('created_at', fn($row) => $row->created_at ? $row->created_at->format('Y-m-d H:i') : '-');

        // Add submitted_by column for internal users
        if ($type === 'internal') {
            $datatable->addColumn('submitted_by', fn($row) => $row->user->fullname ?? '-');
        }

        return $datatable->addColumn('action', function ($row) use ($type) {
            $status = strtolower($row->status ?? '');
            $url = url('/view_consignment/' . $row->application_id);

            $buttons = '<div class="d-flex align-items-center gap-2">
                            <a class="btn btn-sm btn-primary me-1 viewConsignment" href="' . $url . '" title="View" data-bs-toggle="tooltip" data-bs-placement="top">
                                <i class="ti ti-eye"></i>
                            </a>';

            // Extra admin controls for internal users
            if ($type === 'internal') {
                // Show approve/reject buttons based on status flow:
                // - Application Submitted → Clerk can accept (moves to Clerk Review In-Progress)
                // - Clerk Review In-Progress → Clerk can verify/reject (moves to Clerk Verified/Rejected)
                // - Clerk Verified → Officer can verify/reject (moves to Officer Verified/Rejected)
                if (str_contains($status, 'application submitted') || (str_contains($status, 'submitted') && !str_contains($status, 'clerk') && !str_contains($status, 'officer'))) {
                    // Clerk can accept application
                    $buttons .= '<button class="btn btn-sm btn-success me-1 consignment-approve" data-id="' . $row->application_id . '" title="Accept (Clerk Review)" data-bs-toggle="tooltip" data-bs-placement="top">
                                        <i class="ti ti-check"></i>
                                     </button>';
                    $buttons .= '<button class="btn btn-sm btn-warning me-1 consignment-reject" data-id="' . $row->application_id . '" title="Reject" data-bs-toggle="tooltip" data-bs-placement="top">
                                        <i class="ti ti-x"></i>
                                     </button>';
                } elseif (str_contains($status, 'clerk review in progress') || str_contains($status, 'clerk review in-progress')) {
                    // Clerk can verify/reject
                    $buttons .= '<button class="btn btn-sm btn-success me-1 consignment-approve" data-id="' . $row->application_id . '" title="Verify (Clerk)" data-bs-toggle="tooltip" data-bs-placement="top">
                                        <i class="ti ti-check"></i>
                                     </button>';
                    $buttons .= '<button class="btn btn-sm btn-warning me-1 consignment-reject" data-id="' . $row->application_id . '" title="Reject (Clerk)" data-bs-toggle="tooltip" data-bs-placement="top">
                                        <i class="ti ti-x"></i>
                                     </button>';
                } elseif (str_contains($status, 'clerk verified')) {
                    // Officer can verify/reject
                    $buttons .= '<button class="btn btn-sm btn-success me-1 consignment-approve" data-id="' . $row->application_id . '" title="Verify (Officer)" data-bs-toggle="tooltip" data-bs-placement="top">
                                        <i class="ti ti-check"></i>
                                     </button>';
                    $buttons .= '<button class="btn btn-sm btn-warning me-1 consignment-reject" data-id="' . $row->application_id . '" title="Reject (Officer)" data-bs-toggle="tooltip" data-bs-placement="top">
                                        <i class="ti ti-x"></i>
                                     </button>';
                }
            }

            $buttons .= '<button class="btn btn-sm btn-danger delete-consignment" data-id="' . $row->application_id . '" title="Delete" data-bs-toggle="tooltip" data-bs-placement="top">
                                <i class="ti ti-trash"></i>
                             </button>
                         </div>';
            return $buttons;
        })
            ->rawColumns(['action', 'permit_status', 'category_application', 'status'])
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

            // 1. Log activity before deletion (if supported by the model/trait)
            try {
                if (method_exists($application, 'logActivity')) {
                    $application->logActivity(
                        action: 'Deleted',
                        remark: 'Consignment application deleted by ' . $userName,
                        status: 'Deleted'
                    );
                }
            } catch (\Exception $e) {
                \Log::warning('Failed to log deletion activity: ' . $e->getMessage());
            }

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
}

