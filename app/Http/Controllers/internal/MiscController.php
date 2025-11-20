<?php

namespace App\Http\Controllers\internal;

use App\Http\Controllers\Controller;
use App\Models\IpCondition;
use App\Models\PublicCode;
use Illuminate\Http\Request;

class MiscController extends Controller
{
    
    public function showcontrolpanel()
    {
        return view('pages.internal.misc.control_panel');
    }

    public function getpbdata($cate)
    {
        $pbdata = PublicCode::select('id', 'cate_name', 'cate_code', 'description')
        ->where('cate_name', $cate)
        ->where('is_del', false)
        ->get();

        return response()->json([
            'status' => 'success',
            'data'   => $pbdata
        ]);
    }

    public function getspecificpbdata($id)
    {
        $pbdata = PublicCode::select('id', 'cate_name', 'cate_code', 'description')
        ->findorFail($id);

        return response()->json([
            'status' => 'success',
            'data'   => $pbdata
        ]);
    }

    public function updatepbdata(Request $request)
    {
        $id = $request->input('id');
        $code = $request->input('item_code');
        $desc = $request->input('item_desc');

        $pbdata = PublicCode::findorFail($id);
        $pbdata->cate_code = $code;
        $pbdata->description = $desc;
        $pbdata->save();

        return response()->json([
            'status' => 'success',
            'message'   => 'Public code updated successfully.'
        ]);
    }

    public function deletepbdata($id)
    {
        $pbdata = PublicCode::findorFail($id);
        $pbdata->is_del = true;
        $pbdata->save();

        return response()->json([
            'status' => 'success',
            'message'   => 'Public code deleted successfully.'
        ]);
    }

    public function addpbdata(Request $request)
    {
        $cate = $request->input('category');
        $code = $request->input('item_code');
        $desc = $request->input('item_desc');
        // dd($cate,$code,$desc);

        if($code == null || $code == '' ){
            $getcode = PublicCode::where('cate_name', $cate)->max('cate_code');
            $code = $getcode + 1;
        }

        $pbdata = new PublicCode();
        $pbdata->cate_name = $cate;
        $pbdata->cate_code = $code;
        $pbdata->description = $desc;
        $pbdata->is_del = false;
        $pbdata->save();

        return response()->json([
            'status' => 'success',
            'message'   => 'Public code added successfully.'
        ]);
    }

    public function showpermitcondition()
    {
        return view('pages.internal.misc.permit_list');
    }

    public function permitaddcondition()
    {
        $pbdata = PublicCode::select('id', 'cate_name', 'cate_code', 'description')
        ->where('cate_name', 'consignment_purpose')
        ->where('is_del', false)
        ->get();

        return view('pages.internal.misc.permit_add', compact('pbdata'));
    }

    public function saveCondition(Request $request)
    {
        // Validate
        $request->validate([
            'itemName'   => 'required|string',
            'itemCategory' => 'required|integer',
            'permit_condition' => 'required|string',
        ]);

        // Decode Tagify arrays
        $countryArr = json_decode($request->countryTag, true) ?? [];
        $usageArr   = json_decode($request->usageTags, true) ?? [];

        // Convert Tagify structure [{"value":"XYZ"}] → ["XYZ"]
        $countryValues = array_map(fn($i) => $i['value'] ?? $i['name'] ?? null, $countryArr);
        $usageValues   = array_map(fn($i) => $i['value'] ?? $i['name'] ?? null, $usageArr);

        // Save record
        $save = IpCondition::create([
            'category'          => $request->itemCategory,
            'item_name'         => $request->itemName,
            'addional_condition'=> $request->permit_condition,
            'quantity_limit'    => $request->quanLimit ?: null,
            'date_limit'        => $request->spedate ?: null,
            'country'           => json_encode($countryValues),
            'usage'             => json_encode($usageValues),
        ]);

        return response()->json([
            'status' => 'success',
            'data'   => $save
        ]);
    }

    public function editCondition($id)
    {
        $condition = IpCondition::with(['code'])->findOrFail($id);

        $pbdata = PublicCode::select('id', 'cate_name', 'cate_code', 'description')
        ->where('cate_name', 'consignment_purpose')
        ->where('is_del', false)
        ->get();

        return view('pages.internal.misc.permit_edit', compact('condition', 'pbdata'));
    }
}
