<?php

namespace App\Http\Controllers;

use App\Models\ConsignmentApplication;
use App\Models\ConsignmentPermit;
use App\Models\InspectionApplication;
use App\Models\InspectionItem;
use App\Models\IpApplication;
use App\Models\IpConsignmentPermit;
use App\Models\Order;
use App\Models\PaymentMethod;

use App\Mail\PaymentFailedMail;
use App\Mail\PaymentReceiptMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Spatie\Activitylog\Models\Activity;
use App\Models\PublicUser;
use App\Notifications\ApplicationNotification;
use Carbon\Carbon;

class PaymentController extends Controller
{
    //
    public function checkout($id, $permitId, $total, $type)
    {
        if (!session()->has('payment_active')) {
            abort(403, 'Payment session expired');
        }

        if ($type == 'import_permit') {
            $application = IpApplication::findOrFail($id);
            $permitIds = explode(',', $permitId);
            $permits = IpConsignmentPermit::where('application_id', $id)
                ->whereIn('id', $permitIds)
                ->whereIn('status', ['pending for payment', 'payment failed', 'Payment Failed'])
                ->get();
        } elseif ($type == 'inspection') {
            $application = InspectionApplication::findOrFail($id);
            $permitIds = explode(',', $permitId);
            $permits = InspectionItem::where('application_id', $id)
                ->whereIn('id', $permitIds)
                ->whereIn('status', ['pending for payment', 'payment failed', 'Payment Failed'])
                ->get();
        } elseif ($type == 'consignment') {
            $application = ConsignmentApplication::with(['consignmentPermits'])->findOrFail($id);
            $permitIds = explode(',', $permitId);
            $permits = ConsignmentPermit::where('application_id', $id)
                ->whereIn('id', $permitIds)
                ->whereIn('status', ['pending for payment', 'payment failed', 'Payment Failed'])
                ->get();
        }

        if ($permits->isEmpty()) {
            abort(404, 'No permits found');
        }

        $amount = 30;

        $jsonData = [
            'application' => [
                'id' => $application->id,
                'application_id' => $application->application_id,
                'status' => $application->status,
                'application_type' => $application->application_type,
                'prices_total' => $application->prices_total
            ],
            'user' => [
                'uuid' => $application->user->uuid,
                'fullname' => $application->user->fullname,
                'email' => $application->user->email,
                'phone_number' => $application->user->phone_number,
            ],
            'permits' => $permits
                ->map(function ($permit) use ($amount) {
                    return [
                        'permit_id' => $permit->id,
                        'permit_number' => $permit->permit_number ?? '-',
                        'item_name' => $permit->consignment_detail['item_name'] ?? null,
                        'status' => $permit->status,
                        'amount' => number_format($amount, 2, '.', ''),
                    ];
                })
                ->values()
                ->toArray(),
            'total' => number_format($amount * $permits->count(), 2, '.', ''),
        ];

        session(['application_details' => $jsonData]);

        $total = (float) $total;
        $paymentMethod = PaymentMethod::get();

        // ─── Generate order number preview (without creating order) ───
        $lastOrder = Order::where('application_id', $application->application_id)->latest('id')->first();
        $runningNumber = 1;
        if ($lastOrder) {
            $parts = explode('-', $lastOrder->order_number);
            $lastRunning = (int) end($parts);
            $runningNumber = $lastRunning + 1;
        }
        $runningNumber = str_pad($runningNumber, 3, '0', STR_PAD_LEFT);
        $orderNumberPreview = 'QIS-' . $application->application_id . '-' . $runningNumber;

     
        return view('pages.public.cart', compact(
            'permits',
            'application',
            'total',
            'paymentMethod',
            'orderNumberPreview'  // <-- pass to view
        ));
    }

    public function signedUrl(Request $request)
    {
        //    dd($request->all());
        $type = $request['type'];
        $id = $request['application_id'];
        $permitIds = $request['permit_ids'];

        $request->validate([
            'application_id' => 'required',
            'permit_ids' => 'required|array|min:1',
        ]);

        if ($type == 'import_permit') {

            $application = IpApplication::where(
                'application_id',
                $id
            )->firstOrFail();

            $permits = IpConsignmentPermit::where(
                'application_id',
                $application->id
            )
                ->whereIn('id', $permitIds)
                ->whereIn('status', [
                    'pending for payment',
                    'payment failed',
                    'Payment Failed'
                ])
                ->get();
        } elseif ($type == 'inspection') {
            $application = InspectionApplication::findOrFail($id);
            // $permitIds = explode(',', $permitId);

            $permits = InspectionItem::where('application_id', $id)
                ->whereIn('id', $permitIds)
                ->whereIn('status', ['pending for payment', 'payment failed', 'Payment Failed'])
                ->get();
            // dd($permits);
        } elseif ($type == 'consignment') {
            $application = ConsignmentApplication::with(['consignmentPermits'])->where('application_id', $id)->first();
            $permits = $application->consignmentPermits;
        }

        if ($application->user_id !== authUser()['user']->uuid) {
            abort(403);
        }
        // $permits = IpConsignmentPermit::where('application_id', $application->id)
        //     ->whereIn('id', $request->permit_ids)
        //     ->whereIn('status', ['pending for payment', 'payment failed'])
        //     ->get();

        if ($permits->count() !== count($request->permit_ids)) {
            abort(403, 'Invalid permit selection');
        }

        // if ($permits !== count($request->permit_ids)) {
        //     abort(403, 'Invalid permit selection');
        // }

        // order number

        $total = number_format($request['total'], 2, '.', '');

        // dd( $application->id, $request->permit_ids, $total);

        session(['payment_active' => true]);

        $signedUrl = URL::signedRoute('payment.checkout', [
            'id' => $application->id,
            'permitId' => implode(',', $request->permit_ids),
            'total' => $total,
            'type' => $type,
            // 'details' => $jsonData
        ]);

        $application->logActivity(action: 'User Payment', remark: 'Application is ready to be paid', status: 'User Payment');

        return response()->json([
            'url' => $signedUrl,
        ]);
    }

    public function payment(Request $request)
    {
        $applicationDetails = session('application_details');

        if (!$applicationDetails) {
            abort(403, 'Application details expired');
        }

        if ($request['paymentMethod'] === 'bayuPay') {
            $data = $this->bayuPay($request, $applicationDetails);

            // 🔥 LOG EXACT PAYLOAD
            Log::info('BayuPay POST payload', $data);

            return view('bayuPayRedirect', compact('data'));
        }

        return 'no payment';
    }

    private function bayuPay(Request $request, $applicationDetails)
    {
        $application = $applicationDetails['application'];
        $user = $applicationDetails['user'];
        $permitsArray = $applicationDetails['permits'];
        $permitIds = collect($permitsArray)->pluck('permit_id')->toArray();

        // dd($application);
        if ($application['application_type'] == 'Import Permit') {
            $permits = IpConsignmentPermit::whereIn('id', $permitIds)->get();
        } elseif ($application['application_type'] == 'Inspection') {
            $permits = InspectionItem::whereIn('id', $permitIds)->get();
        } elseif ($application['application_type'] == 'Consignment Certificate') {
            $permits = ConsignmentPermit::whereIn('id', $permitIds)->get();
        } else {
            $permits = [];
        }
        // dd($permits);
        foreach ($permits as $permit) {
            $permit->status = 'payment processing';
            $permit->save();
        }
        // dd($request->all(), $applicationDetails, $application, $user);

        $lastOrder = Order::where('order_details->application->application_id', $request->application_id)->latest('id')->first();

        $runningNumber = 1;

        if ($lastOrder) {
            // Extract last running number from order_number
            $parts = explode('-', $lastOrder->order_number);
            $lastRunning = (int) end($parts);
            $runningNumber = $lastRunning + 1;
        }

        // Pad running number to 3 digits
        $runningNumber = str_pad($runningNumber, 3, '0', STR_PAD_LEFT);

        // dd($application['application_type']);
        if ($application['application_type'] == 'Import Permit') {
            $itn = 'IT037962';
            // $itn = 'ITN10001';
            $application_name = $request->application_id;
        } elseif ($application['application_type'] == 'Inspection Certificate') {
            $itn = 'IT549383';
            // $itn = 'ITN10002';
            $application_name = $request->application_id;
        } elseif ($application['application_type'] == 'Consignment Certificate') {
            // $itn = 'IT037963';
            $itn = 'IT331659';
            // $itn = 'ITN10003';
            $application_name = $request->application_id;
        } else {
            $itn = 'ITN';
        }


        // Build order number
        $orderNumber = 'QIS-' . $application_name . '-' . $runningNumber;


        // $sid = 'QIS123';


        $sid = 'SE13001C';



        $order = Order::create([
            'order_number' => $orderNumber,
            'status' => 'payment pending',
            'order_details' => $applicationDetails,
            'application_id' => $application['application_id'],
            'public_user_uuid' => $user['uuid'],
            'application_type' => $application['application_type'],
            'transaction_status' => 'PAYMENT PROCESSING',
            'itn' => $itn,
            'sid' => $sid,
            'payment_type' => 'bayupay'
        ]);
        // dd($order);
        $data = [
            'sid' => $sid,
            'itn' => $itn,
            'rn' => $order->order_number,
            'amount' => number_format((float) $request->amount, 2, '.', ''),
            'co_name' => $request->name,
            'email' => $request->email,
            'tel_no' => $request->no_phone,
            'co_no' => $request->no_phone,

            // 'application_id' => $request->application_id,
            // 'bounce' => '/paymentUpdate' . '/' . $order->order_number,
            'bounce' => route('payment.update', ['rn' => $order->order_number]),
        ];

        return $data;
    }

    function bounce($kod_transaksi)
    {
        return $kod_transaksi;
    }

    public function paymentUpdate($rn, Request $request)
    {
        $title = 'Payment Status';
        $kodTransaksi = $request->query('ref');

        if (!$kodTransaksi) {
            abort(404, 'Kod Transaksi not found');
        }

        // Call BayuPay API
        $response = Http::withToken('test-api')
            ->withoutVerifying() // Disable SSL verification for testing
            ->get('https://bayupay-dummy.geovidia.my/readdata.php', ['kod_transaksi' => $kodTransaksi]);
        // $response = Http::withToken('test-api')->get('https://hands-on5.sabah.gov.my/readdata.php', ['kod_transaksi' => $kodTransaksi]);


        // $response = Http::withToken('test-api')->get('https://hands-on11.sabah.gov.my/readdata.php', ['kod_transaksi' => $kodTransaksi]);

        if (!$response->successful()) {
            // abort(500, 'Failed to retrieve payment data');
            throw new \Exception(
                "BayuPay error ({$response->status()}): " . $response->body()
            );
        }

        $paymentData = $response->json();

        $order = Order::where('order_number', $rn)->firstOrFail();

        // Idempotency check: If order is already processed, skip updates and notifications
        $isProcessed = in_array($order->status, ['payment complete', 'payment partial', 'payment failed']);
        if ($isProcessed) {
            return view('pages.paymentStatus', compact('title', 'kodTransaksi', 'paymentData', 'order'));
        }

        // Load the correct application based on application_type
        $application = match ($order->application_type) {
            'Import Permit' => IpApplication::where('application_id', $order->application_id)->firstOrFail(),
            'Inspection Certificate' => InspectionApplication::where('application_id', $order->application_id)->firstOrFail(),
            'Consignment Certificate' => ConsignmentApplication::where('application_id', $order->application_id)->firstOrFail(),
            default => null,
        };

        if (!$application) {
            abort(404, 'Application not found');
        }

        $permits = $order->order_details['permits'] ?? [];

        DB::transaction(function () use ($paymentData, $order, $application, $permits, $kodTransaksi) {
            $transactionStatus = strtolower($paymentData['transaction_status']);

            $statusMap = [
                'successful' => [
                    'permit_status' => 'paid',
                    'remark' => 'The order is successfully paid',
                    'log_status' => 'Payment Successful',
                ],
                'unsuccessful' => [
                    'permit_status' => 'payment failed',
                    'remark' => 'The order is unsuccessfully paid',
                    'log_status' => 'Payment Unsuccessful',
                ],
                'pending for authorizer to approve' => [
                    'permit_status' => 'payment processing',
                    'remark' => 'The order is pending for authorization',
                    'log_status' => 'Payment Pending Authorization',
                ],
            ];

            // Skip if unknown status
            if (!isset($statusMap[$transactionStatus])) {
                return;
            }

            $config = $statusMap[$transactionStatus];

            // Update permits first
            foreach ($permits as $permit) {
                match ($application['application_type']) {
                    'Import Permit' => IpConsignmentPermit::where('id', $permit['permit_id'])
                        ->update([
                            'status' => $config['permit_status'],
                            'validity_date' => Carbon::now()->addMonth(), // ✅ add 1 month
                        ]),

                    'Inspection Certificate' => InspectionItem::where('id', $permit['permit_id'])
                        ->update([
                            'status' => $config['permit_status'],
                            'validity_date' => Carbon::now()->addMonth(),
                        ]),

                    'Consignment Certificate' => ConsignmentPermit::where('id', $permit['permit_id'])
                        ->update([
                            'status' => $config['permit_status'],
                            'validity_date' => Carbon::now()->addMonth(),
                        ]),

                    default => null,
                };
            }

            // Check if all permits are paid
            $allPaid = match ($application['application_type']) {
                'Import Permit' => (int) IpConsignmentPermit::where('application_id', $application->id)->where('status', '!=', 'paid')->doesntExist(),

                'Inspection Certificate' => (int) InspectionItem::where('application_id', $application->id)->where('status', '!=', 'paid')->doesntExist(),

                'Consignment Certificate' => (int) ConsignmentPermit::where('application_id', $application->id)->where('status', '!=', 'paid')->doesntExist(),

                default => 0,
            };

            $notificationController = new NotificationController();

            // Update order status
            if ($transactionStatus === 'successful') {
                $order->status = $allPaid ? 'payment complete' : 'payment partial';

                $notificationController->sendStatusMessage(
                    $application->importer_detail['fullname'] ?? 'User',
                    $application['application_type'],
                    $application->application_id,
                    'paid',
                    'Your application is successfully paid.',
                    $application->importer->phone_number ?? '60143290092' // recipient number
                );
            } else {
                $order->status = $transactionStatus === 'unsuccessful' ? 'payment failed' : 'pending authorization';

                $notificationController->sendStatusMessage(
                    $application->importer_detail['fullname'] ?? 'User',
                    $application['application_type'],
                    $application->application_id,
                    $transactionStatus === 'unsuccessful' ? 'failed' : 'pending authorization',
                    'Your application payment is ' . $transactionStatus . '.',

                    $application->importer->phone_number ?? '60143290092' // recipient number
                );
            }

            $order->save();

            // Update application if all permits paid
            if ($allPaid) {
                $application->update(['status' => 'Completed']);
                $application->logActivity(action: 'Application Completed', remark: 'All permits under this application have been fully paid', status: 'Completed');
            } else {
                $application->logActivity(action: 'User Payment', remark: $config['remark'], status: $config['log_status']);
            }

            // Update order payment fields
            $order->update([
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
        });

        // Send payment receipt email on successful payment (outside transaction)
        if (strtolower($paymentData['transaction_status']) === 'successful') {
            try {
                // Refresh order to get latest data
                $order->refresh();

                // Collect permit numbers
                $permitNumbers = [];
                foreach ($permits as $permit) {
                    $permitNumbers[] = $permit['permit_number'] ?? 'N/A';
                }

                // Use user's email from order details, not payment gateway email
                $userEmail = $order->order_details['user']['email'] ?? null;

                // Send email to the actual user
                if (!empty($userEmail)) {
                    Mail::to($userEmail)->send(
                        new PaymentReceiptMail($order, $paymentData, $permitNumbers)
                    );

                    // Activity log for successful payment
                    $user = $order->order_details['user'] ?? null;
                    if ($user) {
                        activity()
                            ->tap(function (Activity $activity) {
                                $activity->log_name = 'user_activity';
                            })
                            ->event('payment successful')
                            ->performedOn($order)
                            ->withProperties([
                                'order_number' => $order->order_number,
                                'application_id' => $order->application_id,
                                'amount' => $paymentData['payment_amount'] ?? 0,
                                'permit_numbers' => $permitNumbers,
                            ])
                            ->log(($user['fullname'] ?? 'User') . ' has successfully completed payment for order ' . $order->order_number);

                        // Send notification to public user
                        $publicUser = PublicUser::where('uuid', $user['uuid'] ?? null)->first();
                        if ($publicUser) {
                            try {
                                $notificationUrl = url('/order/list');
                                Notification::send($publicUser, new ApplicationNotification(
                                    'Payment successful! Your order ' . $order->order_number . ' has been completed. Amount: RM' . number_format($paymentData['payment_amount'] ?? 0, 2),
                                    'QIS Payment',
                                    $notificationUrl
                                ));
                            } catch (\Exception $e) {
                                Log::warning('Failed to send payment success notification: ' . $e->getMessage());
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                // Log the error but don't break the payment flow
                Log::error('Failed to send payment receipt email: ' . $e->getMessage(), [
                    'order_number' => $order->order_number,
                    'user_email' => $userEmail ?? 'N/A',
                    'payment_email' => $paymentData['email'] ?? 'N/A',
                ]);
            }
        }

        // Send payment failed email on unsuccessful payment (outside transaction)
        if (strtolower($paymentData['transaction_status']) === 'unsuccessful') {
            try {
                // Refresh order to get latest data
                $order->refresh();

                // Collect permit numbers
                $permitNumbers = [];
                foreach ($permits as $permit) {
                    $permitNumbers[] = $permit['permit_number'] ?? 'N/A';
                }

                // Use user's email from order details, not payment gateway email
                $userEmail = $order->order_details['user']['email'] ?? null;

                // Send email to the actual user
                if (!empty($userEmail)) {
                    Mail::to($userEmail)->send(
                        new PaymentFailedMail($order, $paymentData, $permitNumbers)
                    );

                    // Activity log for failed payment
                    $user = $order->order_details['user'] ?? null;
                    if ($user) {
                        activity()
                            ->tap(function (Activity $activity) {
                                $activity->log_name = 'user_activity';
                            })
                            ->event('payment failed')
                            ->performedOn($order)
                            ->withProperties([
                                'order_number' => $order->order_number,
                                'application_id' => $order->application_id,
                                'amount' => $paymentData['payment_amount'] ?? 0,
                                'permit_numbers' => $permitNumbers,
                                'reason' => $paymentData['transaction_data'] ?? 'Payment unsuccessful',
                            ])
                            ->log(($user['fullname'] ?? 'User') . ' payment failed for order ' . $order->order_number);

                        // Send notification to public user
                        $publicUser = PublicUser::where('uuid', $user['uuid'] ?? null)->first();
                        if ($publicUser) {
                            try {
                                $notificationUrl = url('/order/list');
                                Notification::send($publicUser, new ApplicationNotification(
                                    'Payment failed for order ' . $order->order_number . '. Please try again or contact support.',
                                    'QIS Payment',
                                    $notificationUrl
                                ));
                            } catch (\Exception $e) {
                                Log::warning('Failed to send payment failure notification: ' . $e->getMessage());
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                // Log the error but don't break the payment flow
                Log::error('Failed to send payment failed email: ' . $e->getMessage(), [
                    'order_number' => $order->order_number,
                    'user_email' => $userEmail ?? 'N/A',
                    'payment_email' => $paymentData['email'] ?? 'N/A',
                ]);
            }
        }

        return view('pages.paymentStatus', compact('title', 'kodTransaksi', 'paymentData', 'order'));
    }

    public function cancelPayment(Request $request)
    {
        $request->validate([
            'order.order_number' => 'required|string',
        ]);

        $orderNumber = $request->input('order.order_number');

        // Retrieve order to determine application type and permit IDs
        $order = Order::where('order_number', $orderNumber)->first();

        if ($order) {
            // Extract permit IDs (or item IDs) from the order details
            // order_details is cast to array, structure: ['permits' => [['permit_id' => ...], ...], ...]
            $permits = $order->order_details['permits'] ?? [];
            $permitIds = collect($permits)->pluck('permit_id')->toArray();

            if (!empty($permitIds)) {
                // Revert permit statuses based on application type
                match ($order->application_type) {
                    'Import Permit' => IpConsignmentPermit::whereIn('id', $permitIds)->update(['status' => 'pending for payment']),
                    'Inspection', 'Inspection Certificate' => InspectionItem::whereIn('id', $permitIds)->update(['status' => 'pending for payment']),
                    'Consignment Certificate' => ConsignmentPermit::whereIn('id', $permitIds)->update(['status' => 'pending for payment']),
                    default => null,
                };
            }

            // Delete order
            $order->delete();
        }

        // Clear session flag
        session()->forget('payment_active');

        return response()->json([
            'status' => 'cancelled',
        ]);
    }
}
