<?php

namespace App\Http\Controllers;

use App\Models\IpApplication;
use App\Models\IpConsignmentPermit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

class PaymentController extends Controller
{
    //
    public function checkout($id, $permitId, $total)
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

        return response()->view('pages.public.cart', compact('permits', 'application', 'total'))->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')->header('Pragma', 'no-cache');
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

        // 🔒 Validate permits belong to this application & are payable
        $permits = IpConsignmentPermit::where('application_id', $application->id)->whereIn('id', $request->permit_ids)->where('status', 'pending for payment')->count();

        if ($permits !== count($request->permit_ids)) {
            abort(403, 'Invalid permit selection');
        }

        $total = number_format($request['total'], 2, '.', ''); // ensures '50.00' instead of 50

        session(['payment_active' => true]);

        $signedUrl = URL::signedRoute('payment.checkout', [
            'id' => $application->id,
            'permitId' => implode(',', $request->permit_ids),
            'total' => $total,
        ]);

        return response()->json([
            'url' => $signedUrl,
        ]);
    }
}
