<?php

namespace App\Http\Controllers;

use App\Models\InternalUser;
use App\Models\PublicUser;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class UserController extends Controller
{
    //
    public function public_list()
    {
        return view('pages.internal.user_management.list_public');
    }

    public function public_list_data()
    {
        $users = PublicUser::query();

        return DataTables::of($users)
            ->addColumn('action', function ($user) {
                // $verifyBtn = $user->email_verified_at
                //     ? '<span class="badge bg-success">Verified</span>'
                //     : '<button class="btn btn-sm btn-primary verify-btn" data-id="' . $user->id . '">Verify</button>';

                // return $verifyBtn;
                $actionHtml = '
                    <div class="d-flex align-items-center gap-2">
                        <button class="btn btn-sm btn-primary text-white viewPublicUser-modal" data-id="' . $user->uuid . '" title="Edit">
                            <i class="ti ti-eye"></i>
                        </button>
                        <button class="btn btn-sm btn-primary text-white editPublicUser-modal" data-id="' . $user->uuid . '" title="Edit">
                            <i class="ti ti-pencil"></i>
                        </button>
                        <button class="btn btn-sm btn-danger text-white deleteBtn" data-id="' . $user->uuid . '" title="Delete">
                            <i class="bx bx-trash-alt"></i>
                        </button>
                    </div>
                ';

                return $actionHtml;
            })
            ->editColumn('created_at', function ($user) {
                return $user->created_at->format('d-m-Y H:i');
            })
            ->editColumn('account_type', function ($user) {
                return ucfirst($user->account_type);
            })
            ->editColumn('doa_verified', function ($user) {
                return $user->doa_verified ? 'Yes' : 'No';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function user_data($id)
    {
        $public = PublicUser::where('uuid', $id)->first();

        return response()->json([
            'user' => $public
        ]);
    }

    public function internal_list()
    {
        return view('pages.internal.user_management.list_internal');
    }

    public function internal_list_data(Request $request)
    {
        $query = InternalUser::select(['uuid', 'name', 'email', 'phone', 'position', 'office'])
            ->with('roles'); // If using Spatie roles

        return DataTables::of($query)
            ->addColumn('role', function ($user) {
                return $user->roles->pluck('name')->implode(', ') ?: 'N/A';
            })
            ->addColumn('action', function ($user) {
                return '
                    <button class="btn btn-sm btn-primary editInternalUser-modal" data-id="' . $user->uuid . '">
                        <i class="ti ti-edit"></i>
                    </button>
                ';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function show($uuid)
    {
        return InternalUser::where('uuid', $uuid)->firstOrFail();
    }

    public function update(Request $request, $uuid)
    {
        $user = InternalUser::where('uuid', $uuid)->firstOrFail();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:internal_users,email,' . $user->id,
            'phone' => 'required|string|unique:internal_users,phone,' . $user->id,
            'position' => 'nullable|string|max:255',
            'office' => 'nullable|string|max:255',
        ]);

        $user->update($validated);

        return response()->json(['success' => true, 'message' => 'User updated successfully']);
    }
}
