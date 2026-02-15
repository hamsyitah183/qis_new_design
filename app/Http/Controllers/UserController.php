<?php

namespace App\Http\Controllers;

use App\Events\InternalUserAdded;
use App\Events\InternalUserAdminEvent;
use App\Events\InternalUserDeleted;
use App\Events\InternalUserEdited;
use App\Events\PublicUser as EventsPublicUser;
use App\Models\InternalUser;
use App\Models\PublicUser;
use App\Models\ApprovedPublic;
use App\Models\Postcode;
use App\Models\CountryNoPhone;
use App\Notifications\InternalUserEditedNotification;
use App\Notifications\UserNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use App\Models\State;
use App\Models\District;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Str;
use App\Events\PublicUserEvent;
use App\Models\BoundaryOfficer;
use App\Notifications\ApplicationNotification;
use App\Services\VerificationService;
use Illuminate\Support\Facades\Notification;

class UserController extends Controller
{
    //

    public function userInfo()
    {
        $user = authUser()['user'];

        $user->type = authUser()['type'];

        return response()->json($user);
    }

    public function public_list()
    {
        $user = authUser()['user'];

        $countryNo = CountryNoPhone::get();

        // if (!$user->can('create public user')) {
        //     abort(403, 'Unauthorized action.'); // or redirect to another page
        // }

        return view('pages.internal.user_management.list_public', [
            'countryNo' => $countryNo
        ]);
    }


    public function public_list_data(Request $request)
    {
        $users = PublicUser::query();

        // Apply filters from request
        if ($request->has('account_type') && $request->account_type != '') {
            $users->where('account_type', $request->account_type);
        }

        if ($request->has('email_verification') && $request->email_verification != '') {
            if ($request->email_verification == 'verified') {
                $users->whereNotNull('email_verified_at');
            } else if ($request->email_verification == 'not_verified') {
                $users->whereNull('email_verified_at');
            }
        }

        if ($request->has('account_verification') && $request->account_verification != '') {
            if ($request->account_verification == 'verified') {
                $users->whereHas('approved', function ($query) {
                    $query->where('doa_verified', true);
                });
            } else if ($request->account_verification == 'not_verified') {
                $users->whereDoesntHave('approved', function ($query) {
                    $query->where('doa_verified', true);
                });
            }
        }

        if ($request->has('sort_by') && $request->sort_by != '') {
            if ($request->sort_by == 'created_at') {
                $users->orderBy('created_at', 'asc');
            } else if ($request->sort_by == 'latest') {
                $users->orderBy('created_at', 'desc');
            }
        }
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
                    <button class="btn btn-sm btn-danger text-white deletePublicUser" data-id="' . $user->uuid . '" title="Delete">
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
                // Default icon is blank
                $icon = '';

                // Only show icon for NOT Verified with a verification attachment
                if (!$user->approved?->doa_verified && !empty($user->approved?->verification_attachment)) {
                    $icon = '<span class="text-warning fs-16 fw-bold ">
                                <i class="bi bi-exclamation-circle"></i>
                             </span>';
                }

                // If no approved record, show Not Verified without icon
                if (!$user->approved) {
                    return '<span class="badge bg-dark-transparent cursor-pointer badge-verification" data-id="'
                        . $user->uuid . '" data-verified="no">Not Verified</span>';
                }

                // Show Verified badge
                if ($user->approved->doa_verified) {
                    return '<span class="badge bg-success-transparent cursor-pointer badge-verification" data-id="'
                        . $user->uuid . '" data-verified="yes">Verified</span>';
                }

                // Show Not Verified badge (with icon if attachment exists)
                return '<span class="badge bg-dark-transparent cursor-pointer badge-verification" data-id="'
                    . $user->uuid . '" data-verified="no">Not Verified ' . $icon . '</span>';
            })



            ->rawColumns(['action', 'doa_verified'])
            ->make(true);
    }

    public function verification_list()
    {
        $count = PublicUser::whereHas('approved', function ($query) {
            $query->whereNotNull('verification_attachment')
                ->where('doa_verified', '!=', 1)
                ->where('status', '!=', 'Verification is rejected');
        })->count();

        return view('pages.internal.user_management.verification_list', compact('count'));
    }

    function verification_count()
    {
        $count = PublicUser::whereHas('approved', function ($query) {
            $query->whereNotNull('verification_attachment')
                ->where('doa_verified', '!=', 1)
                ->where('status', '!=', 'Verification is rejected');
        })->count();

        return response()->json([
            'count' => $count
        ]);
        // return $count;
    }

    public function verification_list_data()
    {
        $query = PublicUser::whereHas('approved', function ($query) {
            $query->whereNotNull('verification_attachment')
                ->where('doa_verified', '!=', 1)
                ->where('status', '!=', 'Verification is rejected');
        })->with('approved');

        \Log::info('Verification List Data Query Result Count: ' . $query->count());

        return DataTables::of($query)
            ->addColumn('verification_attachment', function ($user) {
                if ($user->approved && $user->approved->verification_attachment) {
                    return '<button class="btn btn-sm btn-info view-attachment" data-id="' . $user->uuid . '"><i class="ti ti-file-description"></i> View Attachment</button>';
                }
                return '-';
            })
            ->addColumn('action', function ($user) {
                return '
                    <div class="d-flex gap-2">
                         <button class="btn btn-sm btn-success accept-btn" data-id="' . $user->uuid . '">Accept</button>
                         <button class="btn btn-sm btn-danger reject-btn" data-id="' . $user->uuid . '">Reject</button>
                    </div>
                ';
            })
            ->rawColumns(['verification_attachment', 'action'])
            ->make(true);
    }



    public function user_data($id)
    {
        $public = PublicUser::where('uuid', $id)->first();

        return response()->json([
            'user' => $public
        ]);
    }

    public function public_user_save(Request $request)
    {
        $uuid = $request->input('uuid');

        // dd($request->all());

        if ($uuid) {
            // dd($request->all());
            // UPDATE existing user
            $public = PublicUser::where('uuid', $uuid)->firstOrFail();


            $validated = $request->validate([
                'fullname' => 'required|string|max:255',
                'email' => 'required|email|unique:public_users,email,' . $public->id,
                // 'password' => 'sometimes|min:8',
                'no_ic' => 'required|unique:public_users,no_ic,' . $public->id,
                'account_type' => 'required|in:individu,company',
                'phone_number' => 'required|unique:public_users,phone_number,' . $public->id,
                'address_1' => 'required',
                'postcode' => 'required',
                'district' => 'required',
                'state' => 'required',
            ]);

            try {
                DB::beginTransaction();

                $public->update([
                    'fullname' => $validated['fullname'],
                    'email' => $validated['email'],
                    // Only update password if provided
                    'password' => $request->filled('password') ? Hash::make($request->password) : $public->password,
                    'no_ic' => $validated['no_ic'],
                    'account_type' => $validated['account_type'],
                    'phone_number' => $validated['phone_number'],
                    'address_1' => $validated['address_1'],
                    'address_2' => $request->address_2,
                    'postcode' => $validated['postcode'],
                    'district' => $validated['district'],
                    'state' => $validated['state'],
                    'office_number' => $request->office_number
                ]);

                DB::commit();

                try {
                    event(new \App\Events\PublicUserEvent(
                        'Your profile has been updated',
                        $public->uuid
                    ));


                    event(new \App\Events\PublicUserUpdatedForInternal(
                        'A public user updated their profile',
                        $public->uuid
                    ));
                } catch (\Exception $e) {
                    \Log::info('Failed to broadcast event: ' . $e->getMessage());
                }

                $users = InternalUser::all(); // or filter by role/guard

                Notification::send($users, new UserNotification(
                    $public->fullname . ' account has been updated.',
                    authUser()['user']->fullname,
                    route('internal.public.list')
                ));

                Notification::send($public, new UserNotification(
                    'You update your account.',
                    'QIS',
                    '/profile'
                ));



                return response()->json([
                    'message' => 'Public User Updated',
                    'user' => $public,
                ]);
            } catch (\Exception $e) {
                DB::rollBack();

                return response()->json([
                    'message' => 'Update failed. Please try again.',
                    'error' => $e->getMessage(),
                ], 500);
            }
        } else {
            // CREATE new user
            $validated = $request->validate([
                'fullname' => 'required|string|max:255',
                'email' => 'required|email|unique:public_users,email',
                // 'password' => 'required|min:8',
                'no_ic' => 'required|unique:public_users,no_ic',
                'account_type' => 'required|in:individu,company',
                'phone_number' => 'required|unique:public_users,phone_number',
                'address_1' => 'required',
                'postcode' => 'required',
                'district' => 'required',
                'state' => 'required',
            ]);

            try {
                DB::beginTransaction();

                $user = PublicUser::create([
                    'fullname' => $validated['fullname'],
                    'email' => $validated['email'],
                    'password' => Hash::make($validated['no_ic']),
                    'no_ic' => $validated['no_ic'],
                    'account_type' => $validated['account_type'],
                    'phone_number' => $validated['phone_number'],
                    'address_1' => $validated['address_1'],
                    'address_2' => $request->address_2,
                    'postcode' => $validated['postcode'],
                    'district' => $validated['district'],
                    'state' => $validated['state'],
                ]);

                DB::commit();

                try {
                    event(new \App\Events\PublicUserUpdatedForInternal(
                        'A public user created an account',
                        $user->uuid
                    ));
                } catch (\Exception $e) {
                    \Log::info('Failed to broadcast event: ' . $e->getMessage());
                }

                $users = InternalUser::all(); // or filter by role/guard

                Notification::send($users, new UserNotification(
                    $user->fullname . ' account has been created.',
                    authUser()['user']->fullname,
                    route('internal.public.list')
                ));

                Notification::send($user, new UserNotification(
                    'You created an account.',
                    'QIS',
                    '#'
                ));
                Notification::send($user, new UserNotification(
                    'Upload your verification ID.',
                    'QIS',
                    '/profile'
                ));


                return response()->json([
                    'message' => 'Public User Created',
                    'user' => $user,
                ]);
            } catch (\Exception $e) {
                DB::rollBack();

                return response()->json([
                    'message' => 'Registration failed. Please try again.',
                    'error' => $e->getMessage(),
                ], 500);
            }
        }
    }

    public function public_user_delete($id)
    {
        $public = PublicUser::where('uuid', $id)->first();

        $public->delete();

        return response()->json([
            'user' => $public
        ]);
    }

    public function internal_user_delete($id)
    {
        $user = InternalUser::where('uuid', $id)->first();

        if (!$user) {
            return response()->json([
                'message' => 'User not found'
            ], 404);
        }

        // Store user name before deletion for event message
        $userName = $user->fullname;
        $actorName = authUser()['user']->fullname;

        $user->delete();

        try {
            event(new InternalUserDeleted($userName . ' account has been deleted by ' . $actorName));
        } catch (\Exception $e) {
            \Log::warning('Failed to broadcast internal user deleted event: ' . $e->getMessage());
        }

        return response()->json([
            'message' => 'User deleted successfully'
        ]);
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
        $query = InternalUser::select(['uuid', 'fullname', 'email', 'phone_number', 'position', 'office'])
            ->with('roles'); // Using Spatie roles

        $currentUser = Auth::guard('internal')->user();

        return DataTables::of($query)
            ->addColumn('role', function ($user) {
                return $user->getRoleNames()->implode(', ') ?: 'N/A';
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
        $actor = authUser()['user'];
        $url = route('internal.internal.list');
        
        // dd('internal user save', $request->all());

        return DB::transaction(function () use ($request, $actor, $url) {

            $uuid = $request->input('uuid');

            if ($uuid) {
                // =========================
                // UPDATE USER
                // =========================
                $internalUser = InternalUser::where('uuid', $uuid)->firstOrFail();

                $request->validate([
                    'fullname' => 'required|string|max:255',
                    'email' => 'required|email|max:255|unique:internal_users,email,' . $internalUser->id,
                    'no_ic' => 'required|digits:12|unique:internal_users,no_ic,' . $internalUser->id,
                    'phone_number' => 'required|unique:internal_users,phone_number,' . $internalUser->id,
                    'position' => 'required|string|max:255',
                    'office' => 'required|string|max:255',
                    'role' => 'required|string',
                ]);

                $internalUser->update([
                    'fullname' => $request->fullname,
                    'email' => $request->email,
                    'no_ic' => $request->no_ic,
                    'phone_number' => $request->phone_number,
                    'position' => $request->position,
                    'office' => $request->office,
                ]);

                // Sync role
                $internalUser->syncRoles([$request->role]);

                // Notify edited user (if not self)
                if ($internalUser->uuid !== $actor->uuid) {
                    $internalUser->notify(new InternalUserEditedNotification(
                        'Your account was updated',
                        'Your account details were updated by ' . $actor->fullname,
                        $url
                    ));
                }

                // Notify actor
                $actor->notify(new InternalUserEditedNotification(
                    'Account updated',
                    'You updated ' . $internalUser->fullname . '\'s account',
                    $url
                ));

                // Broadcast
                try {
                    event(new InternalUserEdited(
                        $internalUser->fullname . ' account was edited by ' . $actor->fullname,
                        $internalUser->uuid
                    ));
                } catch (\Exception $e) {
                    \Log::info('Broadcast failed: ' . $e->getMessage());
                }

                return response()->json([
                    'used_id' => $uuid,
                    'message' => 'User Updated'
                ]);
            }

            // =========================
            // CREATE USER
            // =========================
            $request->validate([
                'fullname' => 'required|string|max:255',
                'email' => 'required|email|max:255|unique:internal_users,email',
                'no_ic' => 'required|digits:12|unique:internal_users,no_ic',
                'phone_number' => 'required|digits_between:7,15|unique:internal_users,phone_number',
                'position' => 'required|string|max:255',
                'office' => 'required|string|max:255',
                'role' => 'required|string',
            ]);

            $internalUser = InternalUser::create([
                'uuid' => Str::uuid(),
                'fullname' => $request->fullname,
                'email' => $request->email,
                'no_ic' => $request->no_ic,
                'phone_number' => $request->phone_number,
                'position' => $request->position,
                'office' => $request->office,
                'password' => Hash::make($request->no_ic),
            ]);

            $internalUser->assignRole($request->role);

            if ($request->role === 'boundary officer') {
                BoundaryOfficer::create([
                    'user_id' => $internalUser->uuid
                ]);

                activity()
                    ->useLog('user_activity')
                    ->event('created')
                    ->performedOn($internalUser)
                    ->causedBy($actor)
                    ->log("{$internalUser->fullname} is new user for boundary officer.");
            }

            // Notify creator
            $actor->notify(new InternalUserEditedNotification(
                'User created',
                'You created a new user: ' . $internalUser->fullname,
                $url
            ));

            try {
                event(new InternalUserAdded('A new internal user has been added'));
            } catch (\Exception $e) {
                \Log::info('Broadcast failed: ' . $e->getMessage());
            }

            return response()->json([
                'used_id' => $internalUser->uuid,
                'message' => 'User Created'
            ]);
        });
    }


    public function user_list($type)
    {
        if ($type === 'public') {
            $users = PublicUser::select(['fullname', 'id', 'uuid'])->get();
        } else {
            $users = InternalUser::select(['fullname', 'id', 'uuid'])->get();
        }

        return response()->json([
            'users' => $users
        ]);
    }

    public function profile()
    {
        // $user = authUser()['user'];

        // $roles = $user->getRoleNames(); // returns a collection of role names

        // $permissions = $user->getAllPermissions(); // returns a collection of Permission models

        return view('pages.authentication.profile', [
            'title' => 'Profile',
            'states' => State::all()
        ]);
    }

    public function userData()
    {
        $user = authUser();

        return response()->json($user);
    }

    public function updateData(Request $request)
    {
        if ($request->type == 'public') {
            // call update
            return $this->public_user_save($request);
        } else {
            // call internal_user_save
            return $this->internal_user_save($request);
        }
    }

    public function password(Request $request)
    {
        // dd($request->all());
        $validated = $request->validate([
            // 'type' => 'required|in:public,internal',
            // 'uuid' => 'required|uuid',
            'old_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed', // expects new_password + new_password_confirmation
        ]);

        // Select correct user model
        $user = $request['type'] === 'public'
            ? PublicUser::where('uuid', $request['uuid'])->first()
            : InternalUser::where('uuid', $request['uuid'])->first();

        if (!$user) {
            return response()->json([
                'message' => 'User not found.',
            ], 404);
        }

        // Verify old password before allowing change
        if (!Hash::check($validated['old_password'], $user->password)) {
            return response()->json([
                'message' => 'Old password is incorrect.',

            ], 400);
        }

        // Update password
        $user->password = Hash::make($validated['new_password']);
        $user->save();

        return response()->json([
            'message' => 'Password updated successfully.',
        ]);
    }

    // public function uploadVerificationAttachment(Request $request, $id)
    // {
    //     // $userId = $request->input('user_id');
    //     $userId = $id ?? $request->input('user_id');

    //     if (!$userId) {
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'User ID is required.',
    //         ], 400);
    //     }

    //     DB::beginTransaction();

    //     try {
    //         $verification = ApprovedPublic::where('user_id', $userId)->first();

    //         // Create a new record if it doesn't exist yet
    //         if (!$verification) {
    //             $verification = new ApprovedPublic();
    //             $verification->user_id = $userId;
    //         }

    //         if ($request->hasFile('attachment')) {
    //             // Delete old file if it exists
    //             if ($verification->verification_attachment && file_exists(public_path($verification->verification_attachment))) {
    //                 unlink(public_path($verification->verification_attachment));
    //             }

    //             $file = $request->file('attachment');

    //             // Make sure the directory exists
    //             $destinationPath = public_path('storage/app/public/verifications');
    //             if (!file_exists($destinationPath)) {
    //                 mkdir($destinationPath, 0755, true);
    //             }

    //             $filename = time() . '_' . $file->getClientOriginalName();
    //             $file->move($destinationPath, $filename);

    //             // Save relative path for database
    //             $verification->verification_attachment = 'storage/app/public/verifications/' . $filename;
    //         }

    //         $verification->status = 'waiting for approval';

    //         $verification->save();

    //         DB::commit();

    //         return response()->json([
    //             'success' => true,
    //             'file_url' => $verification->verification_attachment,
    //             'message' => 'File uploaded successfully.',
    //         ]);
    //     } catch (\Exception $e) {
    //         DB::rollBack();

    //         Log::error('Verification upload failed: ' . $e->getMessage());

    //         return response()->json([
    //             'success' => false,
    //             'message' => 'An error occurred while uploading the file.',
    //             'error' => $e->getMessage(),
    //         ], 500);
    //     }
    // }

    public function uploadVerificationAttachment(Request $request, VerificationService $verificationService)
    {
        // dd('upload');
        $userId = $request->user_id;
        $file = $request->file('attachment');

        $result = $verificationService->uploadVerificationAttachment($userId, $file);

        $user = PublicUser::where("uuid", $userId)->first();



        try {
            event(new InternalUserAdminEvent(
                $user->fullname . ' is uploaded a verification attachement.'
            ));

            event(new PublicUserEvent(
                'You Upload a verification attachment',
                $user->uuid
            ));
        } catch (\Exception $e) {
            \Log::info('Failed to broadcast event: ' . $e->getMessage());
        }

        $users = InternalUser::role(['admin', 'superadmin'])->get();
        $notificationUrl = route('internal.public.list');
        Notification::send($users, new ApplicationNotification(
            'A user upload a verification attachment',
            $user->fullname,
            $notificationUrl
        ));


        $user->notify(new ApplicationNotification(
            'You Upload a verification attachment',
            'QIS',
            '/profile'
        ));

        activity()
            ->useLog('user_activity')
            ->event('verified')
            ->performedOn($user)
            ->causedBy(authUser()['user'])
            ->log("{$user->fullname} is uploading an attachment to get verification.");


        return response()->json($result, $result['success'] ? 200 : 500);
    }

    public function getStates()
    {
        $states = State::all();
        return response()->json($states);
    }

    public function getDistricts($state_id)
    {
        $districts = District::where('state_id', $state_id)->get();
        return response()->json($districts);
    }

    public function getPostcodes($district_id)
    {
        $postcodes = Postcode::where('district_id', $district_id)->get();
        return response()->json($postcodes);
    }




    public function verification_attachment($id)
    {
        \Log::info("Fetching verification for user: {$id}");

        $verification = ApprovedPublic::with(['publicUser', 'approver'])
            ->where('user_id', $id)
            ->first();

        if (!$verification) {
            \Log::warning("No verification record found for user_id: {$id}");
            return response()->json(['error' => 'Verification not found'], 404);
        }

        return response()->json($verification);
    }

    public function save_attachment($id, Request $request)
    {
        $internal = authUser()['user'];

        //  dd('requesr', $request->all(), ' id', $id);

        DB::beginTransaction();

        try {
            // 🔹 Fetch related records
            $verification = ApprovedPublic::with(['publicUser', 'approver'])
                ->where('user_id', $id)
                ->firstOrFail();

            $user = PublicUser::where('uuid', $id)->firstOrFail();

            $isApproved = 0;

            // 🔹 Update based on approval type
            if ($request->input('approved') === 'yes') {
                $verification->doa_verified = 1;
                $user->doa_verified = 1;
                $verification->doa_approved_time = now();
                $verification->approved_by = $internal->uuid;
                $verification->status = 'Verified and approved';
                $verification->reason = null;

                $isApproved = 1;
            } else {
                // dd('requesr', $request->all());
                $verification->doa_verified = 0;
                $user->doa_verified = 0;
                $verification->doa_approved_time = now();
                $verification->approved_by = $internal->uuid;
                $verification->status = 'Verification is rejected';
                $verification->reason = $request->input('reason');
            }


            // 🔹 Save both models
            $verification->save();
            $user->save();

            // 🔹 Commit if all good
            DB::commit();

            try {
                event(new InternalUserAdminEvent(
                    $isApproved ? $user->fullname . ' account is verified' :
                    $user->fullname . ' account verification is rejected'
                ));

                event(new PublicUserEvent(
                    $isApproved ? 'Your Account is verified by DOA' :
                    'Your Account is not verified by DOA',
                    $user->uuid
                ));
            } catch (\Exception $e) {
                \Log::info('Failed to broadcast event: ' . $e->getMessage());
            }

            $users = InternalUser::role(['admin', 'superadmin'])->get();
            $notificationUrl = route('internal.public.list');
            Notification::send($users, new ApplicationNotification(
                $isApproved ? $user->fullname . ' account is verified' : $user->fullname . ' account verification is rejected',
                $user->fullname,
                $notificationUrl
            ));

            activity()
                ->useLog('user_activity')
                ->event('verified')
                ->performedOn($user)
                ->causedBy(authUser()['user'])
                ->log(
                    $isApproved ? "{$user->fullname} was verified by " . authUser()['user']['fullname'] :
                    "{$user->fullname}'s verification is rejected by " . authUser()['user']['fullname']
                );
            $user->notify(new ApplicationNotification(
                $isApproved ? 'Your account is verified' : 'Your account verification is rejected',
                'QIS',
                '/profile'
            ));

            if ($isApproved) {
                $user->notify(new ApplicationNotification(
                    'Start apply new application',
                    'QIS',
                    '/public/new_application'
                ));
            }

            return response()->json([
                'success' => true,
                'message' => $request->input('approved') === 'yes'
                    ? 'User successfully verified.'
                    : 'User verification has been rejected.',
            ], 200);
        } catch (\Exception $e) {
            // 🔹 Rollback on failure
            DB::rollBack();

            Log::error('Verification update failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while saving verification status.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
