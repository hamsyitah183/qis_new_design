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
        if (auth()->user()->doa_verified) {
            return view('pages.public.new_application');
        } else {
            return view('pages.public.wait_for_verified');
        }
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


                    str_contains($status, 'fully processed') =>
                    '<span class="badge bg-success fs-12 p-1">Fully Processed</span>',
                    str_contains($status, 'clerk verified') =>
                    '<span class="badge bg-info fs-12 p-1">Clerk Verified</span>',
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

    public function verify_application_permit($id, Request $request)
    {
        $application = IpApplication::where('application_id', $id)->firstOrFail();

        // Centralized messages per status
        $statusMessages = [
            'Clerk Review In-Progress' => [
                'public'   => 'Your application has been verified by the importer and is now pending clerk review.',
                'internal' => 'Application verified by importer and awaiting clerk review.',
                'notify'   => 'Import application is now awaiting clerk review.',
            ],
            'Not Approved' => [
                'public'   => 'Your application was not approved by the importer.',
                'internal' => 'Application was not approved by the importer.',
                'notify'   => 'Import application was not approved by the importer.',
            ],
            'Clerk Verified' => [
                'public'   => 'Your application has been approved by the clerk.',
                'internal' => 'Application approved by clerk.',
                'notify'   => 'Import application has been approved by clerk.',
            ],
            'Clerk Rejected' => [
                'public'   => 'Your application has been rejected by the clerk.',
                'internal' => 'Application rejected by clerk.',
                'notify'   => 'Import application has been rejected by clerk.',
            ],
        ];

        $status = null;

        /**
         * =====================
         * STATUS HANDLING
         * =====================
         */
        if ($request->input('verified')) {

            $application->logActivity(
                action: 'Importer Verified',
                remark: 'Application verified by importer',
                status: 'Clerk Review In-Progress'
            );

            $application->status = 'Clerk Review In-Progress';
            $application->importer_verify = 'Verified';
            $status = 'Clerk Review In-Progress';
        } elseif ($request->input('not_verified')) {

            $application->logActivity(
                action: 'Importer Rejected',
                remark: 'Application rejected by importer',
                status: 'Not Approved'
            );

            $application->status = 'Not Approved';
            $application->importer_verify = 'Not Approved';
            $status = 'Not Approved';
        } elseif ($request->accepted) {

            $application->logActivity(
                action: 'Clerk Approved',
                remark: 'Application approved by clerk',
                status: 'Clerk Verified'
            );

            $application->status = 'Clerk Verified';
            $application->importer_verify = 'Accepted';
            $status = 'Clerk Verified';
        } elseif ($request->rejected) {

            $application->logActivity(
                action: 'Clerk Rejected',
                remark: $request->input('reason'),
                status: 'Clerk Rejected'
            );

            $application->status = 'Clerk Rejected';
            $status = 'Clerk Rejected';
        }

        // Save application state
        $application->save();

        // Safety check
        if (!$status || !isset($statusMessages[$status])) {
            return response()->json([
                'message' => 'Invalid application status.'
            ], 400);
        }

        $messages = $statusMessages[$status];
        $notificationUrl = route('viewApplication', $application->application_id);

        /**
         * =====================
         * INTERNAL USER EVENT + NOTIFICATION
         * =====================
         */
        event(new ApplicationCreatedInternalUser($messages['internal']));

        $internalUsers = InternalUser::all();
        Notification::send($internalUsers, new ApplicationNotification(
            $messages['notify'],
            authUser()['user']->fullname,
            $notificationUrl
        ));

        /**
         * =====================
         * PUBLIC USER (APPLICANT)
         * =====================
         */
        $publicUser = PublicUser::where('uuid', $application->user_id)->first();

        event(new ApplicationCreatedPublicUser(
            $messages['public'],
            $publicUser->uuid
        ));

        Notification::send($publicUser, new ApplicationNotification(
            $messages['public'],
            authUser()['user']->fullname,
            $notificationUrl
        ));

        /**
         * =====================
         * IMPORTER (IF DIFFERENT USER)
         * =====================
         */
        if ($application->importer_id !== $application->user_id) {
            $importerUser = PublicUser::where('uuid', $application->importer_id)->first();

            event(new ApplicationCreatedPublicUser(
                $messages['public'],
                $importerUser->uuid
            ));

            Notification::send($importerUser, new ApplicationNotification(
                $messages['public'],
                authUser()['user']->fullname,
                $notificationUrl
            ));
        }

        /**
         * =====================
         * RESPONSE
         * =====================
         */
        return response()->json([
            'message' => 'Application status updated successfully.',
            'status'  => $status
        ]);
    }


    function show_exporter()
    {
        $pubmeasure = PublicCode::where('cate_name', 'unit_measurement')->get();
        $pubpurpose = PublicCode::where('cate_name', 'consignment_purpose')->get();
        $country = country::where('is_del', false)->get();
        return view('pages.public.exporter_list',  compact('pubmeasure', 'pubpurpose', 'country'));
    }
}
