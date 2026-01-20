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

        return DataTables::eloquent($query)
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
            ->addColumn('created_at', fn($row) => $row->created_at ? $row->created_at->format('Y-m-d H:i') : '-')
            ->addColumn('action', function ($row) {
                $status = strtolower($row->status);
                // Draft/Pending -> Edit (Yellow Pencil)
                // Approved/Rejected -> View (Blue Eye)
    
                $isEditable = ($status === 'draft' || $status === 'pending' || $status === 'submitted');
                $url = $isEditable
                    ? route('editApplication', ['uuid' => $row->application_id])
                    : route('public.viewApplication', ['uuid' => $row->application_id]);

                $icon = $isEditable ? 'ti ti-edit' : 'ti ti-eye';
                $btnClass = $isEditable ? 'btn-info' : 'btn-primary'; // Pencil usually info/warning, Eye usually primary/success
    
                $view = '<div class="d-flex align-items-center gap-2">
                            <a class="btn btn-sm ' . $btnClass . ' viewConsignment" href="' . $url . '">
                                <i class="' . $icon . '"></i>
                            </a>
                            <button class="btn btn-sm btn-danger delete-consignment" data-id="' . $row->application_id . '">
                                <i class="ti ti-trash"></i>
                            </button>
                         </div>';
                return $view;
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
}

