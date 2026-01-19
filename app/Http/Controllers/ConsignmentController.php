<?php

namespace App\Http\Controllers;

use App\Models\ConsignmentApplication;
use Illuminate\Http\Request;
use App\Models\PublicCode;
use App\Models\Country;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use Str;

class ConsignmentController extends Controller
{
    //
    function getView()
    {
        $pubmeasure = PublicCode::where('cate_name', 'unit_measurement')->get();
        $pubpurpose = PublicCode::where('cate_name', 'consignment_purpose')->get();
        $country = Country::where('is_del', false)->get();
        return view('pages.public.consignmentapp', compact('pubmeasure', 'pubpurpose', 'country'));
    }


    function getViewOther()
    {
        $pubmeasure = PublicCode::where('cate_name', 'unit_measurement')->get();
        $pubpurpose = PublicCode::where('cate_name', 'consignment_purpose')->get();
        $country = Country::where('is_del', false)->get();
        return view('pages.public.consignmentappOther', compact('pubmeasure', 'pubpurpose', 'country'));
    }

    function saveApplicationConsignment(Request $request)
    {
        $exporter = $request->exporterData
            ? json_decode($request->exporterData, true)
            : null;

        $importer = $request->importerData
            ? json_decode($request->importerData, true)
            : null;

        $permit = $request->permitDetails
            ? json_decode($request->permitDetails, true)
            : [];
        $application = ConsignmentApplication::create([
            'application_id' => Str::uuid(),
            'eta' => $permit['eta'] ?? null,
            'transport_type' => $permit['tranType'] ?? null,
            'entry_point' => $permit['entrypoint'] ?? null,
            'category_application' => $permit['applCate'] ?? null,
            'user_id' => Auth::user()->uuid,
            'exporter_id' => $exporter['id'] ?? null,
            'importer_id' => $importer['uuid'] ?? null,
            'importer_detail' => $importer,
            'status' => '',
            'importer_verify' => '',
        ]);
    }
    public function showallconsignmentlist()
    {
        return view('pages.public.consignment_list');
    }

    public function getallconsignmentlist()
    {
        $userUuid = authUser()['user']->uuid;
        $type = authUser()['type'];

        $query = ConsignmentApplication::with([
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
            ->addColumn('eta', fn($row) => $row->eta ? $row->eta->format('Y-m-d') : '-')
            ->addColumn('transport_type', fn($row) => $row->transport_type ?? '-')
            ->addColumn('entry_point', function ($row) {
                if ($row->entryPoint) {
                    $district = $row->entryPoint->districtCode->name ?? '';
                    $point = $row->entryPoint->entry_name ?? '';
                    return $district . ($district && $point ? ' - ' : '') . $point;
                }
                return '-';
            })
            ->addColumn('category_application', function ($row) {
                $category = $row->category_application == 1 ? 'Others' : 'Self';
                return '<span class="badge bg-primary-transparent fs-12 p-1">' . $category . '</span>';
            })
            ->addColumn('importer_verify', function ($row) {
                $verify = $row->importer_verify ?? 'pending';
                $status = strtolower($verify);

                return match (true) {
                    str_contains($status, 'verified') => '<span class="badge bg-success fs-12 p-1">Verified</span>',
                    str_contains($status, 'not approved') => '<span class="badge bg-danger fs-12 p-1">Not Approved</span>',
                    str_contains($status, 'accepted') => '<span class="badge bg-success fs-12 p-1">Accepted</span>',
                    default => '<span class="badge bg-warning fs-12 p-1">Pending</span>',
                };
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
                $permit_statuses = $row->consignmentPermits->pluck('status')
                    ->map(fn($status) => strtolower($status))
                    ->toArray();

                // Count how many of each status
                $statusCounts = array_fill_keys(array_keys($statusColors), 0);

                foreach ($permit_statuses as $status) {
                    if (isset($statusCounts[$status])) {
                        $statusCounts[$status]++;
                    } else {
                        // Handle unknown statuses by adding them dynamically or ignoring
                        // allocating a default color if not found
                    }
                }

                // Build HTML boxes with count inside
                $boxesHtml = '';
                foreach ($statusColors as $status => $color) {
                    $count = $statusCounts[$status] ?? 0;
                    if ($count > 0) {
                        $boxesHtml .= '<div class="badge ' . $color . ' text-white text-center" data-bs-toggle="tooltip"
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
                $url = '/view_consignment/' . $row->application_id;

                $view = '<a class="btn btn-sm btn-primary viewConsignment" href="' . $url . '">
                        <i class="ti ti-eye"></i>
                     </a>';

                return $view;
            });

        if ($type === 'internal') {
            $datatable->addColumn('submitted_by', fn($row) => $row->user->fullname ?? '-');
        }

        return $datatable
            ->rawColumns(['action', 'permit_status', 'category_application', 'importer_verify'])
            ->make(true);
    }
}