<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Models\country;
use App\Models\PublicCode;
class InspectionController extends Controller
{
    //
    function getInspectionSelf()
    {
        $pubmeasure = PublicCode::where('cate_name', 'unit_measurement')->get();
        $pubpurpose = PublicCode::where('cate_name', 'consignment_purpose')->get();
        $country = Country::where('is_del', false)->get();
        return view('pages.public.inspection_self', compact('pubmeasure', 'pubpurpose', 'country'));
    }

    function getInspectionOthers()
    {
        $pubmeasure = PublicCode::where('cate_name', 'unit_measurement')->get();
        $pubpurpose = PublicCode::where('cate_name', 'consignment_purpose')->get();
        $country = Country::where('is_del', false)->get();
        return view('pages.public.inspection_others', compact('pubmeasure', 'pubpurpose', 'country'));
    }
}
