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

    public function validatePermitApi(Request $request)
    {
        $rawPermit = (string) $request->query('permit_number', '');
        $rawPermit = trim($rawPermit);

        if ($rawPermit === '') {
            return response()->json([
                'status' => 'error',
                'message' => 'permit_number is required.',
            ], 422);
        }

        // Normalize permit_number: handle IPO/123 and IPO123 formats
        $normalized = strtoupper(preg_replace('/\s+/', '', $rawPermit));
        $withoutSlash = str_replace('/', '', $normalized);

        // Try each permit table to find the matching permit
        $permitId = null;
        $applicationType = null;

        // Check Import Permit
        $ipPermit = IpConsignmentPermit::where(function ($query) use ($normalized, $withoutSlash) {
            $query->whereRaw('UPPER(permit_number) = ?', [$normalized])
                ->orWhereRaw('UPPER(REPLACE(permit_number, "/", "")) = ?', [$withoutSlash]);
        })->first();

        if ($ipPermit) {
            $permitId = $ipPermit->id;
            $applicationType = 'Import Permit';
        }

        // Check Inspection Certificate
        if (!$permitId) {
            $inspectionItem = InspectionItem::where(function ($query) use ($normalized, $withoutSlash) {
                $query->whereRaw('UPPER(permit_number) = ?', [$normalized])
                    ->orWhereRaw('UPPER(REPLACE(permit_number, "/", "")) = ?', [$withoutSlash]);
            })->first();

            if ($inspectionItem) {
                $permitId = $inspectionItem->id;
                $applicationType = 'Inspection Certificate';
            }
        }

        // Check Consignment Certificate
        if (!$permitId) {
            $consignmentPermit = ConsignmentPermit::where(function ($query) use ($normalized, $withoutSlash) {
                $query->whereRaw('UPPER(permit_number) = ?', [$normalized])
                    ->orWhereRaw('UPPER(REPLACE(permit_number, "/", "")) = ?', [$withoutSlash]);
            })->first();

            if ($consignmentPermit) {
                $permitId = $consignmentPermit->id;
                $applicationType = 'Consignment Certificate';
            }
        }

        // If no permit found in any table, return invalid
        if (!$permitId) {
            return response()->json([
                'status' => 'success',
                'valid' => false,
                'message' => 'Not listed',
                'permit_number' => '-',
            ]);
        }

        // Find the order containing this permit
        $order = Order::where('application_type', $applicationType)
            ->latest()
            ->get()
            ->first(function ($ord) use ($permitId) {
                $permits = $ord->order_details['permits'] ?? [];
                foreach ($permits as $perm) {
                    if ((int) $perm['permit_id'] === (int) $permitId) {
                        return true;
                    }
                }
                return false;
            });

        if (!$order) {
            return response()->json([
                'status' => 'success',
                'valid' => false,
                'message' => 'Not listed',
                'permit_number' => '-',
            ]);
        }

        // Fallback: also search by permit_number directly in order_details if not found by ID
        if (!$order) {
            $order = Order::where('application_type', $applicationType)
                ->latest()
                ->get()
                ->first(function ($ord) use ($normalized, $withoutSlash) {
                    $permits = $ord->order_details['permits'] ?? [];
                    foreach ($permits as $perm) {
                        $permNum = strtoupper($perm['permit_number'] ?? '');
                        $permNumNoSlash = str_replace('/', '', $permNum);
                        if ($permNum === $normalized || $permNumNoSlash === $withoutSlash) {
                            return true;
                        }
                    }
                    return false;
                });
        }

        if (!$order) {
            return response()->json([
                'status' => 'success',
                'valid' => false,
                'message' => 'Not listed',
                'permit_number' => '-',
            ]);
        }

        return response()->json([
            'status' => 'success',
            'valid' => true,
            'message' => 'Valid',
            'permit_number' => $rawPermit,
            'order_number' => $order->order_number,
            'application_type' => $order->application_type,
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
                return $this->resolvePermitNumbersForOrder($row);
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

    private function resolvePermitNumbersForOrder(Order $order): string
    {
        $permitIds = collect($order->order_details['permits'] ?? [])
            ->pluck('permit_id')
            ->filter()
            ->values()
            ->all();

        if (empty($permitIds)) {
            return '-';
        }

        $permitNumbers = match ($order->application_type) {
            'Import Permit' => IpConsignmentPermit::whereIn('id', $permitIds)->pluck('permit_number'),
            'Inspection Certificate' => InspectionItem::whereIn('id', $permitIds)->pluck('permit_number'),
            'Consignment Certificate' => ConsignmentPermit::whereIn('id', $permitIds)->pluck('permit_number'),
            default => collect(),
        };

        $formatted = $permitNumbers
            ->filter(fn($permitNumber) => !empty($permitNumber))
            ->implode(', ');

        return $formatted !== '' ? $formatted : '-';
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
