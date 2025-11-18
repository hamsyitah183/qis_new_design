<?php

namespace App\Http\Controllers\internal;

use App\Http\Controllers\Controller;
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
}
