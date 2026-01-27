<?php

namespace App\Http\Controllers;

use App\Models\InspectionItem;
use App\Models\IpConsignmentPermit;
use App\Models\Order;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ApplicationPaymentController extends Controller
{
    //
    public function getView()
    {
        return view('pages.order.order_list', [
            'title' => 'Order List',
        ]);
    }

    public function getAllOrderList()
    {
        $userUuid = authUser()['user']->uuid;
        $type = authUser()['type'];

        $query = Order::query();

        // Apply filter for public users
        if ($type !== 'internal') {
            $query->where('public_user_uuid', $userUuid);
        }

        $dataTable =  DataTables::eloquent($query)
            ->addIndexColumn()

            ->editColumn('status', function ($row) {
                $status = $row->transaction_status;

                return match (true) {
                    str_contains($status, 'UNSUCCESSFUL') => $this->badge('danger', $status),
                    str_contains($status, 'SUCCESSFUL') => $this->badge('success', $status),
                    default => '<span class="badge bg-warning">' . e(ucfirst($status)) . '</span>',
                };
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

            return $dataTable->rawColumns(['status', 'kod_transaksi', 'payment_amount', 'action'])
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
        $order = Order::with(['publicUser', 'ipApplication.importer', 'ipApplication.exporter', 'ipApplication.entryPoint', 'inspectionApplication.importer', 'inspectionApplication.exporter', 'inspectionApplication.entryPoint'])
            ->where('order_number', $order_number)
            ->firstOrFail();

        $permitsArray = $order->order_details['permits'] ?? [];
        $permitIds = collect($permitsArray)->pluck('permit_id')->toArray();

        $application = $order->application; 

        $permits = match ($order->application_type) {
            'Import Permit' => IpConsignmentPermit::whereIn('id', $permitIds)->get(),
            'Inspection Certificate' => InspectionItem::whereIn('id', $permitIds)->get(),
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
