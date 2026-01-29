<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\IpApplication;
use App\Models\IpConsignmentAttachment;
use App\Models\IpConsignmentPermit;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class PublicController extends Controller
{
    public function show()
    {
        return view('pages.public.new_application');
    }

    public function showthis()
    {
        return view('pages.public.formw');
    }

    public function showallapplicationlist()
    {
        $application = IpApplication::with([
                'user',         // submitted by
                'importer',     // importer user
                'exporter',       // exporter record
                'entryPoint.districtCode'
            ])
            ->where('user_id', auth()->id())
            ->get();

        return view('pages.public.application_list', compact('application'));
    }

    public function verifyapplication()
    {
        $application = IpApplication::with([
                'user',         // submitted by
                'importer',     // importer user
                'exporter',       // exporter record
                'entryPoint.districtCode'
            ])
            ->where('importer_id', auth()->id())
            ->where('category_application', true)
            ->get();

        return view('pages.public.application_review_list', compact('application'));
    }

    public function viewapplication($uuid)
    {
        $application = IpApplication::with([
                'user',         // submitted by
                'importer',     // importer user
                'exporter',       // exporter record
                // 'exporter.country',
                'entryPoint.districtCode'
            ])
            ->where('application_id', $uuid)
            ->orderBy('created_at', 'desc')
            ->firstOrFail();
        
        $itemId = $application->id;
        
        $consignment = IpConsignmentPermit::with([
                    'unit',
                    'purposeCode'
                    ])
                    ->where('application_id', $itemId)
                    ->get();

        $allDetails = [];

        // foreach ($consignment as $consitem) {
        //     $details = json_decode($consitem->consignment_detail, true); // decode as ARRAY
        //     if (is_array($details)) {
        //         foreach ($details as $d) {
        //             $allDetails[] = $d;   // push one by one
        //         }
        //     }
        // }

        foreach ($consignment as $index => $consitem) {

            $details = json_decode($consitem->consignment_detail, true);

            // make sure details is an array and the index exists
            if (is_array($details) && isset($details[$index])) {
                $single = $details[$index];
                // include consignment DB id
                $single['consignment_id'] = $consitem->id;

                // include its attachments
                $single['attachments'] = $consitem->attachments;
                // $allDetails[] = $details[$index];
                $allDetails[] = $single;
            }
        }
        // dd($allDetails);
        return view('pages.public.view_application', [
                        'application'        => $application,
                        'consignment'        => $consignment,
                        'consignmentDetails' => $allDetails
                    ]); //, 'consignment', 'attachment'
    }

    public function modalspeItem($id){
        $cons = IpConsignmentPermit::with(['attachments'])
            ->findOrFail($id);
        
            return response()->json([
                'status' => 'success',
                'data'   => $cons
            ]);
    }

    public function getCountry(){
        $country = country::select('code as value', 'name')->get();

        return response()->json([
            'status' => 'success',
            'data'   => $country
        ]);
    }

    public function showcart()
    {
        return view('pages.public.cart');
    }

    public function showcheckout()
    {
        return view('pages.public.checkout');
    }
    
}
