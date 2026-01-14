<?php

namespace App\Http\Controllers;

use App\Models\IpApplication;
use App\Models\IpConsignmentPermit;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    //
    public function checkout($id, $permitId)
    {

        $permitIds = explode(',', $permitId);


        $permits = IpConsignmentPermit::where('application_id', $id)
            ->whereIn('id', $permitIds)
            ->where('status', 'pending for payment')
            ->get();


        if ($permits->isEmpty()) {
            abort(404, 'No permits found');
        }

        $application = IpApplication::with([
            'user',
            'importer',
            'exporter',
            'entryPoint',
            'consignmentPermits',
            'latestLog',
            'activity_log',
        ])->findOrFail($id);

        // dd($permits);
        return view('pages.public.cart', compact(['permits', 'application']));
    }
}
