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
        $states = State::orderBy('name')->get();
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
}
