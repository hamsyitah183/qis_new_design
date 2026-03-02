<?php

namespace App\Http\Controllers;

use App\Models\BoundaryOfficer;
use App\Models\InternalUser;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class BoundaryOfficerController extends Controller
{
    //
    public function view()
    {
        return view('pages.internal.boundary_officer.boundary_list', [
            
        ]);
    }


    public function data(Request $request)
    {
        $query = BoundaryOfficer::with('user', 'entryPoint.districtCode');

        // Name filter (Boundary Officer name)
        if ($request->filled('name')) {
            $name = $request->input('name');
            $query->whereHas('user', function ($q) use ($name) {
                $q->where('fullname', 'like', '%' . $name . '%');
            });
        }

        // Place / Entry Point filter
        if ($request->filled('place')) {
            $place = $request->input('place');
            $query->whereHas('entryPoint', function ($q) use ($place) {
                $q->where('entry_name', 'like', '%' . $place . '%');
            });
        }

        return DataTables::eloquent($query)
            ->addColumn('name', fn($role) => $role->user->fullname)
            ->addColumn('place', fn($role) => $role->entryPoint->entry_name ?? '')
            ->addColumn('action', function ($role) {

                $actionHtml = '
                <div class="d-flex align-items-center gap-2">
                    <button class="btn btn-sm btn-primary text-white viewBoundaryUser" data-id="' . $role->user->uuid . '"     data-name="' . e($role->user->fullname) . '" title="View">
                        <i class="ti ti-eye"></i>
                    </button>
                    <button class="btn btn-sm btn-secondary text-white editBoundaryUser" data-id="' . $role->user->uuid . '" data-name="' . e($role->user->fullname) . ' " title="Edit">
                        <i class="ti ti-pencil"></i>
                    </button>
            ';

               
                $actionHtml .= '</div>';

                return $actionHtml;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function getBoundaryData($id)
    {

        $user = BoundaryOfficer::with(['entryPoint', 'user'])->where('user_id', $id)->first();

        return response()->json([
            'data' => $user
        ]);
    }

    public function saveInternal($id, Request $request)
    {
        $boundary = BoundaryOfficer::where('user_id', $id)->first();

        $boundary->ip_entry_id = $request->entryPoint;

        $boundary->save();

        return response()->json([
            'text' => 'Save'
        ]);
    }
}
