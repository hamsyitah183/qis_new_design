<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PublicCode;
use App\Models\Country;

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
}
