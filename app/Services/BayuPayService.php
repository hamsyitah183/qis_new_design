<?php

namespace App\Services;

use App\Models\ConsignmentPermit;
use App\Models\InspectionItem;
use Illuminate\Support\Facades\Http;
use App\Models\Order;
use App\Models\IpConsignmentPermit;
use Illuminate\Support\Facades\Log;

class BayuPayService
{
    public function checkAndUpdatePayment(Order $order, string $kodTransaksi): array
    {

        try {
            $response = Http::withToken('test-api')
            ->withoutVerifying()
                ->get('https://bayupay-dummy.geovidia.my/readdata.php', ['kod_transaksi' => $kodTransaksi]);



            if (!$response->successful()) {
                throw new \Exception('Failed to retrieve payment data');
            }

            $paymentData = $response->json();
            $permits = $order->order_details['permits'] ?? [];

            // Get the correct application model
            $application = match ($order->application_type) {
                'Import Permit' => $order->ipApplication,
                'Inspection Certificate' => $order->inspectionApplication,
                'Consignment Certificate' => $order->consignmentApplication,
                default => null,
            };

            Log::info('Payment check', [
                'order_number' => $order->order_number,
                'transaction_status' => $paymentData['transaction_status'],
            ]);

            // Map transaction status to order & permit status
            $statusMap = [
                'SUCCESSFUL' => ['order' => 'payment success', 'permit' => 'paid', 'log' => 'Payment Successful'],
                'UNSUCCESSFUL' => ['order' => 'payment failed', 'permit' => 'payment failed', 'log' => 'Payment Unsuccessful'],
                'PENDING FOR AUTHORIZER TO APPROVE' => ['order' => 'pending authorization', 'permit' => 'payment processing', 'log' => 'Payment Pending Authorization'],
            ];

            $transactionStatus = $paymentData['transaction_status'] ?? null;

            if (!isset($statusMap[$transactionStatus])) {
                return $paymentData; // unknown status, skip
            }

            $config = $statusMap[$transactionStatus];

            // Update order status
            $order->status = $config['order'];
            $order->fill([
                'seller_ref' => $paymentData['seller_ref'] ?? null,
                'fpx_seller_reference' => $paymentData['fpx_seller_reference'] ?? null,
                'name' => $paymentData['name'] ?? null,
                'email' => $paymentData['email'] ?? null,
                'phone' => $paymentData['phone'] ?? null,
                'payment_amount' => $paymentData['payment_amount'] ?? null,
                'transaction_data' => $paymentData['transaction_data'] ?? null,
                'transaction_status' => $transactionStatus,
                'kod_transaksi' => $kodTransaksi,
            ]);
            $order->save();

            // Update permits
            foreach ($permits as $permit) {
                match ($order->application_type) {
                    'Import Permit' => IpConsignmentPermit::where('id', $permit['permit_id'])->update(['status' => $config['permit']]),
                    'Inspection Certificate' => InspectionItem::where('id', $permit['permit_id'])->update(['status' => $config['permit']]),
                    'Consignment Certificate' => ConsignmentPermit::where('id', $permit['permit_id'])->update(['status' => $config['permit']]),
                    default => null,
                };
            }

            // Optional: Update application status if all permits are paid
            if ($config['permit'] === 'paid' && $application) {
                $allPaid = match ($order->application_type) {
                    'Import Permit' => IpConsignmentPermit::where('application_id', $application->application_id)->where('status', '!=', 'paid')->doesntExist(),
                    'Inspection Certificate' => InspectionItem::where('application_id', $application->application_id)->where('status', '!=', 'paid')->doesntExist(),
                    'Consignment Certificate' => ConsignmentPermit::where('application_id', $application->application_id)->where('status', '!=', 'paid')->doesntExist(),
                    default => false,
                };

                if ($allPaid) {
                    $application->update(['status' => 'Completed']);
                    $application->logActivity(action: 'Application Completed', remark: 'All permits under this application have been fully paid', status: 'Completed');
                }
            }

            // Optional: Log activity
            $application?->logActivity(action: 'User Payment', remark: "Payment status updated to {$config['order']}", status: $config['log']);

            return $paymentData;
        } catch (\Exception $e) {
            Log::error('BayuPay connection error in checkAndUpdatePayment: ' . $e->getMessage(), [
                'order_number' => $order->order_number,
            ]);
            return [];
        }
    }

    public function checkAndUpdatePaymentWithoutTransactionCode(Order $order): array
    {
        // $response = Http::withToken('test-api')
        //  ->withoutVerifying() // Disable SSL verification for testing
        // ->get('https://bayupay-dummy.geovidia.my/readtransaction.php', [
        //     'sid' => $order->sid,
        //     'itn' => $order->itn,
        //     'rn' => $order->order_number,
        // ]);
        try {
            $response = Http::withToken('test-api')
                ->withoutVerifying()
                ->get('https://bayupay-dummy.geovidia.my/readtransaction.php', [
                    'sid' => $order->sid,
                    'itn' => $order->itn,
                    'rn' => $order->order_number,
                ]);

            Log::info('BayuPay request', [
                'url' => 'readtransaction',
                'params' => [
                    'sid' => $order->sid,
                    'itn' => $order->itn,
                    'rn' => $order->order_number,
                ],
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            /**
             * Handle transaction not found
             */
            if ($response->status() === 404) {
                Log::warning('BayuPay transaction not found. Marking as unsuccessful.', [
                    'order_number' => $order->order_number,
                ]);

                $this->markOrderUnsuccessful($order);

                return [];
            }

            if (!$response->successful()) {
                throw new \Exception('Failed to retrieve payment data');
            }
            // $response = Http::withToken('test-api')
            // ->get('https://hands-on5.sabah.gov.my/readtransaction.php', [
            //     'sid' => $order->sid,
            //     'itn' => $order->itn,
            //     'rn' => $order->order_number,
            // ]);

            if (!$response->successful()) {
                throw new \Exception('Failed to retrieve payment data');
            }

            $paymentData = $response->json();

            $permits = $order->order_details['permits'] ?? [];
            $application = $order->application;
            $applicationType = $application->application_type;

            Log::info('Payment check', [
                'order' => $order->order_number,
                'status' => $paymentData['transaction_status'],
                'application_type' => $applicationType,
            ]);

            /**
             * Decide permit model
             */
            $permitModel = match ($applicationType) {
                'Import Permit' => IpConsignmentPermit::class,
                'Inspection Certificate' => InspectionItem::class,
                'Consignment Certificate' => ConsignmentPermit::class,
                default => null,
            };

            if (!$permitModel) {
                throw new \Exception('Unsupported application type');
            }

            /**
             * Handle payment status
             */
            switch ($paymentData['transaction_status']) {
                case 'SUCCESSFUL':
                    $order->status = 'payment success';
                    $permitStatus = 'paid';

                    $application->logActivity(action: 'User Payment', remark: 'The order is successfully paid', status: 'User Payment');
                    break;

                case 'UNSUCCESSFUL':
                    $order->status = 'payment failed';
                    $permitStatus = 'payment failed';

                    $application->logActivity(action: 'User Payment', remark: 'The order payment failed', status: 'User Payment');
                    break;

                case 'PENDING FOR AUTHORIZER TO APPROVE':
                    $order->status = 'pending authorization';
                    $permitStatus = 'payment processing';

                    $application->logActivity(action: 'User Payment', remark: 'The order is pending for authorization', status: 'User Payment');
                    break;

                default:
                    return $paymentData;
            }

            /**
             * Update permits (ONCE)
             */
            foreach ($permits as $permit) {
                $permitModel::where('id', $permit['permit_id'])->update(['status' => $permitStatus]);
            }

            /**
             * Save payment metadata
             */
            $order->fill([
                'seller_ref' => $paymentData['seller_ref'] ?? null,
                'fpx_seller_reference' => $paymentData['fpx_seller_reference'] ?? null,
                'name' => $paymentData['name'] ?? null,
                'email' => $paymentData['email'] ?? null,
                'phone' => $paymentData['phone'] ?? null,
                'payment_amount' => $paymentData['payment_amount'] ?? null,
                'transaction_data' => $paymentData['transaction_data'] ?? null,
                'transaction_status' => $paymentData['transaction_status'] ?? null,
                'kod_transaksi' => ' ',
            ]);

            $order->save();

            return $paymentData;
        } catch (\Exception $e) {
            Log::error('BayuPay connection error in checkAndUpdatePaymentWithoutTransactionCode: ' . $e->getMessage(), [
                'order_number' => $order->order_number,
            ]);
            return [];
        }
    }

    private function markOrderUnsuccessful(Order $order): void
    {
        $permits = $order->order_details['permits'] ?? [];
        $application = $order->application;

        $permitModel = match ($application?->application_type) {
            'Import Permit' => IpConsignmentPermit::class,
            'Inspection Certificate' => InspectionItem::class,
            'Consignment Certificate' => ConsignmentPermit::class,
            default => null,
        };

        if (!$permitModel) {
            return;
        }

        /**
         * Reset order
         */
        $order->update([
            'status' => 'payment failed',
            'transaction_status' => 'UNSUCCESSFUL',
        ]);

        /**
         * Reset permits
         */
        foreach ($permits as $permit) {
            $permitModel::where('id', $permit['permit_id'])->update(['status' => 'pending for payment']);
        }

        /**
         * Log activity
         */
        $application?->logActivity(action: 'User Payment', remark: 'Transaction not found in BayuPay. Payment marked as unsuccessful.', status: 'Payment Failed');
    }
}
