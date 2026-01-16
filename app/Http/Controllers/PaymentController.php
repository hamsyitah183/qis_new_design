<?php

namespace App\Http\Controllers;

use App\Models\IpApplication;
use App\Models\IpConsignmentPermit;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class PaymentController extends Controller
{
    //
    public function checkout($id, $orderNo, $permitId, $total)
    {
        if (!session()->has('payment_active')) {
            abort(403, 'Payment session expired');
        }

        $permitIds = explode(',', $permitId);

        $permits = IpConsignmentPermit::where('application_id', $id)->whereIn('id', $permitIds)->where('status', 'pending for payment')->get();

        if ($permits->isEmpty()) {
            abort(404, 'No permits found');
        }

        $application = IpApplication::with(['user', 'importer', 'exporter', 'entryPoint', 'consignmentPermits', 'latestLog', 'activity_log'])->findOrFail($id);

        // Calculate total safely here
        $total = (float) $total;

        $order = Order::where('order_number', $orderNo)->first();

        return response()->view('pages.public.cart', compact('permits', 'application', 'total', 'order'))->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')->header('Pragma', 'no-cache');
    }

    public function signedUrl(Request $request)
    {
        // dd($request['total']);
        $request->validate([
            'application_id' => 'required|integer',
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

        $jsonData = [
            'application' => [
                'id' => $application->id,
                'application_id' => $application->application_id,
                'status' => $application->status,
            ],

            'user' => [
                'uuid' => $application->user->uuid,
                'fullname' => $application->user->fullname,
                'email' => $application->user->email,
                'phone_number' => $application->user->phone_number,
            ],

            'permits' => $permits
                ->map(function ($permit) {
                    return [
                        'permit_id' => $permit->id,
                        'permit_no' => $permit->permit_no,
                        'item_name' => $permit->item_name,
                        'status' => $permit->status,
                        'amount' => number_format($permit->amount, 2, '.', ''),
                    ];
                })
                ->values()
                ->toArray(),

            'total' => number_format($permits->sum('amount'), 2, '.', ''),
        ];

        $applicationId = $application->application_id;

        // Count existing orders for this application
        $lastOrder = Order::where('order_details->application->application_id', $applicationId)->latest('id')->first();

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
        $orderNumber = 'ORD-' . $applicationId . '-' . $runningNumber;

        $order = Order::create([
            'order_number' => $orderNumber,
            'status' => 'payment pending',
            'order_details' => $jsonData,
        ]);

        $total = number_format($request['total'], 2, '.', ''); // ensures '50.00' instead of 50

        session(['payment_active' => true]);

        $signedUrl = URL::signedRoute('payment.checkout', [
            'id' => $application->id,
            'orderNo' => $orderNumber,
            'permitId' => implode(',', $request->permit_ids),
            'total' => $total,
        ]);

        return response()->json([
            'url' => $signedUrl,
        ]);
    }

    public function payment(Request $request)
    {
        // dd($request->all());
        // if ($request['paymentMethod'] == 'bayuPay') {
        //     $this->bayuPay($request);
        // }
        // dd('sinika');
        if ($request['paymentMethod'] == 'bayuPay') {
            // dd($data);
            $data = $this->bayuPay($request);
            return view('bayuPayRedirect', compact('data'));
        } else {
            return 'no payment';
        }
    }

    private function bayuPay(Request $request)
    {
        $data = [
            'sid' => 'SIDTEST',
            'itn' => 'IMPORT123',
            'rn' => $request->orderNo,
            'amount' => $request->amount,
            'co_name' => $request->name,
            'email' => $request->email,
            'tel_no' => $request->no_phone,
            'bounce' => url('/paymentUpdate'),
        ];

        return $data;
    }

    function bounce($kod_transaksi)
    {
        return $kod_transaksi;
    }

    public function paymentUpdate(Request $request)
    {
        // Get the parameter
        $kodTransaksi = $request->query('kod_transaksi');

        if (!$kodTransaksi) {
            return 'Kod Transaksi not found!';
        }

        // Show it
        return 'Kod Transaksi: ' . $kodTransaksi;
    }
}
