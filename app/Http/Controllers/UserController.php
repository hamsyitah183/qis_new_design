<?php

namespace App\Http\Controllers;

use App\Models\InternalUser;
use App\Models\PublicUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;

class UserController extends Controller
{
    //
    public function public_list()
    {
        // dd(Auth::guard('internal')->user());
        return view('pages.internal.user_management.list_public');
    }

    public function public_list_data()
    {
        $users = PublicUser::query();
        $currentUser = Auth::guard('internal')->user();


        return DataTables::of($users)
            ->addColumn('action', function ($user) use ($currentUser) {
                // Always available buttons
                $actionHtml = '
                <div class="d-flex align-items-center gap-2">
                    <button class="btn btn-sm btn-primary text-white viewPublicUser-modal" data-id="' . $user->uuid . '" title="View">
                        <i class="ti ti-eye"></i>
                    </button>
                    <button class="btn btn-sm btn-primary text-white editPublicUser-modal" data-id="' . $user->uuid . '" title="Edit">
                        <i class="ti ti-pencil"></i>
                    </button>
            ';

              
                if (!$currentUser || $currentUser->uuid !== $user->uuid) {
                    $actionHtml .= '
                        <button class="btn btn-sm btn-danger text-white deleteBtn" data-id="' . $user->uuid . '" title="Delete">
                            <i class="bx bx-trash-alt"></i>
                        </button>
                    ';
                }

                $actionHtml .= '</div>';

                return $actionHtml;
            })
            ->editColumn('created_at', fn($user) => $user->created_at->format('d-m-Y H:i'))
            ->editColumn('account_type', fn($user) => ucfirst($user->account_type))
            ->editColumn('doa_verified', fn($user) => $user->doa_verified ? 'Yes' : 'No')
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

    function public_user_save(Request $request)
    {
        $uuid = $request->input('uuid');

        if ($uuid) {
            $public = PublicUser::where('uuid', $uuid)->first();


            return response()->json([
                'message' => 'Save Public User',
                'user' => $public
            ]);
        } else {
            // register
            return response()->json([
                'message' => 'Create Public User',

            ]);
        }
    }


    // internal

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

    public function internal_user_data($id)
    {
        $public = InternalUser::where('uuid', $id)->first();

        return response()->json([
            'user' => $public
        ]);
    }
}
