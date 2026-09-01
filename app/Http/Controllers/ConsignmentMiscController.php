<?php

namespace App\Http\Controllers;

use App\Models\ConsignmentCondition;
use App\Models\ConsignmentPermit;
use App\Models\PublicCode;
use Illuminate\Http\Request;
use App\Models\Country;
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

    public function getConsignmentConditionDataByCategory($category, $country)
    {
        $query = ConsignmentCondition::where('category', $category)
            ->whereJsonContains('country', $country)
            ->get();

        return DataTables::of($query)->make(true);
    }
    public function getConsignmentConditionData()
    {

        $query = ConsignmentCondition::with(['condcategory'])->select('id', 'scientific_name', 'item_name', 'category', 'usage', 'country');

        return DataTables::of($query)->make(true);
    }

    public function getConsignmentConditionDataById($id)
    {

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
        $pbdata = PublicCode::select('id', 'cate_name', 'cate_code', 'description')->where('cate_name', 'consignment_category')->where('is_del', false)->get();
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


        if ($condition) {
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
        $condition->scientific_name =  $data['scientific_name'];

        $condition->save();

        return response()->json(['success' => 'Consignment Condition updated successfully', 'id' => $condition->id]);
    }

    public function addConsignmentConditionData()
    {
        if (auth()->user()->hasRole('boundary officer')) {
            abort(403, 'Unauthorized action. Boundary Officers are restricted from this area.');
        }

        $pbdata = PublicCode::select('id', 'cate_name', 'cate_code', 'description')->where('cate_name', 'consignment_category')->where('is_del', false)->get();


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

    public function deleteCondition($id)
    {
        if (auth()->user()->hasRole('boundary officer')) {
            abort(403, 'Unauthorized action. Boundary Officers are restricted from this area.');
        }

        $condition = ConsignmentCondition::findOrFail($id);
        $condition->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Consignment condition deleted successfully.',
        ]);
    }

    public function search(Request $request, $country)
    {
        // dd($country);
        $term = $request->input('q', '');

        $countryRecord = Country::where('name', $country)->first();

        if (!$countryRecord) {
            return response()->json(['results' => []]);
        }

        $items = ConsignmentCondition::whereJsonContains('country', $countryRecord->code)
            ->when($term, function ($query) use ($term) {
                $query->where(function ($q) use ($term) {
                    $q->where('item_name', 'like', "%{$term}%")
                        ->orWhere('scientific_name', 'like', "%{$term}%");
                });
            })
            ->orderBy('item_name')
            ->limit(20)
            ->get(['id', 'item_name', 'scientific_name']);

        return response()->json([
            'results' => $items->map(fn($item) => [
                'id'   => $item->id,
                'text' => "{$item->item_name} ({$item->scientific_name})",
            ]),
        ]);
    }

    public function quickAdd(Request $request)
    {
        $request->validate([
            'item_name'     => 'required|string|max:255',
            'permit_id'     => 'nullable|integer|exists:ip_consignment_permit,id',
            'countrySelect' => 'nullable|array',       // optional; countries sent as array
            'countrySelect.*' => 'string|max:10',     // each country code
        ]);

        // 1. Retrieve the permit
        $permit = ConsignmentPermit::find($request->permit_id);
        if (!$permit) {
            return response()->json(['message' => 'Permit not found.'], 404);
        }

        // 2. Update consignment_detail
        $consignment_detail = $permit->consignment_detail ?? [];
        $consignment_detail['item_name'] = $request->item_name;
        $consignment_detail['isCustom'] = false;
        $permit->consignment_detail = $consignment_detail;
        $permit->save();

        // 3. Prepare country array – handle both JSON string and direct array
        $countryValues = [];
        if ($request->filled('countrySelect')) {
            $countryValues = $request->countrySelect; // already an array
        } elseif ($request->filled('country')) {
            // fallback if frontend sends a JSON string under 'country'
            $countryValues = json_decode($request->country, true) ?? [];
        }

        // 4. Create the new condition
        $condition = ConsignmentCondition::create([
            'item_name'          => $request->item_name,
            'item_bahasa'        => $request->scientific_name,
            'category'           => $request->category,
            'quantity_limit'     => $request->quantity_limit ?: null,
            'measurement_unit'   => $request->measurement_unit,
            'addional_condition' => $request->condition_html,
            'country'            => $countryValues,
            'usage'              => [],
            'another_name'       => [],
        ]);

        // // 5. Activity log (use the same $permit)
        // if ($permit && $permit->application) {
        //     ConsignmentActivityLogger::log(
        //         application: $permit->application,
        //         event: 'permit_condition_created',
        //         description: authUser()['user']->fullname
        //             . " created a new permit condition item \"{$condition->item_name}\" from a custom item on application {$permit->application->application_id}",
        //         properties: [
        //             'permit_id'       => $permit->id,
        //             'ip_condition_id' => $condition->id,
        //         ],
        //     );
        // }

        return response()->json([
            'message' => 'Item added successfully.',
            'id'      => $condition->id,
        ]);
    }

    public function addAlias(Request $request, $id)
    {
        // dd($request->all());
        $request->validate([
            'alias'     => 'required|string|max:255',
            'permit_id' => 'nullable|integer|exists:consignment_permits,id',
        ]);

        $condition = ConsignmentCondition::findOrFail($id);

        $aliases = $condition->another_name ?? [];
        if (!in_array($request->alias, $aliases)) {
            $aliases[] = $request->alias;
            $condition->another_name = $aliases;
            $condition->save();
        }

        // 1. Retrieve the permit
        $permit = ConsignmentPermit::find($request->permit_id);
        if (!$permit) {
            return response()->json(['message' => 'Permit not found.'], 404);
        }



        // 2. Update consignment_detail
        $consignment_detail = $permit->consignment_detail ?? [];
        $consignment_detail['item_name'] = $condition->item_name;
        $consignment_detail['isCustom'] = false;
        $consignment_detail['condition'] = $condition->addional_condition;
        $permit->consignment_detail = $consignment_detail;
        $permit->save();

        // dd($consignment_detail, $permit);


        // ─── Activity Log ─────────────────────────────────
        // if ($request->permit_id) {
        //     $permit = ConsignmentPermit::with('application')->find($request->permit_id);
        //     if ($permit && $permit->application) {
        //         ApplicationActivityLogger::log(
        //             application: $permit->application,
        //             event: 'permit_condition_alias_added',
        //             description: authUser()['user']->fullname
        //                 . " added \"{$request->alias}\" as an alias to permit condition \"{$condition->item_name}\" (ID {$condition->id}) on application {$permit->application->application_id}",
        //             properties: [
        //                 'permit_id'       => $permit->id,
        //                 'ip_condition_id' => $condition->id,
        //                 'alias'           => $request->alias,
        //             ],
        //         );
        //     }
        // }

        return response()->json([
            'message' => 'Alias added successfully.',
            'id'      => $condition->id,
        ]);
    }

    public function linkCondition(Request $request, $id)
    {
        // $request->validate([
        //     'ip_condition_id' => 'required|integer|exists:ip_condition,id',
        // ]);

        $permit = ConsignmentPermit::with('application')->findOrFail($id);

        // Ensure we're working with a real array, not null/stdClass
        $detail = is_array($permit->consignment_detail) ? $permit->consignment_detail : [];
        $originalItemName = $detail['item_name'] ?? null;
        $wasCustom = $detail['isCustom'] ?? null;

        $detail['item_id']  = (int) $request->ip_condition_id;
        $detail['isCustom'] = false;

        \DB::transaction(function () use ($permit, $detail) {
            $permit->consignment_detail = $detail; // explicit attribute assignment
            $permit->status = 'processing';
            $permit->save();
        });

        $permit->refresh();

        // ─── Activity Log ─────────────────────────────────
        $application = $permit->application;

        // if ($application) {
        //     $user = authUser()['user'] ?? null;

        //     ApplicationActivityLogger::log(
        //         application: $application,
        //         event: 'custom_item_linked',
        //         description: ($user->fullname ?? 'System')
        //             . " linked custom item \"{$originalItemName}\" to permit condition ID {$request->ip_condition_id} for application {$application->application_id}",
        //         properties: [
        //             'permit_id'        => $permit->id,
        //             'ip_condition_id'  => (int) $request->ip_condition_id,
        //             'original_item'    => $originalItemName,
        //             'is_custom_before' => $wasCustom,
        //             'is_custom_after'  => $permit->consignment_detail['isCustom'] ?? null,
        //         ],
        //     );
        // }

        return response()->json([
            'message' => 'Item linked successfully.',
            'permit'  => $permit->consignment_detail, // return the persisted state for the frontend to verify
        ]);
    }
}
