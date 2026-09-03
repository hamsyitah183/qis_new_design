<?php

namespace App\Http\Controllers;

use App\Models\ConsignmentPermit;
use App\Models\Country;
use App\Models\IpCondition;
use App\Models\IpConsignmentPermit;
use App\Services\ApplicationActivityLogger;
use Illuminate\Http\Request;

class IpConditionController extends Controller
{
    public function quickAdd(Request $request)
    {
        $request->validate([
            'item_name'     => 'required|string|max:255',
            'permit_id'     => 'nullable|integer|exists:ip_consignment_permit,id',
            'countrySelect' => 'nullable|array',       // optional; countries sent as array
            'countrySelect.*' => 'string|max:10',     // each country code
        ]);

        // 1. Retrieve the permit
        $permit = IpConsignmentPermit::find($request->permit_id);
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
        $condition = IpCondition::create([
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

        // 5. Activity log (use the same $permit)
        if ($permit && $permit->application) {
            ApplicationActivityLogger::log(
                application: $permit->application,
                event: 'permit_condition_created',
                description: authUser()['user']->fullname
                    . " created a new permit condition item \"{$condition->item_name}\" from a custom item on application {$permit->application->application_id}",
                properties: [
                    'permit_id'       => $permit->id,
                    'ip_condition_id' => $condition->id,
                ],
            );
        }

        return response()->json([
            'message' => 'Item added successfully.',
            'id'      => $condition->id,
        ]);
    }

    public function search(Request $request, $country)
    {
        $term = $request->input('q', '');

        $countryRecord = Country::where('name', $country)->first();

        if (!$countryRecord) {
            return response()->json(['results' => []]);
        }

        $items = IpCondition::whereJsonContains('country', $countryRecord->code)
            ->when($term, function ($query) use ($term) {
                $query->where(function ($q) use ($term) {
                    $q->where('item_name', 'like', "%{$term}%")
                        ->orWhere('item_bahasa', 'like', "%{$term}%");
                });
            })
            ->orderBy('item_name')
            ->limit(20)
            ->get(['id', 'item_name', 'item_bahasa']);

        return response()->json([
            'results' => $items->map(fn($item) => [
                'id'   => $item->id,
                'text' => $item->item_name . ($item->item_bahasa ? " ({$item->item_bahasa})" : ''),
            ]),
        ]);
    }

    public function addAlias(Request $request, $id)
    {
        $request->validate([
            'alias'     => 'required|string|max:255',
            'permit_id' => 'nullable|integer|exists:ip_consignment_permit,id',
        ]);


        $condition = IpCondition::findOrFail($id);

        $aliases = $condition->another_name ?? [];
        if (!in_array($request->alias, $aliases)) {
            $aliases[] = $request->alias;
            $condition->another_name = $aliases;
            $condition->save();
        }

        // 1. Retrieve the permit
        $permit = IpConsignmentPermit::find($request->permit_id);
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


        // ─── Activity Log ─────────────────────────────────
        if ($request->permit_id) {
            $permit = ConsignmentPermit::with('application')->find($request->permit_id);
            if ($permit && $permit->application) {
                ApplicationActivityLogger::log(
                    application: $permit->application,
                    event: 'permit_condition_alias_added',
                    description: authUser()['user']->fullname
                        . " added \"{$request->alias}\" as an alias to permit condition \"{$condition->item_name}\" (ID {$condition->id}) on application {$permit->application->application_id}",
                    properties: [
                        'permit_id'       => $permit->id,
                        'ip_condition_id' => $condition->id,
                        'alias'           => $request->alias,
                    ],
                );
            }
        }

        return response()->json([
            'message' => 'Alias added successfully.',
            'id'      => $condition->id,
        ]);
    }
}
