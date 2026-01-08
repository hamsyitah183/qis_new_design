<?php

namespace App\Http\Controllers;

use App\Events\ApplicationCreatedInternalUser;
use App\Events\ApplicationCreatedPublicUser;
use App\Events\ApplicationDeleted;
use App\Events\PublicUserEvent;
use App\Models\Country;
use App\Models\Exporter;
use App\Models\InternalUser;
use App\Models\IpApplication;
use App\Models\IpConsignmentAttachment;
use App\Models\IpConsignmentPermit;
use App\Models\PublicCode;
use App\Models\PublicUser;
use App\Notifications\ApplicationNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;


class ApplicationController extends Controller
{
    //
    public function show()
    {
        return view('pages.public.new_application');
    }

    public function showthis()
    {
        return view('pages.public.formw');
    }

    public function showallapplicationlist()
    {
        return view('pages.public.application_list');
    }

    public function getallapplicationlist()
    {
        $userUuid = authUser()['user']->uuid;
        $type = authUser()['type'];

        $query = IpApplication::with([
            'user',
            'importer',
            'exporter',
            'entryPoint.districtCode',
        ]);

        // Filter for public users
        if ($type === 'public') {
            $query->where(function ($q) use ($userUuid) {
                $q->where('user_id', $userUuid)
                    ->orWhere('importer_id', $userUuid);
            });
        }

        $datatable = DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('importer', fn($row) => $row->importer->fullname ?? '-')
            ->addColumn('exporter', fn($row) => $row->exporter->name ?? '-')
            ->addColumn('status', function ($row) {
                $status = strtolower($row->status ?? 'pending');

                return match (true) {
                    str_contains($status, 'pending') =>
                    '<span class="badge bg-warning fs-12 p-1">Pending</span>',
                    str_contains($status, 'rejected') =>
                    '<span class="badge bg-danger fs-12 p-1">Rejected</span>',
                    str_contains($status, 'not approved') =>
                    '<span class="badge bg-danger fs-12 p-1">Not Approved</span>',
                    str_contains($status, 'accepted') =>
                    '<span class="badge bg-success fs-12 p-1">Accepted</span>',
                    default =>
                    '<span class="badge bg-secondary fs-12 p-1">' . ucfirst($status) . '</span>',
                };
            })
            ->addColumn('permit_status', function ($row) {
                // Map statuses to colors
                $statusColors = [
                    'processing' => 'bg-info', // blue
                    'rejected'   => 'bg-danger',  // red
                    'completed'  => 'bg-success', // green
                ];

                // Get all permit statuses for this row, lowercase
                $permit_statuses = $row->consignmentPermits->pluck('status')
                    ->map(fn($status) => strtolower($status))
                    ->toArray();

                // Count how many of each status
                $statusCounts = [
                    'processing' => 0,
                    'rejected'   => 0,
                    'completed'  => 0,
                ];

                foreach ($permit_statuses as $status) {
                    if (isset($statusCounts[$status])) {
                        $statusCounts[$status]++;
                    }
                }

                // Build HTML boxes with count inside
                $boxesHtml = '';
                foreach ($statusColors as $status => $color) {
                    $count = $statusCounts[$status] ?? 0;
                    $boxesHtml .= '<div class="badge ' . $color . ' text-white text-center" 
                           style="height:20px; width:20px; display:inline-flex; align-items:center; justify-content:center; margin-right:5px;">
                           ' . $count . '
                       </div>';
                }

                return $boxesHtml;
            })



            ->addColumn('action', function ($row) {
                $url = '/view_application/' . $row->application_id;

                $view = '<a class="btn btn-sm btn-primary viewApplication" href="' . $url . '">
                        <i class="ti ti-eye"></i>
                     </a>';

                $delete = '';

                if (authUser()['type'] === 'internal') {
                    $delete = '<button class="btn btn-sm btn-danger deleteApplication"
                            data-id="' . $row->application_id . '">
                            <i class="ti ti-trash"></i>
                           </button>';
                }

                return $view . ' ' . $delete;
            });


        if ($type === 'internal') {
            $datatable->addColumn('submitted_by', fn($row) => $row->user->fullname ?? '-');
        }

        return $datatable
            ->rawColumns(['status', 'action', 'permit_status'])
            ->make(true);
    }


    public function getAllReviewapplicationList()
    {
        $userUuid = authUser()['user']->uuid;
        $type = authUser()['type'];

        $query = IpApplication::with([
            'user',
            'importer',
            'exporter',
            'entryPoint.districtCode',
        ]);

        if ($type === 'public') {
            $query->where(function ($q) use ($userUuid) {
                $q->where('category_application',  1)
                    ->where('importer_id', $userUuid);
            });
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('importer', fn($row) => $row->importer->fullname ?? '-')
            ->addColumn('exporter', fn($row) => $row->exporter->name ?? '-')
            ->addColumn('submitted_by', fn($row) => $row->user->fullname ?? '-')
            ->addColumn('importer_type', function ($row) {
                $type = $row->category_application == 1 ? 'Others' : 'Self';
                return '<span class="badge bg-primary-transparent fs-13 p-1">' . $type . '</span>';
            })
            ->addColumn('date', fn($row) => $row->eta ? $row->eta->format('Y-m-d') : '-')
            ->addColumn('status', function ($row) {
                $status = strtolower($row->status ?? 'pending');

                return match (true) {
                    str_contains($status, 'pending') => '<span class="badge bg-warning fs-13 p-1">Pending</span>',
                    str_contains($status, 'rejected') => '<span class="badge bg-danger fs-13 p-1">Rejected</span>',
                    str_contains($status, 'success') => '<span class="badge bg-success fs-13 p-1">Success</span>',
                    default => '<span class="badge bg-secondary fs-13 p-1">' . ucfirst($status) . '</span>',
                };
            })
            ->addColumn('action', function ($row) {
                $url = '/view_application/' . $row->application_id;
                return '<a class="btn btn-sm btn-primary viewApplication" href="' . $url . '">View</a>';
            })

            ->rawColumns(['status', 'importer_type', 'action'])
            ->make(true);
    }

    public function deleteApplication($id)
    {
        return DB::transaction(function () use ($id) {

            $application = IpApplication::where('application_id', $id)->firstOrFail();

            $consignments = IpConsignmentPermit::where('application_id', $application->id)->get();

            if ($consignments->isNotEmpty()) {
                $consignmentIds = $consignments->pluck('id');

                // 🔥 Get attachments FIRST
                $attachments = IpConsignmentAttachment::whereIn('permit_id', $consignmentIds)->get();

                foreach ($attachments as $attachment) {
                    if ($attachment->file_path) {
                        // Convert "/storage/import/xxx.pdf" → "import/xxx.pdf"
                        $path = str_replace('/storage/', '', $attachment->file_path);

                        if (Storage::disk('public')->exists($path)) {
                            Storage::disk('public')->delete($path);
                        }
                    }

                    // Delete DB record
                    $attachment->delete();
                }

                // Delete consignments
                IpConsignmentPermit::whereIn('id', $consignmentIds)->delete();
            }
            $user = PublicUser::where('uuid', $application->user_id)->first();
            // Delete application
            $application->delete();

            // Events & notifications
            event(new ApplicationDeleted('Application with ID ' . $id . ' has been deleted.'));

            $users = InternalUser::all();
            Notification::send($users, new ApplicationNotification(
                'Import Application with ID ' . $id . ' has been deleted.',
                authUser()['user']->fullname
            ));

            event(new PublicUserEvent(
                'Your Application with ID ' . $id . ' has been deleted.',
                $user->uuid
            ));

            Notification::send($user, new ApplicationNotification(
                'Import Application with ID ' . $id . ' has been deleted.',
                authUser()['user']->fullname
            ));

            return response()->json([
                'message' => 'Application and all attachments deleted successfully.'
            ]);
        });
    }



    public function verifyapplication()
    {
        $application = IpApplication::with([
            'user',         // submitted by
            'importer',     // importer user
            'exporter',       // exporter record
            'entryPoint.districtCode'
        ])
            ->where('importer_id', auth()->id())
            ->where('category_application', true)
            ->get();

        return view('pages.public.application_review_list', compact('application'));
    }

    public function viewapplication($uuid)
    {

        $application = IpApplication::with([
            'user',         // submitted by
            'importer',     // importer user
            'exporter',       // exporter record
            // 'exporter.country',
            'entryPoint.districtCode'
        ])
            ->where('application_id', $uuid)
            ->orderBy('created_at', 'desc')
            ->firstOrFail();

        $itemId = $application->id;

        // dd($application->consignmentPermits);

        $consignment = IpConsignmentPermit::with([
            'unit',
            'purposeCode'
        ])
            ->where('application_id', $itemId)
            ->get();

        // dd($consignment);

        return view('pages.public.view_application', [
            'application'        => $application,
            'consignment'        => $consignment,
            // 'consignmentDetails' => $consignment[0]->attachments
        ]); //, 'consignment', 'attachment'
    }
    public function editApplication($uuid)
    {

        $application = IpApplication::with([
            'user',         // submitted by
            'importer',     // importer user
            'exporter',       // exporter record
            // 'exporter.country',
            'entryPoint.districtCode',
            'consignmentPermits.attachments'
        ])
            ->where('application_id', $uuid)
            ->orderBy('created_at', 'desc')
            ->firstOrFail();

        if ($application->user_id != authUser()['user']->uuid || $application->status != 'Draft') {
            abort(403, 'Cannot edit this application.');
        }

        $itemId = $application->id;

        // dd($application->consignmentPermits);

        $consignment = IpConsignmentPermit::with([
            'unit',
            'purposeCode'
        ])
            ->where('application_id', $itemId)
            ->get();


        $pubmeasure = PublicCode::where('cate_name', 'unit_measurement')->get();
        $pubpurpose = PublicCode::where('cate_name', 'consignment_purpose')->get();
        $country = Country::where('is_del', false)->get();
        return view('pages.public.edit_permit', compact('pubmeasure', 'pubpurpose', 'country', 'application')); // , compact('')
    }

    public function modalspeItem($id)
    {
        $cons = IpConsignmentPermit::with(['attachments'])
            ->findOrFail($id);

        return response()->json([
            'status' => 'success',
            'data'   => $cons
        ]);
    }


    public function getApplicationDetails($id)
    {
        $type = authUser()['type'];   // 'public' or 'internal'
        $user = authUser()['user'];   // authenticated user object

        // Fetch application and eager load relationships
        $application = IpApplication::where('application_id', $id)
            ->with([
                'user',
                'importer',
                'exporter.countryInfo',
                'entryPoint.districtCode',
                'consignmentPermits.attachments',
                'activity_log.causer'
            ])
            ->firstOrFail();

        if ($type === 'internal') {
            return response()->json($application);
        }

        if ($type === 'public') {
            // Check if user is either the submitter or the importer
            if ($application->user_id !== $user->uuid && $application->importer_id !== $user->uuid) {
                return response()->json([
                    'message' => 'You do not have authority to view this application'
                ], 403);
            }

            return response()->json($application);
        }

        // Default fallback
        return response()->json([
            'message' => 'User type not recognized'
        ], 400);
    }

    function verify_application_permit($id, Request $request)
    {

        $application = IpApplication::where('application_id', $id)->first();
        $notificationUrl = '';
        $message = '';
        $status = '';

        if ($request->input('verified')) {



            $application->logActivity(
                action: 'Approved',
                remark: 'Application Approved By The Importer',
                status: 'Approved'
            );

            $application->logActivity(
                action: 'Pending',
                remark: 'Application Pending',
                status: 'Pending'
            );

            $application->status = 'Pending';
            $application->importer_verify = "Pending";

            $status = 'Pending';
            $message = 'Application is verified and pending admin approval';
        } else if ($request->input('not_verified')) {

            $application->logActivity(
                action: 'Not Approved',
                remark: 'Not Application Not Approved By The Importer',
                status: 'Not Approved'
            );

            $application->status = 'Not Approved';
            $application->importer_verify = "Not Approved";

            $status = 'Not Approved';
            $message = 'Application is not verified by importer';
        } else if ($request->accepted) {
            $application->logActivity(
                action: 'Accepted',
                remark: 'Application Accepted By Admin',
                status: 'Approved'
            );

            $application->status = 'Accepted';
            $application->importer_verify = "Accepted";

            $status = 'Accepted';
            $message = 'Application is accepted';
        } else if ($request->rejected) {
            $application->logActivity(
                action: 'Rejected',
                remark: $request['reason'],
                status: 'Rejected'
            );

            $application->status = 'Rejected';
            $status = 'Rejected';
            $message = 'Application is rejected';
        }
        $application->save();


        $notificationUrl = route('viewApplication', $application->application_id);

        event(new ApplicationCreatedInternalUser('Application with ID ' . $id . ' has been ' . $status . '.'));
        $users = InternalUser::all(); // or filter by role/guard
        Notification::send($users, new ApplicationNotification(
            'Import Application with ID ' . $id . ' has been ' . $status . '.',
            authUser()['user']->fullname,
            $notificationUrl
        ));

        $user = PublicUser::where('uuid', $application->user_id)->first();
        event(new ApplicationCreatedPublicUser(
            'Your Application with ID ' . $id . ' has been ' . $status . '.',
            $user->uuid
        ));
        Notification::send($user, new ApplicationNotification(
            'Import Application with ID ' . $id . ' has been ' . $status . '.',
            authUser()['user']->fullname,
            $notificationUrl
        ));

        if ($application->importer_id != $application->user_id) {
            $importerUser = PublicUser::where('uuid', $application->importer_id)->first();
            event(new ApplicationCreatedPublicUser(
                'Import Application with ID ' . $id . ' has been ' . $status . '.',
                $importerUser->uuid
            ));
            Notification::send($importerUser, new ApplicationNotification(
                'Import Application with ID ' . $id . ' has been ' . $status . '.',
                authUser()['user']->fullname,
                $notificationUrl
            ));
        }

        return response()->json([
            'message' => 'Application is verified'
        ]);
    }
}
