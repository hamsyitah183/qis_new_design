<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use App\Services\BayuPayService;
use Illuminate\Support\Facades\Log;

class CheckPendingPayments extends Command
{
    protected $signature = 'bayupay:check-pending';
    protected $description = 'Check all pending authorization orders and update their payment status';

    protected BayuPayService $bayuPay;

    public function __construct(BayuPayService $bayuPay)
    {
        parent::__construct();
        $this->bayuPay = $bayuPay;
    }

    public function handle(): int
    {
        $orders = Order::where('status', 'pending authorization')->get();

        if ($orders->isEmpty()) {
            $this->info('No pending authorization orders found.');
            return 0;
        }

        foreach ($orders as $order) {
            try {
                $kodTransaksi = $order->kod_transaksi;

                if (!$kodTransaksi) {
                    Log::warning("Order {$order->order_number} missing kod_transaksi, skipping.");
                    continue;
                }

                $paymentData = $this->bayuPay->checkAndUpdatePayment($order, $kodTransaksi);

                Log::info("Order {$order->order_number} checked. Status: {$order->status}");

            } catch (\Exception $e) {
                Log::error("Error checking order {$order->order_number}: " . $e->getMessage());
            }
        }

        return 0;
    }
}
