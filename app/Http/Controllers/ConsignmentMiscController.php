<?php

namespace App\Http\Controllers;

use App\Models\ConsignmentCondition;
use App\Models\PublicCode;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ConsignmentMiscController extends Controller
{
    //

    public function showConsignmentCondition()
    {
        if (auth()->user()->hasRole('boundary officer')) {
            abort(403, 'Unauthorized action. Boundary Officers are restricted from this area.');
        }

        return view('pages.internal.misc.consignment_condition_list');
    }

    public function getConsignmentConditionData()
    {
        if (auth()->user()->hasRole('boundary officer')) {
            abort(403, 'Unauthorized action. Boundary Officers are restricted from this area.');
        }

        $query = ConsignmentCondition::with(['condcategory'])->select('id', 'item_name', 'category', 'usage', 'country');

        return DataTables::of($query)->make(true);
    }

    public function getConsignmentConditionDataById($id)
    {
        if (auth()->user()->hasRole('boundary officer')) {
            abort(403, 'Unauthorized action. Boundary Officers are restricted from this area.');
        }

        $condition = ConsignmentCondition::with(['code', 'condcategory'])->find($id);

        if (!$condition) {
            return response()->json(['error' => 'Consignment Condition not found'], 404);
        }

        return response()->json([
            'data' => $condition,
        ]);
    }

    public function editConsignmentConditionDataById($id)
    {
        if (auth()->user()->hasRole('boundary officer')) {
            abort(403, 'Unauthorized action. Boundary Officers are restricted from this area.');
        }

        $condition = ConsignmentCondition::find($id);

        // dd($condition);
        $pbdata = PublicCode::select('id', 'cate_name', 'cate_code', 'description')->where('cate_name', 'consignment_purpose')->where('is_del', false)->get();
        $measurements = PublicCode::select('id', 'cate_name', 'cate_code', 'description')->where('cate_name', 'unit_measurement')->where('is_del', false)->get();

        if (!$condition) {
            return response()->json(['error' => 'Consignment Condition not found'], 404);
        }

        return view('pages.internal.misc.consignment_condition_edit', compact('condition', 'pbdata', 'measurements'));
    }

    public function saveCondition(Request $request)
    {
        if (auth()->user()->hasRole('boundary officer')) {
            abort(403, 'Unauthorized action. Boundary Officers are restricted from this area.');
        }

        $data = $request->all();
        // dd($data);

        $condition = ConsignmentCondition::find($data['id']);

        

        $countryArr = json_decode($request->country, true) ?? [];
        $usageArr = json_decode($request->usage, true) ?? [];

        $countryValues = array_map(fn($i) => $i['value'] ?? ($i['name'] ?? null), $countryArr);
        $usageValues = array_map(fn($i) => $i['value'] ?? ($i['name'] ?? null), $usageArr);


        if( $condition) {
            $condition = ConsignmentCondition::find($request->id);
        } else {
            $condition = new ConsignmentCondition();
        }

        $condition->item_name = $data['item_name'];
        $condition->addional_condition = $data['addional_condition'];
        $condition->quantity_limit = $data['quantity_limit'];
        // $condition->date_limit = $data['date_limit'];
        $condition->country = $countryValues;
        $condition->usage = $usageValues;
        $condition->category = $data['category'];
        $condition->start_date = $request['start_date'];
        $condition->end_date = $request['end_date'];
        $condition->measurement_unit =  $request->quanmunit;

        $condition->save();

        return response()->json(['success' => 'Consignment Condition updated successfully']);
    }

    public function addConsignmentConditionData()
    {
        if (auth()->user()->hasRole('boundary officer')) {
            abort(403, 'Unauthorized action. Boundary Officers are restricted from this area.');
        }

        $pbdata = PublicCode::select('id', 'cate_name', 'cate_code', 'description')->where('cate_name', 'consignment_purpose')->where('is_del', false)->get();

        return view('pages.internal.misc.consignment_condition_add', compact('pbdata'));
    }

    public function getDistinctUsage()
    {
        $rows = ConsignmentCondition::whereNotNull('usage')->pluck('usage');

        $usages = collect();
        foreach ($rows as $row) {
            $values = is_array($row) ? $row : json_decode($row, true);
            if (is_array($values)) {
                $usages = $usages->merge($values);
            }
        }

        $distinct = $usages->map(fn($v) => trim($v))
            ->filter()
            ->unique()
            ->values();

        return response()->json(['data' => $distinct]);
    }
}

