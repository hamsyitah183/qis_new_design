<?php

namespace App\Http\Controllers;

use App\Models\InternalUser;
use App\Models\PublicUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Str;

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
        $currentUser = Auth::guard('public')->user();

        return DataTables::of($users)
            ->addColumn('action', function ($user) use ($currentUser) {

                $actionHtml = '
                <div class="d-flex align-items-center gap-2">
                    <button class="btn btn-sm btn-primary text-white viewPublicUser-modal" data-id="' . $user->uuid . '" title="View">
                        <i class="ti ti-eye"></i>
                    </button>
                    <button class="btn btn-sm btn-secondary text-white editPublicUser-modal" data-id="' . $user->uuid . '" title="Edit">
                        <i class="ti ti-pencil"></i>
                    </button>
            ';

                // Only show delete button if not current user
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
            ->editColumn('doa_verified', function ($user) {
                if ($user->doa_verified) {
                    return '<span class="badge bg-success-transparent cursor-pointer badge-verification" data-id="' . $user->uuid . '" data-verified = "yes">Verified</span>';
                } else {
                    return '<span class="badge bg-dark-transparent cursor-pointer badge-verification" data-id="' . $user->uuid . '" data-verified = "no">Not Verified</span>';
                }
            })
            ->rawColumns(['action', 'doa_verified'])
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
        // if (Auth::guard('internal')->check()) {
        //     $user = Auth::guard('internal')->user();
        //     dd('Internal user logged in:', $user);
        // }
        return view('pages.internal.user_management.list_internal');
    }

    public function internal_list_data(Request $request)
    {
        $query = InternalUser::select(['uuid', 'fullname', 'email', 'phone', 'position', 'office'])
            ->with('roles'); // Using Spatie roles

        $currentUser = Auth::guard('internal')->user();

        return DataTables::of($query)
            ->addColumn('role', function ($user) {
                return $user->roles->pluck('fullname')->implode(', ') ?: 'N/A';
            })
            ->addColumn('action', function ($user) use ($currentUser) {
                $actionHtml = '
                <button class="btn btn-sm btn-primary viewInternalUser-modal" data-id="' . $user->uuid . '" title="View">
                    <i class="ti ti-eye"></i>
                </button>
                <button class="btn btn-sm btn-secondary editInternalUser-modal" data-id="' . $user->uuid . '" title="Edit">
                    <i class="ti ti-edit"></i>
                </button>
            ';

                if (!$currentUser || $currentUser->uuid !== $user->uuid) {
                    $actionHtml .= '
                    <button class="btn btn-sm btn-danger text-white deleteBtn" data-id="' . $user->uuid . '" title="Delete">
                        <i class="bx bx-trash-alt"></i>
                    </button>
                ';
                }

                return $actionHtml; // <-- Must return the HTML
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
            'fullname' => 'required|string|max:255',
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
        $internal = InternalUser::where('uuid', $id)->first();

        return response()->json([
            'user' => $internal
        ]);
    }

    public function internal_user_save(Request $request)
    {
        $uuid = $request->input('uuid');

        if ($uuid) {
            $internalUser = InternalUser::where('uuid', $uuid)->firstOrFail();

            $request->validate([
                'fullname' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:internal_users,email,' . $internalUser->id,
                'no_ic' => 'required|digits:12|unique:internal_users,no_ic,' . $internalUser->id,
                'phone' => 'required|digits_between:7,15|unique:internal_users,phone,' . $internalUser->id,
                'position' => 'required|string|max:255',
                'office' => 'required|string|max:255',
            ]);

            $internalUser->update([
                'fullname' => $request->fullname,
                'email' => $request->email,
                'no_ic' => $request->no_ic,
                'phone' => $request->phone,
                'position' => $request->position,
                'office' => $request->office,
            ]);

            return response()->json([
                'used id' => $uuid,
                'message' => 'User Updated'
            ]);
        } else {
            $request->validate([
                'fullname' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:internal_users,email',
                'no_ic' => 'required|digits:12|unique:internal_users,no_ic',
                'phone' => 'required|digits_between:7,15|unique:internal_users,phone',
                'position' => 'required|string|max:255',
                'office' => 'required|string|max:255',
                'role' => 'required|string', // Make sure role is sent
            ]);

            $internalUser = InternalUser::create([
                'uuid' => Str::uuid()->toString(), // ✅ Add a UUID
                'fullname' => $request->fullname,
                'email' => $request->email,
                'no_ic' => $request->no_ic,
                'phone' => $request->phone,
                'position' => $request->position,
                'office' => $request->office,
                'password' => Hash::make($request->no_ic),
            ]);

            $internalUser->assignRole($request->role);

            return response()->json([
                'used id' => $internalUser->uuid, // Return the new UUID
                'message' => 'User Created'
            ]);
        }
    }


    public function user_list($type)
    {
        if ($type === 'public') {
            $users = PublicUser::select(['fullname', 'id'])->get();
        } else {
            $users = InternalUser::select(['fullname', 'id'])->get();
        }

        return response()->json([
            'users' => $users
        ]);
    }
}
