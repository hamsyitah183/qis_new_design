<?php

namespace App\Http\Controllers;

use App\Models\ConsignmentPermit;
use App\Models\InspectionItem;
use App\Models\IpConsignmentPermit;
use App\Models\Order;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Artisan;

class ApplicationPaymentController extends Controller
{
    //
    public function getView()
    {
        Artisan::call('bayupay:check-pending');
        return view('pages.order.order_list', [
            'title' => 'Order List',
        ]);
    }

    public function getAllOrderList(Request $request)
    {
        $userUuid = authUser()['user']->uuid;
        $type = authUser()['type'];

        $query = Order::with('publicUser')->latest();

        // Apply filter for public users
        if ($type !== 'internal') {
            $query->where('public_user_uuid', $userUuid);
        }

        // Apply Order Status filter (filter by transaction_status since that's what's displayed)
        if ($request->filled('order_status') && $request->order_status != '') {
            $status = $request->input('order_status');
            // Match exact transaction_status name from database (case-sensitive)
            $query->where('transaction_status', $status);
        }

        // Apply Application Type filter
        if ($request->filled('application_type')) {
            $appType = $request->input('application_type');

            $mappedType = match ($appType) {
                'import_permit' => 'Import Permit',
                'inspection' => 'Inspection Certificate',
                'consignment' => 'Consignment Certificate',
                default => null,
            };

            if ($mappedType) {
                $query->where('application_type', $mappedType);
            }
        }

        // Apply Order Number filter
        if ($request->filled('order_number')) {
            $query->where('order_number', 'like', '%' . $request->input('order_number') . '%');
        }

        // Apply FPX Reference filter
        if ($request->filled('fpx_reference')) {
            $query->where('fpx_seller_reference', 'like', '%' . $request->input('fpx_reference') . '%');
        }

        // Apply Date Range filter (created_at)
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->input('start_date'));
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->input('end_date'));
        }

        $dataTable = DataTables::eloquent($query)
            ->addIndexColumn()

            ->editColumn('status', function ($row) {
                $status = $row->transaction_status;

                return match (true) {
                    str_contains($status, 'UNSUCCESSFUL') => $this->badge('danger', $status),
                    str_contains($status, 'SUCCESSFUL') => $this->badge('success', $status),
                    default => '<span class="badge bg-warning">' . e(ucfirst($status)) . '</span>',
                };
            })
            ->addColumn('transaction_date', function ($row) {
                return $row->created_at->format('d-m-Y H:i:s');
            })
            ->addColumn('fpx_reference', function ($row) {
                return $row->fpx_seller_reference ?? '-';
            })
            ->addColumn('payment_reference', function ($row) {
                return $row->seller_ref ?? '-';
            })
            ->addColumn('user_name', function ($row) {
                return $row->publicUser->fullname ?? 'N/A';
            })
            ->addColumn('permit_number', function ($row) {
                $permits = $row->order_details['permits'] ?? [];
                $numbers = collect($permits)->pluck('permit_number')->filter()->values();

                if ($numbers->isEmpty()) {
                    return e($row->application_id ?? '-');
                }

                $count = $numbers->count();
                $list = $numbers->map(fn($n) => e($n))->implode('<br>');

                return '<button type="button" class="btn btn-sm btn-outline-primary" '
                    . 'data-bs-toggle="popover" '
                    . 'data-bs-trigger="focus" '
                    . 'data-bs-html="true" '
                    . 'data-bs-title="Permit Numbers (' . $count . ')" '
                    . 'data-bs-content="' . e($list) . '" '
                    . 'data-permits="' . e($numbers->implode(', ')) . '">'
                    . '<i class="ti ti-eye me-1"></i>' . $count . ' Permit(s)'
                    . '</button>';
            })
            ->addColumn('transaction_data', function ($row) {
                return $row->transaction_data ?? '-';
            })

            // ->editColumn('kod_transaksi', function ($row) {
            //     return $row->kod_transaksi ? '<span class="text-wrap">' . $row->kod_transaksi . '</span>' : '-';
            // })

            ->editColumn('payment_amount', function ($row) {
                return $row->payment_amount ? 'RM ' . number_format($row->payment_amount, 2) : '-';
            })

            ->addColumn('action', function ($row) use ($type) {
                $viewUrl = '/order/' . $row->order_number;

                $view =
                    '<a href="' .
                    $viewUrl .
                    '" class="btn btn-sm btn-primary">
                        <i class="ti ti-eye"></i>
                     </a>';

                $delete = '';

                if ($type === 'internal') {
                    $delete =
                        '<button class="btn btn-sm btn-danger deleteApplication"
                            data-id="' .
                        $row->id .
                        '">
                            <i class="ti ti-trash"></i>
                           </button>';
                }

                return $view . ' ' . $delete;
            });


        if(authUser()['type'] == 'internal') {
            $dataTable->editColumn('kod_transaksi', function ($row) {
                return $row->kod_transaksi ? '<span class="text-wrap">' . $row->kod_transaksi . '</span>' : '-';
            });

        }

        return $dataTable->rawColumns(['status', 'kod_transaksi', 'payment_amount', 'permit_number', 'action'])
            ->make(true);
    }

    private function badge($color, $label)
    {
        return '
            <span class="badge bg-' .
            e($color) .
            '"> ' .
            e($label) .
            '</span>
        ';
    }

    public function orderDetails($order_number)
    {
        if (auth()->check() && auth()->user()->hasRole('boundary officer')) {
            abort(403, 'Unauthorized action. Boundary Officers are restricted from this area.');
        }

        $order = Order::with(['publicUser', 'ipApplication.importer', 'ipApplication.exporter', 'ipApplication.entryPoint', 'inspectionApplication.importer', 'inspectionApplication.exporter', 'inspectionApplication.entryPoint'])
            ->where('order_number', $order_number)
            ->firstOrFail();

        $permitsArray = $order->order_details['permits'] ?? [];
        $permitIds = collect($permitsArray)->pluck('permit_id')->toArray();

        $application = $order->application;

        $permits = match ($order->application_type) {
            'Import Permit' => IpConsignmentPermit::whereIn('id', $permitIds)->get(),
            'Inspection Certificate' => InspectionItem::whereIn('id', $permitIds)->get(),
            'Consignment Certificate' => ConsignmentPermit::whereIn('id', $permitIds)->get(),
            default => collect(),
        };

        return view('pages.order.order_details', [
            'title' => $order_number . ' order details',
            'order' => $order,
            'permits' => $permits,
            'application' => $application,
        ]);
    }
}
