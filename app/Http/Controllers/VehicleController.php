<?php

namespace App\Http\Controllers;

use App\Models\UserVehicleList;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    //
    public function getVehicleList()
    {
        $user = authUser();

        // Handle both array and object
        $userId = is_array($user) ? $user['user']['uuid'] : $user->uuid;

        $vehicles = UserVehicleList::where('user_id', $userId)->get();

        return response()->json([
            'vehicle' => $vehicles,
            'user'    => $user
        ]);
    }

    public function storeVehicle(Request $request)
    {
        $request->validate([
            'vehicle_name'               => 'required|string|max:255',
            'vehicle_number'             => 'required|string|max:255',

            'vehicle_registration_number' => 'nullable|string|max:255',
            'valid_from'                 => 'nullable|date',
            'valid_until'                => 'nullable|date|after_or_equal:valid_from',
        ]);

        $user = authUser();
        $userId = is_array($user) ? $user['user']['uuid'] : $user->uuid;

        $vehicle = UserVehicleList::create([
            'user_id'                      => $userId,
            'vehicle_name'                 => $request->vehicle_name,
            'vehicle_number'               => $request->vehicle_number,
            'vehicle_type'                 => $request->vehicle_type,
            'vehicle_registration_number'  => $request->vehicle_registration_number,
            'valid_from'                   => $request->valid_from,
            'valid_until'                  => $request->valid_until,
        ]);

        return response()->json([
            'id'   => $vehicle->id,
            'name' => $vehicle->vehicle_name,
            // optionally return the full vehicle object
        ], 201);
    }
}
