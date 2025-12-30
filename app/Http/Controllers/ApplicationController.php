<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\Exporter;
use App\Models\IpApplication;
use App\Models\IpConsignmentAttachment;
use App\Models\IpConsignmentPermit;
use App\Models\PublicCode;
use Illuminate\Http\Request;
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

        if ($type === 'public') {
            $query->where(function ($q) use ($userUuid) {
                $q->where('user_id', $userUuid)
                    ->orWhere('importer_id', $userUuid);
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
        } else {



            $application->logActivity(
                action: 'Not Approved',
                remark: 'Not Application Not Approved By The Importer',
                status: 'Not Approved'
            );

            $application->status = 'Not Approved';
        }
        if ($request->input('verified')) {
            $application->importer_verify = "Pending";
        }
        $application->save();
        return response()->json([
            'message' => 'Application is verified'
        ]);
    }
}
