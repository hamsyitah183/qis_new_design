<?php

namespace App\Http\Controllers;

use App\Models\ConsignmentApplication;
use Illuminate\Http\Request;
use App\Models\PublicCode;
use App\Models\Country;
use Illuminate\Support\Facades\Auth;
use Str;

class ConsignmentController extends Controller
{
    //
    function getView()
    {
        $pubmeasure = PublicCode::where('cate_name', 'unit_measurement')->get();
        $pubpurpose = PublicCode::where('cate_name', 'consignment_purpose')->get();
        $country = Country::where('is_del', false)->get();
        return view('pages.public.consignmentapp', compact('pubmeasure', 'pubpurpose', 'country'));
    }


    function getViewOther()
    {
        $pubmeasure = PublicCode::where('cate_name', 'unit_measurement')->get();
        $pubpurpose = PublicCode::where('cate_name', 'consignment_purpose')->get();
        $country = Country::where('is_del', false)->get();
        return view('pages.public.consignmentappOther', compact('pubmeasure', 'pubpurpose', 'country'));
    }

function saveApplicationConsignment(Request $request)
    {
        $exporter = $request->exporterData
                ? json_decode($request->exporterData, true)
                : null;

            $importer = $request->importerData
                ? json_decode($request->importerData, true)
                : null;

            $permit = $request->permitDetails
                ? json_decode($request->permitDetails, true)
                : [];
 $application = ConsignmentApplication::create([
                    'application_id'       => Str::uuid(),
                    'eta'                  => $permit['eta'] ?? null,
                    'transport_type'       => $permit['tranType'] ?? null,
                    'entry_point'          => $permit['entrypoint'] ?? null,
                    'category_application' => $permit['applCate'] ?? null,
                    'user_id'              => Auth::user()->uuid,
                    'exporter_id'          => $exporter['id'] ?? null,
                    'importer_id'          => $importer['uuid'] ?? null,
                    'importer_detail'      => $importer,
                    'status'               => '',
                    'importer_verify'      => '',
                ]);
    }
}