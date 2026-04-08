<?php

namespace App\Http\Controllers;

use App\Events\OrderQrUsed;
use App\Models\ConsignmentPermit;
use App\Models\InternalUser;
use App\Models\InspectionItem;
use App\Models\IpConsignmentPermit;
use App\Models\Order;
use App\Models\QrPermitUsage;
use App\Models\QrScanLog;
use App\Services\PermitQrService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Artisan;

class ApplicationPaymentController extends Controller
{
    public function __construct(private readonly PermitQrService $permitQrService)
    {
    }

    //
    public function getView()
    {
        Artisan::call('bayupay:check-pending');


        return view('pages.order.order_list', [
            'title' => 'Order List',
        ]);
    }

    public function getQrScanLogs(Request $request)
    {
        $type = authUser()['type'] ?? null;

        if ($type !== 'internal') {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized.',
            ], 403);
        }

        $logs = QrScanLog::query()
            ->where(function ($query) {
                $query->whereRaw("LOWER(COALESCE(result, '')) IN (?, ?)", ['approved', 'valid'])
                    ->orWhere(function ($legacyQuery) {
                        $legacyQuery->whereNull('result')
                            ->where('is_valid', true);
                    });
            })
            ->latest('scanned_at')
            ->limit(100)
            ->get()
            ->map(function ($log) {
                return [
                    'internal_user_name' => $log->internal_user_name ?? '-',
                    'internal_user_position' => $log->internal_user_position ?? '-',
                    'scanned_value' => $log->scanned_value ?? '-',
                    'is_valid' => true,
                    'result' => 'Valid',
                    'scanned_at' => optional($log->scanned_at)->format('d-m-Y h:i:s A') ?? '-',
                ];
            })
            ->values();

        return response()->json([
            'status' => 'success',
            'logs' => $logs,
        ]);
    }

    public function getEncryptedPermitPayload(Request $request)
    {
        $type = authUser()['type'] ?? null;
        if ($type !== 'internal') {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized.',
            ], 403);
        }

        $permitNumber = trim((string) $request->query('permit_number', ''));
        if ($permitNumber === '') {
            return response()->json([
                'status' => 'error',
                'message' => 'permit_number is required.',
            ], 422);
        }

        try {
            $encrypted = $this->permitQrService->createEncryptedPayload($permitNumber);

            return response()->json([
                'status' => 'success',
                'payload' => $encrypted,
            ]);
        } catch (\Throwable $e) {
            Log::warning('Failed generating encrypted permit payload: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Unable to encrypt permit QR payload.',
            ], 500);
        }
    }

    public function validatePermitApi(Request $request)
    {
        $enforceOneTime = $this->isQrOneTimeEnforced();

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
        $resolvedPermitNumber = $rawPermit;

        $itemName = '-';

        // Check Import Permit
        $ipPermit = IpConsignmentPermit::where(function ($query) use ($normalized, $withoutSlash) {
            $query->whereRaw('UPPER(permit_number) = ?', [$normalized])
                ->orWhereRaw('UPPER(REPLACE(permit_number, "/", "")) = ?', [$withoutSlash]);
        })->first();

        if ($ipPermit) {
            $permitId = $ipPermit->id;
            $applicationType = 'Import Permit';
            $itemName = (string) data_get($ipPermit->consignment_detail, 'item_name', '-');
            $resolvedPermitNumber = (string) ($ipPermit->permit_number ?: $rawPermit);
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
                $itemName = (string) data_get($inspectionItem->consignment_detail, 'item_name', '-');
                $resolvedPermitNumber = (string) ($inspectionItem->permit_number ?: $rawPermit);
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
                $itemName = (string) data_get($consignmentPermit->consignment_detail, 'item_name', '-');
                $resolvedPermitNumber = (string) ($consignmentPermit->permit_number ?: $rawPermit);
            }
        }

        // If no permit found in any table, return invalid
        if (!$permitId) {
            return response()->json([
                'status' => 'success',
                'valid' => false,
                'message' => 'Not listed',
                'permit_number' => '-',
                'item_name' => '-',
                'is_used' => false,
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
                'item_name' => '-',
                'is_used' => false,
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
                'is_used' => false,
            ]);
        }

        // One-time consume is restricted to known internal scanner users only.
        $scannerUserType = trim((string) $request->query('scanner_user_type', ''));
        $scannerUserUuid = trim((string) $request->query('scanner_user_uuid', ''));

        $internalScanner = null;
        if ($scannerUserType === 'internal' && $scannerUserUuid !== '') {
            $internalScanner = InternalUser::query()
                ->where('uuid', $scannerUserUuid)
                ->first();
        }

        if (!$internalScanner) {
            return response()->json([
                'status' => 'success',
                'valid' => false,
                'message' => 'Internal scanner identity is required.',
                'permit_number' => $resolvedPermitNumber,
                'order_number' => $order->order_number,
                'application_type' => $order->application_type,
                'item_name' => $itemName,
                'is_used' => false,
            ]);
        }

        $permitNumberKey = strtoupper(str_replace('/', '', preg_replace('/\s+/', '', $resolvedPermitNumber)));

        if ($enforceOneTime) {
            // Check if this permit_number has already been used.
            $existingUsage = QrPermitUsage::query()
                ->where('application_type', $applicationType)
                ->where('permit_number_key', $permitNumberKey)
                ->first();

            if ($existingUsage) {
                return response()->json([
                    'status' => 'success',
                    'valid' => false,
                    'message' => 'QR code has already been used',
                    'permit_number' => $resolvedPermitNumber,
                    'order_number' => $order->order_number,
                    'application_type' => $order->application_type,
                    'item_name' => $itemName,
                    'is_used' => true,
                ]);
            }

            QrPermitUsage::query()->create([
                'application_type' => $applicationType,
                'permit_number' => $resolvedPermitNumber,
                'permit_number_key' => $permitNumberKey,
                'order_number' => $order->order_number,
                'used_by_uuid' => $internalScanner->uuid,
                'used_at' => now(),
            ]);
        }

        if ($enforceOneTime) {
            try {
                event(new OrderQrUsed(
                    orderNumber: (string) $order->order_number,
                    permitNumber: $resolvedPermitNumber,
                    usedAt: now()->format('d-m-Y h:i:s A'),
                ));
            } catch (\Throwable $e) {
                Log::warning('Failed to broadcast OrderQrUsed event: ' . $e->getMessage());
            }
        }

        return response()->json([
            'status' => 'success',
            'valid' => true,
            'message' => 'Valid',
            'permit_number' => $resolvedPermitNumber,
            'order_number' => $order->order_number,
            'application_type' => $order->application_type,
            'item_name' => $itemName,
            'is_used' => false,
        ]);
    }

    private function isQrOneTimeEnforced(): bool
    {
        // Default true to enforce one-time QR usage in production behavior.
        return filter_var((string) env('QIS_QR_ENFORCE_ONE_TIME', 'true'), FILTER_VALIDATE_BOOL);
    }

    private function recordQrScanLog(Request $request, array $payload): void
    {
        try {
            $userType = (string) $request->query('scanner_user_type', '');
            $scannerUserUuid = trim((string) $request->query('scanner_user_uuid', ''));

            if ($userType !== 'internal' || $scannerUserUuid === '') {
                return;
            }

            $internalUser = InternalUser::query()
                ->where('uuid', $scannerUserUuid)
                ->first();

            if (!$internalUser) {
                return;
            }

            QrScanLog::create([
                'internal_user_uuid' => $internalUser->uuid,
                'internal_user_name' => $internalUser->fullname ?? '-',
                'internal_user_position' => $internalUser->position ?? '-',
                'scanned_value' => $payload['scanned_value'] ?? '-',
                'permit_number' => $payload['permit_number'] ?? '-',
                'order_number' => $payload['order_number'] ?? null,
                'application_type' => $payload['application_type'] ?? null,
                'is_valid' => (bool) ($payload['is_valid'] ?? false),
                'result' => $payload['result'] ?? 'invalid',
                'scanned_at' => now(),
            ]);
        } catch (\Throwable $e) {
            // Keep scan validation resilient even when logging fails.
            return;
        }
    }

    public function orderDetailsApi(Request $request, $order_number)
    {
        try {
            $order = Order::with([
                'publicUser',
                'ipApplication.importer',
                'ipApplication.exporter.countryInfo',
                'inspectionApplication.importer',
                'inspectionApplication.exporter.countryInfo',
                'consignmentApplication.importer.countryInfo',
                'consignmentApplication.exporter',
            ])
                ->where('order_number', $order_number)
                ->firstOrFail();

            $permitsArray = $order->order_details['permits'] ?? [];
            $permitIds = collect($permitsArray)->pluck('permit_id')->toArray();

            $permits = match ($order->application_type) {
                'Import Permit' => IpConsignmentPermit::whereIn('id', $permitIds)->get(),
                'Inspection Certificate' => InspectionItem::whereIn('id', $permitIds)->get(),
                'Consignment Certificate' => ConsignmentPermit::whereIn('id', $permitIds)->get(),
                default => collect(),
            };

            $application = match ($order->application_type) {
                'Import Permit' => $order->ipApplication,
                'Inspection Certificate' => $order->inspectionApplication,
                'Consignment Certificate' => $order->consignmentApplication,
                default => null,
            };

            $applicationId = data_get($order->order_details, 'application.application_id', $application?->application_id ?? '-');

            $permitNumberList = $permits
                ->pluck('permit_number')
                ->filter(fn($permitNumber) => !empty($permitNumber))
                ->implode(', ');

            $permitDetails = $permits->map(function ($permit) {
                return [
                    'permit_number' => $permit->permit_number ?? '-',
                    'item_name' => data_get($permit->consignment_detail, 'item_name', '-'),
                ];
            })->values()->all();

            [$exporterName, $exporterPhone, $exporterAddress, $exporterCountry] = $this->resolveExporterData($order, $application);
            [$importerName, $importerAddress] = $this->resolveImporterData($order, $application);

            return response()->json([
                'status' => 'success',
                'header' => [
                    'order_number' => $order->order_number,
                ],
                'order_details' => [
                    'order_number' => $order->order_number ?? '-',
                    'order_status' => $order->status ?? '-',
                    'application_id' => $applicationId ?? '-',
                    'permit_id' => $permitNumberList !== '' ? $permitNumberList : '-',
                ],
                'payment_details' => [
                    'seller_ref' => $order->seller_ref ?? '-',
                    'fpx_seller_reference' => $order->fpx_seller_reference ?? '-',
                    'name' => $order->name ?? '-',
                    'email' => $order->email ?? '-',
                    'phone' => $order->phone ?? '-',
                    'payment_amount' => $order->payment_amount !== null
                        ? 'RM ' . number_format((float) $order->payment_amount, 2)
                        : '-',
                    'transaction_data' => $order->transaction_data ?? '-',
                    'transaction_status' => $order->transaction_status ?? '-',
                ],
                'application_details' => [
                    'application_id' => $applicationId ?? '-',
                    'exporter_name' => $exporterName,
                    'exporter_number_phone' => $exporterPhone,
                    'exporter_address' => $exporterAddress,
                    'exporter_country' => $exporterCountry,
                    'importer_name' => $importerName,
                    'importer_address' => $importerAddress,
                ],
                'permit_details' => $permitDetails,
                'qr_info' => [
                    'is_used' => $order->qr_used_at !== null,
                    'used_at' => $order->qr_used_at?->format('d-m-Y h:i:s A'),
                    'used_by_uuid' => $order->qr_used_by_uuid,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Order not found.',
            ], 404);
        }
    }

    private function resolveExporterData(Order $order, $application): array
    {
        if (!$application) {
            return ['-', '-', '-', '-'];
        }

        if (in_array($order->application_type, ['Import Permit', 'Inspection Certificate'])) {
            return [
                $application->exporter?->name ?? '-',
                $application->exporter?->phone_no ?? '-',
                $application->exporter?->address ?? '-',
                $application->exporter?->countryInfo?->name ?? '-',
            ];
        }

        if ($order->application_type === 'Consignment Certificate') {
            $addressParts = array_filter([
                $application->exporter?->address_1 ?? null,
                $application->exporter?->address_2 ?? null,
                $application->exporter?->postcode ?? null,
                $application->exporter?->district ?? null,
                $application->exporter?->state ?? null,
            ]);

            return [
                $application->exporter?->fullname ?? '-',
                $application->exporter?->phone_number ?? '-',
                !empty($addressParts) ? implode(', ', $addressParts) : '-',
                '-',
            ];
        }

        return ['-', '-', '-', '-'];
    }

    private function resolveImporterData(Order $order, $application): array
    {
        if (!$application) {
            return ['-', '-'];
        }

        if (in_array($order->application_type, ['Import Permit', 'Inspection Certificate'])) {
            $addressParts = array_filter([
                $application->importer?->address_1 ?? null,
                $application->importer?->address_2 ?? null,
                $application->importer?->postcode ?? null,
                $application->importer?->district ?? null,
                $application->importer?->state ?? null,
            ]);

            return [
                $application->importer?->fullname ?? '-',
                !empty($addressParts) ? implode(', ', $addressParts) : '-',
            ];
        }

        if ($order->application_type === 'Consignment Certificate') {
            return [
                $application->importer?->name ?? '-',
                $application->importer?->address ?? '-',
            ];
        }

        return ['-', '-'];
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
                return $this->resolvePermitNumbersForOrder($row);
            })
            ->addColumn('qr_used_at', function ($row) {
                if (empty($row->qr_used_at)) {
                    return null;
                }

                if ($row->qr_used_at instanceof \DateTimeInterface) {
                    return $row->qr_used_at->format('d-m-Y h:i:s A');
                }

                return Carbon::parse((string) $row->qr_used_at)->format('d-m-Y h:i:s A');
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

    public function completeQrScan(Request $request)
    {
        try {
            $permitNumber = trim((string) $request->input('permit_number', ''));
            $orderNumber = trim((string) $request->input('order_number', ''));
            $applicationType = trim((string) $request->input('application_type', ''));
            $inspectionStatus = trim((string) $request->input('inspection_status', ''));
            $scannerUserUuid = trim((string) $request->query('scanner_user_uuid', ''));
            $scannerUserType = trim((string) $request->query('scanner_user_type', ''));

            if ($permitNumber === '' || $orderNumber === '' || $applicationType === '') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'permit_number, order_number, and application_type are required.',
                ], 422);
            }

            if ($inspectionStatus === '' || !in_array($inspectionStatus, ['approved', 'rejected'])) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'inspection_status must be either "approved" or "rejected".',
                ], 422);
            }

            if ($scannerUserType !== 'internal' || $scannerUserUuid === '') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Internal scanner identity is required.',
                ], 403);
            }

            $internalUser = InternalUser::query()
                ->where('uuid', $scannerUserUuid)
                ->first();

            if (!$internalUser) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid scanner user.',
                ], 403);
            }

            // Verify the order exists
            $order = Order::where('order_number', $orderNumber)
                ->where('application_type', $applicationType)
                ->first();

            if (!$order) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Order not found.',
                ], 404);
            }

            // Only log to Application Log if inspection was approved
            if ($inspectionStatus === 'approved') {
                QrScanLog::create([
                    'internal_user_uuid' => $internalUser->uuid,
                    'internal_user_name' => $internalUser->fullname ?? '-',
                    'internal_user_position' => $internalUser->position ?? '-',
                    'scanned_value' => $permitNumber,
                    'permit_number' => $permitNumber,
                    'order_number' => $orderNumber,
                    'application_type' => $applicationType,
                    'is_valid' => true,
                    'result' => 'approved',
                    'scanned_at' => now(),
                ]);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Inspection result recorded.',
            ]);
        } catch (\Throwable $e) {
            Log::error('Error in completeQrScan: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to log scan completion.',
            ], 500);
        }
    }
}
