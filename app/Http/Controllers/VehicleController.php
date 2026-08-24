<?php

namespace App\Http\Controllers;

use App\Models\UserVehicleList;
use App\Models\PublicUser;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;

class VehicleController extends Controller
{
    /**
     * Display the vehicle list page (Internal).
     */
    public function index()
    {
        return view('pages.vehicle.list');
    }

    /**
     * Return DataTable data for vehicle list (Internal).
     */
    public function data()
    {
        $user = authUser();
        $userData = $user['user'] ?? null;
        $userType = $user['type'] ?? null;

        $vehicles = UserVehicleList::with('user')
            ->select('user_vehicle_lists.*')
            ->orderBy('created_at', 'desc');

        // ─── Filter for public users ──────────────────────────────
        if ($userType === 'public' && $userData) {
            $vehicles->where('user_id', $userData->uuid);
        }
        // Internal users see all (no filter)

        return DataTables::of($vehicles)
            ->addColumn('owner_name', function ($vehicle) {
                return $vehicle->user ? $vehicle->user->fullname : '—';
            })
            ->addColumn('valid_from_formatted', function ($vehicle) {
                if (!$vehicle->valid_from) return '—';
                $date = $vehicle->valid_from instanceof Carbon
                    ? $vehicle->valid_from
                    : Carbon::parse($vehicle->valid_from);
                return $date->format('d M Y');
            })
            ->addColumn('valid_until_formatted', function ($vehicle) {
                if (!$vehicle->valid_until) return '—';
                $date = $vehicle->valid_until instanceof Carbon
                    ? $vehicle->valid_until
                    : Carbon::parse($vehicle->valid_until);
                return $date->format('d M Y');
            })
            ->addColumn('created_at_formatted', function ($vehicle) {
                if (!$vehicle->created_at) return '—';
                $date = $vehicle->created_at instanceof Carbon
                    ? $vehicle->created_at
                    : Carbon::parse($vehicle->created_at);
                return $date->format('d M Y H:i');
            })
            ->addColumn('action', function ($vehicle) {
                return $vehicle->id;
            })
            ->rawColumns(['action'])
            ->make(true);
    }


    /**
     * Store a newly created vehicle (Internal admin).
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'vehicle_name'               => 'required|string|max:255',
            'vehicle_number'             => 'required|string|max:255',
            'vehicle_type'               => 'nullable|string|max:255',
            // 'vehicle_registration_number' => 'required|string|max:255',
            'valid_from'                 => 'nullable|date',
            'valid_until'                => 'nullable|date|after_or_equal:valid_from',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

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
            'message' => 'Vehicle added successfully.',
            'id'      => $vehicle->id,
        ], 201);
    }

    /**
     * Display the specified vehicle (for edit/view modal).
     */
    public function show($id)
    {
        $vehicle = UserVehicleList::with('user')->findOrFail($id);
        return response()->json($vehicle);
    }

    /**
     * Update the specified vehicle.
     */
    public function update(Request $request, $id)
    {
        $vehicle = UserVehicleList::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'vehicle_name'               => 'required|string|max:255',
            'vehicle_number'             => 'required|string|max:255',
            'vehicle_type'               => 'nullable|string|max:255',
            'vehicle_registration_number' => 'required|string|max:255',
            'valid_from'                 => 'nullable|date',
            'valid_until'                => 'nullable|date|after_or_equal:valid_from',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $vehicle->update([
            'vehicle_name'               => $request->vehicle_name,
            'vehicle_number'             => $request->vehicle_number,
            'vehicle_type'               => $request->vehicle_type,
            'vehicle_registration_number' => $request->vehicle_registration_number,
            'valid_from'                 => $request->valid_from,
            'valid_until'                => $request->valid_until,
        ]);

        return response()->json([
            'message' => 'Vehicle updated successfully.',
        ]);
    }

    /**
     * Remove the specified vehicle.
     */
    public function destroy($id)
    {
        $vehicle = UserVehicleList::findOrFail($id);
        $vehicle->delete();

        return response()->json([
            'message' => 'Vehicle deleted successfully.',
        ]);
    }

    // ─── Existing public endpoints (keep as is) ──────────────────────────

    public function getVehicleList()
    {
        $user = authUser();
        $userId = is_array($user) ? $user['user']['uuid'] : $user->uuid;
        $vehicles = UserVehicleList::where('user_id', $userId)->get();
        return response()->json(['vehicle' => $vehicles, 'user' => $user]);
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
        ], 201);
    }

    public function getVehiclesByIds(Request $request)
    {
        $ids = $request->input('ids', []);
        if (is_string($ids)) {
            $ids = array_filter(explode(',', $ids));
        }
        $ids = array_map('intval', $ids);
        if (empty($ids)) {
            return response()->json([]);
        }

        $userId = $request->input('user_id');
        if (!$userId) {
            $user = authUser();
            $userId = is_array($user) ? $user['uuid'] : $user->uuid;
        }

        $vehicles = UserVehicleList::where('user_id', $userId)
            ->whereIn('id', $ids)
            ->get();

        return response()->json($vehicles);
    }

    
}
