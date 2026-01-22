<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\Order;
use App\Models\IpConsignmentPermit;
use Illuminate\Support\Facades\Log;

class BayuPayService
{
    public function checkAndUpdatePayment(Order $order, string $kodTransaksi): array
    {
        $response = Http::withToken('test-api')->get('https://bayupay-dummy.geovidia.my/readdata.php', ['kod_transaksi' => $kodTransaksi]);

        if (!$response->successful()) {
            throw new \Exception('Failed to retrieve payment data');
        }

        $paymentData = $response->json();
        // Log::info('lepas 1', $paymentData);

        $permits = $order->order_details['permits'] ?? [];
        // Log::info('lepas 2', $permits);
        if ($order->application_type == 'Import Permit') {
            $application = $order->ipApplication;
        }

        Log::info('lepas 2', [
            'transaction_status' => $paymentData['transaction_status'],
        ]);

        switch ($paymentData['transaction_status']) {
            case 'SUCCESSFUL':
                $order->status = 'payment success';

                foreach ($permits as $permit) {
                    IpConsignmentPermit::where('id', $permit['permit_id'])->update(['status' => 'paid']);
                }
                // $application->logActivity(action: 'User Payment', remark: 'The order is successfully paid', status: 'User Payment');
                break;

            case 'UNSUCCESSFUL':
                $order->status = 'payment failed';
                foreach ($permits as $permit) {
                    IpConsignmentPermit::where('id', $permit['permit_id'])->update(['status' => 'pending for payment']);
                }
                // $application->logActivity(action: 'User Payment', remark: 'The order is unsuccessfully paid', status: 'User Payment');
                break;

            case 'PENDING FOR AUTHORIZER TO APPROVE':
                $order->status = 'pending authorization';

                foreach ($permits as $permit) {
                    IpConsignmentPermit::where('id', $permit['permit_id'])->update(['status' => 'payment processing']);
                }
                // $application->logActivity(action: 'User Payment', remark: 'The order is pending for authorization', status: 'User Payment');
                break;
        }

        $order->fill([
            'seller_ref' => $paymentData['seller_ref'] ?? null,
            'fpx_seller_reference' => $paymentData['fpx_seller_reference'] ?? null,
            'name' => $paymentData['name'] ?? null,
            'email' => $paymentData['email'] ?? null,
            'phone' => $paymentData['phone'] ?? null,
            'payment_amount' => $paymentData['payment_amount'] ?? null,
            'transaction_data' => $paymentData['transaction_data'] ?? null,
            'transaction_status' => $paymentData['transaction_status'] ?? null,
            'kod_transaksi' => $kodTransaksi,
        ]);

        $order->save();

        return $paymentData;
    }

    public function checkAndUpdatePaymentWithoutTransactionCode(Order $order): array
    {
        $response = Http::withToken('test-api')->get('https://bayupay-dummy.geovidia.my/readtransaction.php', ['sid' => $order->sid, 'itn' => $order->itn, 'rn' => $order->order_number]);

        if (!$response->successful()) {
            throw new \Exception('Failed to retrieve payment data');
        }

        $paymentData = $response->json();
        // Log::info('lepas 1', $paymentData);

        $permits = $order->order_details['permits'] ?? [];
        // Log::info('lepas 2', $permits);
        if ($order->application_type == 'Import Permit') {
            $application = $order->ipApplication;
        }

        Log::info('lepas 2', [
            'transaction_status' => $paymentData['transaction_status'],
        ]);

        switch ($paymentData['transaction_status']) {
            case 'SUCCESSFUL':
                $order->status = 'payment success';

                foreach ($permits as $permit) {
                    IpConsignmentPermit::where('id', $permit['permit_id'])->update(['status' => 'paid']);
                }
                // $application->logActivity(action: 'User Payment', remark: 'The order is successfully paid', status: 'User Payment');
                break;

            case 'UNSUCCESSFUL':
                $order->status = 'payment failed';
                // $application->logActivity(action: 'User Payment', remark: 'The order is unsuccessfully paid', status: 'User Payment');
                break;

            case 'PENDING FOR AUTHORIZER TO APPROVE':
                $order->status = 'pending authorization';

                foreach ($permits as $permit) {
                    IpConsignmentPermit::where('id', $permit['permit_id'])->update(['status' => 'payment processing']);
                }
                // $application->logActivity(action: 'User Payment', remark: 'The order is pending for authorization', status: 'User Payment');
                break;
        }

        $order->fill([
            'seller_ref' => $paymentData['seller_ref'] ?? null,
            'fpx_seller_reference' => $paymentData['fpx_seller_reference'] ?? null,
            'name' => $paymentData['name'] ?? null,
            'email' => $paymentData['email'] ?? null,
            'phone' => $paymentData['phone'] ?? null,
            'payment_amount' => $paymentData['payment_amount'] ?? null,
            'transaction_data' => $paymentData['transaction_data'] ?? null,
            'transaction_status' => $paymentData['transaction_status'] ?? null,
            'kod_transaksi' => 'backend update',
        ]);

        $order->save();

        return $paymentData;
    }
}
