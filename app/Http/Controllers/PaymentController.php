<?php

namespace App\Http\Controllers;

use App\Models\IpApplication;
use App\Models\IpConsignmentPermit;
use App\Models\Order;
use App\Models\PaymentMethod;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;

class PaymentController extends Controller
{
    //
    public function checkout($id, $permitId, $total)
    {
        if (!session()->has('payment_active')) {
            abort(403, 'Payment session expired');
        }

        $application = IpApplication::findOrFail($id);
        $permitIds = explode(',', $permitId);

        $permits = IpConsignmentPermit::where('application_id', $id)->whereIn('id', $permitIds)->where('status', 'pending for payment')->get();

        // dd($permits);
        if ($permits->isEmpty()) {
            abort(404, 'No permits found');
        }

        $amount = 30;

        $jsonData = [
            'application' => [
                'id' => $application->id,
                'application_id' => $application->application_id,
                'status' => $application->status,
                'application_type' => $application->application_type
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
                        'permit_number' => $permit->permit_number,
                        'item_name' => $permit->consignment_detail['item_name'] ?? null,
                        'status' => $permit->status,
                        'amount' => number_format($amount, 2, '.', ''),
                    ];
                })
                ->values()
                ->toArray(),

            'total' => number_format($amount * $permits->count(), 2, '.', ''),
        ];

        // ✅ STORE IN SESSION HERE
        session(['application_details' => $jsonData]);


        $total = (float) $total;
        $paymentMethod = PaymentMethod::get();

        return view('pages.public.cart', compact('permits', 'application', 'total', 'paymentMethod'));
    }

    public function signedUrl(Request $request)
    {
        // dd($request->all());

        $request->validate([
            'application_id' => 'required',
            'permit_ids' => 'required|array|min:1',
        ]);

        $application = IpApplication::findOrFail($request->application_id);

        // 🔒 Ownership check
        if ($application->user_id !== authUser()['user']->uuid) {
            abort(403);
        }

        $permits = IpConsignmentPermit::where('application_id', $application->id)->whereIn('id', $request->permit_ids)->where('status', 'pending for payment')->get();

        if ($permits->count() !== count($request->permit_ids)) {
            abort(403, 'Invalid permit selection');
        }

        // if ($permits !== count($request->permit_ids)) {
        //     abort(403, 'Invalid permit selection');
        // }

        // order number

        $total = number_format($request['total'], 2, '.', ''); // ensures '50.00' instead of 50

        // dd( $application->id, $request->permit_ids, $total);

        session(['payment_active' => true]);

        $signedUrl = URL::signedRoute('payment.checkout', [
            'id' => $application->id,
            'permitId' => implode(',', $request->permit_ids),
            'total' => $total,
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
        if ($request['paymentMethod'] == 'bayuPay') {
            // dd($data);
            $data = $this->bayuPay($request, $applicationDetails);
            return view('bayuPayRedirect', compact('data'));
        } else {
            return 'no payment';
        }
    }

    private function bayuPay(Request $request, $applicationDetails)
    {
        $application = $applicationDetails['application'];
        $user = $applicationDetails['user'];
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

        // Build order number
        $orderNumber = 'ORD-' . $request->application_id . '-' . $runningNumber;

        $order = Order::create([
            'order_number' => $orderNumber,
            'status' => 'payment pending',
            'order_details' => $applicationDetails,
            'application_id' => $application['application_id'],
            'public_user_uuid' => $user['uuid'],
            'application_type' => $application['application_type'],
        ]);

        $data = [
            'sid' => 'SIDTEST',
            'itn' => 'IMPORT123',
            'rn' => $order->order_number,
            'amount' => $request->amount,
            'co_name' => $request->name,
            'email' => $request->email,
            'tel_no' => $request->no_phone,
            'application_id' => $request->application_id,
            'bounce' => url('/paymentUpdate' . '/' . $order->order_number),
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
        $kodTransaksi = $request->query('kod_transaksi');

        if (!$kodTransaksi) {
            abort(404, 'Kod Transaksi not found');
        }

        // Call BayuPay API with Bearer token
        $response = Http::withToken('test-api')->get('https://bayupay-dummy.geovidia.my/readdata.php', [
            'kod_transaksi' => $kodTransaksi,
        ]);

        if (!$response->successful()) {
            abort(500, 'Failed to retrieve payment data');
        }

        // Convert response to array
        $paymentData = $response->json();

        $order = Order::where('order_number', $rn)->first();

        $permits = $order->order_details['permits'];

        if ($paymentData['transaction_status'] == 'SUCCESSFUL') {
            $order->status = 'payment success';

            foreach ($permits as $permit) {
                $permitData = IpConsignmentPermit::where('id', $permit['permit_id'])->first();

                // dd($permitData);
                $permitData->status = 'paid';
                $permitData->save();
            }
        } elseif ($paymentData['transaction_status'] == 'UNSUCCESSFUL') {
            $order->status = 'payment failed';
        } elseif ($paymentData['transaction_status'] == 'PENDING FOR AUTHORIZER TO APPROVE') {
            $order->status = 'pending authorization';

            foreach ($permits as $permit) {
                $permitData = IpConsignmentPermit::where('id', $permit['permit_id'])->first();

                // dd($permitData);
                $permitData->status = 'payment processing';
                $permitData->save();
            }
        }

        $order->seller_ref = $paymentData['seller_ref'];
        $order->fpx_seller_reference = $paymentData['fpx_seller_reference'];
        $order->name = $paymentData['name'];
        $order->email = $paymentData['email'];
        $order->phone = $paymentData['phone'];
        $order->payment_amount = $paymentData['payment_amount'];
        $order->transaction_data = $paymentData['transaction_data'];
        $order->transaction_status = $paymentData['transaction_status'];
        $order->kod_transaksi = $kodTransaksi;
        $order->save();

        return view('pages.paymentStatus', compact('title', 'kodTransaksi', 'paymentData', 'order'));
    }

    public function cancelPayment(Request $request)
    {
        $request->validate([
            'permit_ids' => 'required|array',
            'order.order_number' => 'required|string',
        ]);

        $permitIds = $request->permit_ids;
        $orderNumber = $request->input('order.order_number');

        // Delete order (single record)
        Order::where('order_number', $orderNumber)->delete();

        // Revert permit statuses
        IpConsignmentPermit::whereIn('id', $permitIds)->update(['status' => 'pending for payment']);

        // Clear session flag
        session()->forget('payment_active');

        return response()->json([
            'status' => 'cancelled',
            'permits' => $permitIds,
        ]);
    }
}
