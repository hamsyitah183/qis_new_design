<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use App\Services\BayuPayService;
use Illuminate\Support\Facades\Log;

class CheckPendingPayments extends Command
{
    protected $signature = 'bayupay:check-pending';
    protected $description = 'Check pending and processing payments and update their status';

    protected BayuPayService $bayuPay;

    public function __construct(BayuPayService $bayuPay)
    {
        parent::__construct();
        $this->bayuPay = $bayuPay;
    }

    public function handle(): int
    {
        /**
         * 1️⃣ Check: PENDING FOR AUTHORIZER TO APPROVE
         */
        $pendingOrders = Order::where('transaction_status', 'PENDING FOR AUTHORIZER TO APPROVE')->get();

        if ($pendingOrders->isEmpty()) {
            $this->info('No pending authorization orders found.');
        } else {
            foreach ($pendingOrders as $order) {
                try {
                    if (empty($order->kod_transaksi)) {
                        Log::warning('Order missing kod_transaksi, skipping.', [
                            'order_number' => $order->order_number,
                        ]);
                        continue;
                    }

                    $this->bayuPay->checkAndUpdatePayment($order, $order->kod_transaksi);

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
         * 2️⃣ Check: PAYMENT PROCESSING (no transaction code)
         */
        $processingOrders = Order::where('transaction_status', 'PAYMENT PROCESSING')->get();

        if ($processingOrders->isEmpty()) {
            $this->info('No processing orders without transaction code found.');
        } else {
            foreach ($processingOrders as $order) {
                try {
                    $this->bayuPay->checkAndUpdatePaymentWithoutTransactionCode($order);

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
}
