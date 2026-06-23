<?php

namespace App\Console\Commands;

use App\Models\ConsignmentPermit;
use App\Models\InspectionItem;
use App\Models\IpConsignmentPermit;
use Illuminate\Console\Command;
use App\Models\Order;
use App\Services\BayuPayService;
use Illuminate\Support\Facades\Log;

class CheckPendingPayments extends Command
{
    
    protected $signature = 'bayupay:check-pending';
    protected $description = 'Check pending and processing payments and update their status';

    protected BayuPayService $bayuPay;
    
    // Define invalid kod_transaksi values
    protected array $invalidKodTransaksi = [
        null,
        '',
        'backend update',
        'manual',
        'N/A',
        'unknown',
    ];

    public function __construct(BayuPayService $bayuPay)
    {
        parent::__construct();
        $this->bayuPay = $bayuPay;
    }

    /**
     * Check if kod_transaksi is valid (not empty and not a placeholder)
     */
    private function isValidKodTransaksi(?string $kodTransaksi): bool
    {
        return !empty($kodTransaksi) && !in_array($kodTransaksi, $this->invalidKodTransaksi);
    }

    /**
     * Check if kod_transaksi is empty or should be treated as empty
     */
    private function isEmptyKodTransaksi(?string $kodTransaksi): bool
    {
        return empty($kodTransaksi) || in_array($kodTransaksi, $this->invalidKodTransaksi);
    }

    public function handle(): int
    {
        Log::info('bayupay:check-pending started', [
            'triggered_at' => now(),
            'triggered_by' => 'dashboard or scheduler',
        ]);

        /**
         * 1️⃣ Check: PENDING FOR AUTHORIZER TO APPROVE
         */
        $pendingOrders = Order::where('transaction_status', 'PENDING FOR AUTHORIZER TO APPROVE')->get();

        if ($pendingOrders->isEmpty()) {
            $this->info('No pending authorization orders found.');
        } else {
            foreach ($pendingOrders as $order) {
                try {
                    // Check if kod_transaksi is valid
                    if ($this->isValidKodTransaksi($order->kod_transaksi)) {
                        // Normal flow: use kod_transaksi
                        $this->bayuPay->checkAndUpdatePayment($order, $order->kod_transaksi);
                    } else {
                        // Invalid kod_transaksi: use alternative method
                        Log::info('Using fallback payment check for order with invalid kod_transaksi.', [
                            'order_number' => $order->order_number,
                            'kod_transaksi' => $order->kod_transaksi ?? 'null',
                        ]);
                        
                        $this->bayuPay->checkAndUpdatePaymentWithoutTransactionCode($order);
                    }
                    
                    $this->completeApplicationIfAllPermitsPaid($order);

                    Log::info('Pending authorization order checked.', [
                        'order_number' => $order->order_number,
                        'status' => $order->status,
                    ]);
                } catch (\Throwable $e) {
                    Log::error('Error checking pending authorization order.', [
                        'order_number' => $order->order_number,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        /**
         * 2️⃣ Check: PAYMENT PROCESSING (no transaction code or backend update)
         */
        $processingOrders = Order::where('transaction_status', 'PAYMENT PROCESSING')->get();

        if ($processingOrders->isEmpty()) {
            $this->info('No processing orders without transaction code found.');
        } else {
            foreach ($processingOrders as $order) {
                try {
                    $this->bayuPay->checkAndUpdatePaymentWithoutTransactionCode($order);
                    $this->completeApplicationIfAllPermitsPaid($order);

                    Log::info('Processing order checked.', [
                        'order_number' => $order->order_number,
                        'status' => $order->status,
                    ]);
                } catch (\Throwable $e) {
                    Log::error('Error checking processing order.', [
                        'order_number' => $order->order_number,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        $this->info('BayuPay payment check completed.');

        return Command::SUCCESS;
    }

    private function completeApplicationIfAllPermitsPaid(Order $order): void
    {
        $application = $order->application;

        if (!$application) {
            return;
        }

        $allPaid = match ($order->application_type) {
            'Import Permit' => IpConsignmentPermit::where('application_id', $application->id)
                ->where('status', '!=', 'paid')
                ->doesntExist(),

            'Inspection Certificate' => InspectionItem::where('application_id', $application->id)
                ->where('status', '!=', 'paid')
                ->doesntExist(),

            'Consignment Certificate' => ConsignmentPermit::where('application_id', $application->id)
                ->where('status', '!=', 'paid')
                ->doesntExist(),

            default => false,
        };

        if ($allPaid && $application->status !== 'Completed') {
            $application->update(['status' => 'Completed']);

            $application->logActivity(
                action: 'Application Completed', 
                remark: 'All permits under this application have been fully paid', 
                status: 'Completed'
            );

            Log::info('Application marked as completed.', [
                'application_id' => $application->id,
                'application_type' => $order->application_type,
            ]);
        }
    }
}