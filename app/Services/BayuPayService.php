<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\Order;
use App\Models\IpConsignmentPermit;

class BayuPayService
{
    public function checkAndUpdatePayment(Order $order, string $kodTransaksi): array
    {
        // 1️⃣ Call BayuPay API
        $response = Http::withToken('test-api')->get(
            'https://bayupay-dummy.geovidia.my/readdata.php',
            ['kod_transaksi' => $kodTransaksi]
        );

        if (!$response->successful()) {
            throw new \Exception('Failed to retrieve payment data');
        }

        $paymentData = $response->json();
        $permits = $order->order_details['permits'] ?? [];

        // 2️⃣ Update order + permits based on status
        switch ($paymentData['transaction_status']) {
            case 'SUCCESSFUL':
                $order->status = 'payment success';

                foreach ($permits as $permit) {
                    IpConsignmentPermit::where('id', $permit['permit_id'])
                        ->update(['status' => 'paid']);
                }
                break;

            case 'UNSUCCESSFUL':
                $order->status = 'payment failed';
                break;

            case 'PENDING FOR AUTHORIZER TO APPROVE':
                $order->status = 'pending authorization';

                foreach ($permits as $permit) {
                    IpConsignmentPermit::where('id', $permit['permit_id'])
                        ->update(['status' => 'payment processing']);
                }
                break;
        }

        // 3️⃣ Save transaction info
        $order->fill([
            'seller_ref'           => $paymentData['seller_ref'] ?? null,
            'fpx_seller_reference' => $paymentData['fpx_seller_reference'] ?? null,
            'name'                 => $paymentData['name'] ?? null,
            'email'                => $paymentData['email'] ?? null,
            'phone'                => $paymentData['phone'] ?? null,
            'payment_amount'       => $paymentData['payment_amount'] ?? null,
            'transaction_data'     => $paymentData['transaction_data'] ?? null,
            'transaction_status'   => $paymentData['transaction_status'] ?? null,
            'kod_transaksi'        => $kodTransaksi,
        ]);

        $order->save();

        return $paymentData;
    }
}
