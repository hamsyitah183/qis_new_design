<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\State;
use App\Models\District;

class StateDistrictController extends Controller
{
    public function getStates()
    {
        $states = State::withCount('districts')->orderBy('name')->get();
        return response()->json($states);
    }

    public function getDistricts($stateId)
    {
        $districts = District::where('state_id', $stateId)->orderBy('name')->get();
        return response()->json($districts);
    }

    public function getAllDistricts()
    {
        $districts = District::with('state')->orderBy('name')->get();
        return response()->json($districts);
    }

    public function storeDistrict(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'state_id' => 'required|exists:states,id'
        ]);

        $district = District::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'District created successfully',
            'data' => $district
        ]);
    }

    public function destroyDistrict($districtId)
    {
        $district = District::findOrFail($districtId);
        $district->delete();

        return response()->json([
            'success' => true,
            'message' => 'District deleted successfully'
        ]);
    }

    public function destroyState($stateId)
    {
        $state = State::findOrFail($stateId);
        $state->delete();

        return response()->json([
            'success' => true,
            'message' => 'State and all its districts deleted successfully'
        ]);
    }
}
