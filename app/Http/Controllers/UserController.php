<?php

namespace App\Http\Controllers;

use App\Events\InternalUserAdded;
use App\Events\InternalUserAdminEvent;
use App\Events\InternalUserDeleted;
use App\Events\InternalUserEdited;
use App\Events\PublicUser as EventsPublicUser;
use App\Models\InternalUser;
use App\Models\Branch;
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
use App\Models\DocumentRequirement;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Str;
use App\Events\PublicUserEvent;
use App\Models\BoundaryOfficer;
use App\Models\UserAttachment;
use App\Notifications\ApplicationNotification;
use App\Services\VerificationService;
use Illuminate\Support\Facades\Gate;
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
        if (auth()->user()->hasRole('boundary officer')) {
            abort(403, 'Unauthorized action. Boundary Officers are restricted from this area.');
        }

        Gate::authorize('read public user');

        $user = authUser()['user'];

        $countryNo = CountryNoPhone::get();

        return view('pages.internal.user_management.list_public', [
            'countryNo' => $countryNo,
        ]);
    }

    public function public_list_data(Request $request)
    {
        if (auth()->user()->hasRole('boundary officer')) {
            abort(403, 'Unauthorized action. Boundary Officers are restricted from this area.');
        }

        $users = PublicUser::query();

        // Apply filters from request
        if ($request->has('account_type') && $request->account_type != '') {
            $users->where('account_type', $request->account_type);
        }

        if ($request->has('email_verification') && $request->email_verification != '') {
            if ($request->email_verification == 'verified') {
                $users->whereNotNull('email_verified_at');
            } elseif ($request->email_verification == 'not_verified') {
                $users->whereNull('email_verified_at');
            }
        }

        if ($request->has('account_verification') && $request->account_verification != '') {
            if ($request->account_verification == 'verified') {
                $users->whereHas('approved', function ($query) {
                    $query->where('doa_verified', true);
                });
            } elseif ($request->account_verification == 'not_verified') {
                $users->whereDoesntHave('approved', function ($query) {
                    $query->where('doa_verified', true);
                });
            }
        }

        if ($request->has('sort_by') && $request->sort_by != '') {
            if ($request->sort_by == 'created_at') {
                $users->orderBy('created_at', 'asc');
            } elseif ($request->sort_by == 'latest') {
                $users->orderBy('created_at', 'desc');
            }
        }
        $currentUser = Auth::guard('public')->user();

        $canApprove = Gate::allows('approve public user');

        return DataTables::of($users)
            ->addColumn('action', function ($user) use ($currentUser, $canApprove) {
                $canRead = Gate::allows('read public user');
                $canUpdate = Gate::allows('update public user');
                $canDelete = Gate::allows('delete public user');

                $actionHtml = '<div class="d-flex align-items-center gap-2">';

                if ($canRead) {
                    $actionHtml .=
                        '
                        <a href="' . route('internal.public.view', $user->uuid) . '" class="btn btn-sm btn-primary text-white" title="View">
                            <i class="ti ti-eye"></i>
                        </a>
                    ';
                }

                if ($canUpdate) {
                    $actionHtml .=
                        '
                        <button class="btn btn-sm btn-secondary text-white editPublicUser-modal"
                                data-id="' .
                        $user->uuid .
                        '" title="Edit">
                            <i class="ti ti-pencil"></i>
                        </button>
                    ';
                }

                // ✅ Only show delete if user has permission AND it's not the current user
                if ($canDelete && (!$currentUser || $currentUser->uuid !== $user->uuid)) {
                    $actionHtml .=
                        '
                        <button class="btn btn-sm btn-danger text-white deletePublicUser"
                                data-id="' .
                        $user->uuid .
                        '" title="Delete">
                            <i class="bx bx-trash-alt"></i>
                        </button>
                    ';
                }

                $actionHtml .= '</div>';

                return $actionHtml;
            })
            ->editColumn('created_at', fn($user) => $user->created_at->format('d-m-Y H:i'))
            ->editColumn('account_type', fn($user) => ucfirst($user->account_type))
            // ✅ Only add doa_verified column if user has permission
            ->editColumn('doa_verified', function ($user) use ($canApprove) {
                $icon = '';

                if (!$user->approved?->doa_verified && !empty($user->approved?->verification_attachment)) {
                    $icon = '<span class="text-warning fs-16 fw-bold"><i class="bi bi-exclamation-circle"></i></span>';
                }

                // ✅ badge class + data attributes depend on permission
                $badgeClass = $canApprove ? 'badge-verification cursor-pointer' : '';
                $dataAttribs = $canApprove ? 'data-id="' . $user->uuid . '" data-verified="no"' : '';
                $dataYes = $canApprove ? 'data-id="' . $user->uuid . '" data-verified="yes"' : '';

                if (!$user->approved) {
                    return '<span class="badge bg-dark-transparent ' . $badgeClass . '" ' . $dataAttribs . '>Not Verified</span>';
                }

                if ($user->approved->doa_verified) {
                    return '<span class="badge bg-success-transparent ' . $badgeClass . '" ' . $dataYes . '>Verified</span>';
                }

                return '<span class="badge bg-dark-transparent ' . $badgeClass . '" ' . $dataAttribs . '>Not Verified ' . $icon . '</span>';
            })
            // ✅ Only include doa_verified in rawColumns if user has permission
            ->rawColumns(['action', 'doa_verified'])
            ->make(true);
    }

    public function public_user_view($id)
    {
        if (auth()->user()->hasRole('boundary officer')) {
            abort(403, 'Unauthorized action. Boundary Officers are restricted from this area.');
        }

        $user = PublicUser::with(['attachments', 'vehicles'])->where('uuid', $id)->firstOrFail();

        $activities = $user->activities()->orderBy('created_at', 'desc')->get();

        return view('pages.internal.user_management.view_public', compact('user', 'activities'));
    }

    public function verification_list()
    {
        if (auth()->user()->hasRole('boundary officer')) {
            abort(403, 'Unauthorized action. Boundary Officers are restricted from this area.');
        }

        $count = PublicUser::whereHas('approved', function ($query) {
            $query->whereNotNull('verification_attachment')->where('doa_verified', '!=', 1)->where('status', '!=', 'Verification is rejected');
        })->count();

        return view('pages.internal.user_management.verification_list', compact('count'));
    }
    public function verification_count()
    {
        // 1. Restrict access (as before)
        if (auth()->user()->hasRole('boundary officer')) {
            abort(403, 'Unauthorized action. Boundary Officers are restricted from this area.');
        }

        // 2. Get users who have at least one attachment with is_read = false
        $users = PublicUser::whereHas('attachments', function ($query) {
            $query->where('is_read', false);
        })
            ->with(['attachments' => function ($query) {
                // Only load unread attachments (optional, but useful)
                $query->where('is_read', false);
            }])
            ->get();

        // 3. Return count + the list
        return response()->json([
            'count' => $users->count(),
            // 'data'  => $users, // includes all user fields and their unread attachments
        ]);
    }

    public function verification_list_data(Request $request)
    {
        Gate::authorize('approve public user');

        // Get required document types (from DocumentRequirement)
        $docTypes = DocumentRequirement::where('module', 'user')
            ->where('is_required', true)
            ->where('is_active', true)
            ->pluck('name')
            ->toArray();

        // Query ApprovedPublic with publicUser and their attachments
        $query = ApprovedPublic::with(['publicUser', 'publicUser.attachments'])
            ->where('doa_verified', '!=', 1)
            ->where('status', '!=', 'Verification is rejected')
            ->whereHas('publicUser.attachments'); // only users with at least one attachment

        // Filters
        if ($request->filled('name')) {
            $query->whereHas('publicUser', function ($q) use ($request) {
                $q->where('fullname', 'like', '%' . $request->input('name') . '%');
            });
        }
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->input('start_date'));
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->input('end_date'));
        }

        return DataTables::of($query)
            ->addColumn('fullname', function ($approved) {
                return $approved->publicUser->fullname ?? '';
            })
            ->addColumn('email', function ($approved) {
                return $approved->publicUser->email ?? '';
            })
            // ─── Overall status badge ──────────────────────────────
            ->addColumn('status_badge', function ($approved) {
                $user = $approved->publicUser;
                if (!$user) return '<span class="badge bg-secondary">No user</span>';

                $latest = $user->attachments()->latest()->first();
                if (!$latest) return '<span class="badge bg-secondary">No files</span>';

                if ($latest->rejected_reason) {
                    return '<span class="badge bg-danger">Rejected</span>';
                }
                if ($latest->is_read) {
                    if ($latest->valid_until && now()->greaterThan($latest->valid_until)) {
                        return '<span class="badge bg-warning text-dark">Expired</span>';
                    }
                    return '<span class="badge bg-success">Verified</span>';
                }
                return '<span class="badge bg-info">Pending Review</span>';
            })
            // ─── Document type buttons + View All ──────────────────
            ->addColumn('documents', function ($approved) use ($docTypes) {
                $user = $approved->publicUser;
                $attachments = $user ? $user->attachments : collect();

                $html = '<div class="d-flex flex-wrap gap-1" style="max-width:450px;">';

                // Buttons for each required document type
                foreach ($docTypes as $type) {
                    $has = $attachments->contains('document_type', $type);
                    if ($has) {
                        $short = strlen($type) > 25 ? substr($type, 0, 22) . '…' : $type;
                        $html .= '<button class="btn btn-sm btn-outline-primary view-doc-type" 
                                data-id="' . $approved->user_id . '" 
                                data-doc-type="' . htmlspecialchars($type) . '"
                                title="' . htmlspecialchars($type) . '"
                                style="font-size:0.7rem; padding:2px 6px;">
                                ' . htmlspecialchars($short) . '
                              </button>';
                    } else {
                        // Show a grey cross for missing types
                        $html .= '<span class="badge bg-light text-muted" title="Not uploaded" style="font-size:0.7rem;">✕</span>';
                    }
                }

                // "View All" button (always shown if there is at least one attachment)
                if ($attachments->count() > 0) {
                    $html .= '<button class="btn btn-sm btn-secondary view-attachment" 
                            data-id="' . $approved->user_id . '"
                            style="font-size:0.7rem; padding:2px 8px;">
                            <i class="ti ti-file-description"></i> All
                          </button>';
                }

                $html .= '</div>';
                return $html;
            })
            // ─── Accept / Reject ────────────────────────────────────
            ->addColumn('action', function ($approved) {
                return '
                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-success accept-btn" data-id="' . $approved->user_id . '">Accept</button>
                    <button class="btn btn-sm btn-danger reject-btn" data-id="' . $approved->user_id . '">Reject</button>
                </div>
            ';
            })
            ->rawColumns(['documents', 'status_badge', 'action'])
            ->make(true);
    }
    public function user_data($id)
    {
        if (auth()->user()->hasRole('boundary officer')) {
            abort(403, 'Unauthorized action. Boundary Officers are restricted from this area.');
        }

        $public = PublicUser::where('uuid', $id)->first();

        return response()->json([
            'user' => $public,
        ]);
    }

    public function public_user_save(Request $request)
    {
        $uuid = $request->input('uuid');

        if ($uuid) {
            // UPDATE existing user
            $public = PublicUser::where('uuid', $uuid)->firstOrFail();

            $validated = $request->validate([
                'fullname' => 'required|string|max:255',
                'email' => 'required|email|unique:public_users,email,' . $public->id,
                'no_ic' => 'required|unique:public_users,no_ic,' . $public->id,
                'account_type' => 'required|in:individu,company',
                'phone_number' => 'required|unique:public_users,phone_number,' . $public->id,
                'address_1' => 'required',
                'postcode' => 'required',
                'district' => 'required',
                'state' => 'required',
                // PIC fields (optional)
                'pic_name.*' => 'nullable|string|max:255',
                'pic_position.*' => 'nullable|string|max:255',
                'pic_phone.*' => 'nullable|string|max:20',
            ]);

            try {
                DB::beginTransaction();

                // ─── Build Person In Charge ──────────────────────────────
                $personInCharge = null;
                if ($validated['account_type'] === 'company') {
                    $picNames = $request->input('pic_name', []);
                    $picPositions = $request->input('pic_position', []);
                    $picPhones = $request->input('pic_phone', []);
                    $pics = [];

                    foreach ($picNames as $index => $name) {
                        $name = trim($name);
                        if (!empty($name) && isset($picPositions[$index]) && isset($picPhones[$index])) {
                            $pics[] = [
                                'name' => $name,
                                'position' => trim($picPositions[$index]),
                                'phone' => trim($picPhones[$index]),
                            ];
                        }
                    }
                    if (!empty($pics)) {
                        $personInCharge = $pics;
                    }
                }

                // ─── Update user ────────────────────────────────────────────
                $public->update([
                    'fullname' => $validated['fullname'],
                    'email' => $validated['email'],
                    'password' => $request->filled('password') ? Hash::make($request->password) : $public->password,
                    'no_ic' => $validated['no_ic'],
                    'account_type' => $validated['account_type'],
                    'phone_number' => $validated['phone_number'],
                    'address_1' => $validated['address_1'],
                    'address_2' => $request->address_2,
                    'postcode' => $validated['postcode'],
                    'district' => $validated['district'],
                    'state' => $validated['state'],
                    'office_number' => $request->office_number,
                    'person_in_charge' => $personInCharge,
                ]);

                DB::commit();

                // ─── Events and notifications ──────────────────────────────
                try {
                    event(new \App\Events\PublicUserEvent('Your profile has been updated', $public->uuid));
                    event(new \App\Events\PublicUserUpdatedForInternal('A public user updated their profile', $public->uuid));
                } catch (\Exception $e) {
                    \Log::info('Failed to broadcast event: ' . $e->getMessage());
                }

                $users = InternalUser::all();
                Notification::send($users, new UserNotification($public->fullname . ' account has been updated.', authUser()['user']->fullname, route('internal.public.list')));
                Notification::send($public, new UserNotification('You update your account.', 'QIS', '/profile'));

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
                'no_ic' => 'required|unique:public_users,no_ic',
                'account_type' => 'required|in:individu,company',
                'phone_number' => 'required|unique:public_users,phone_number',
                'address_1' => 'required',
                'postcode' => 'required',
                'district' => 'required',
                'state' => 'required',
                // PIC fields (optional)
                'pic_name.*' => 'nullable|string|max:255',
                'pic_position.*' => 'nullable|string|max:255',
                'pic_phone.*' => 'nullable|string|max:20',
            ]);

            try {
                DB::beginTransaction();

                // ─── Build Person In Charge ──────────────────────────────
                $personInCharge = null;
                if ($validated['account_type'] === 'company') {
                    $picNames = $request->input('pic_name', []);
                    $picPositions = $request->input('pic_position', []);
                    $picPhones = $request->input('pic_phone', []);
                    $pics = [];

                    foreach ($picNames as $index => $name) {
                        $name = trim($name);
                        if (!empty($name) && isset($picPositions[$index]) && isset($picPhones[$index])) {
                            $pics[] = [
                                'name' => $name,
                                'position' => trim($picPositions[$index]),
                                'phone' => trim($picPhones[$index]),
                            ];
                        }
                    }
                    if (!empty($pics)) {
                        $personInCharge = $pics;
                    }
                }

                // ─── Create user ────────────────────────────────────────────
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
                    'person_in_charge' => $personInCharge,
                ]);

                DB::commit();

                // ─── Events and notifications ──────────────────────────────
                try {
                    event(new \App\Events\PublicUserUpdatedForInternal('A public user created an account', $user->uuid));
                } catch (\Exception $e) {
                    \Log::info('Failed to broadcast event: ' . $e->getMessage());
                }

                $users = InternalUser::all();
                Notification::send($users, new UserNotification($user->fullname . ' account has been created.', authUser()['user']->fullname, route('internal.public.list')));
                Notification::send($user, new UserNotification('You created an account.', 'QIS', '#'));
                Notification::send($user, new UserNotification('Upload your verification ID.', 'QIS', '/profile'));

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
        if (auth()->user()->hasRole('boundary officer')) {
            abort(403, 'Unauthorized action. Boundary Officers are restricted from this area.');
        }

        $public = PublicUser::where('uuid', $id)->first();

        $public->delete();

        return response()->json([
            'user' => $public,
        ]);
    }

    public function internal_user_delete($id)
    {
        if (auth()->user()->hasRole('boundary officer')) {
            abort(403, 'Unauthorized action. Boundary Officers are restricted from this area.');
        }

        $user = InternalUser::where('uuid', $id)->first();

        if (!$user) {
            return response()->json(
                [
                    'message' => 'User not found',
                ],
                404,
            );
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
            'message' => 'User deleted successfully',
        ]);
    }

    // internal

    public function internal_list()
    {
        Gate::authorize('read internal user');

        $actor = authUser()['user'];
        $actorRole = $actor->getRoleNames()->first();
        $isAdminOrSuperadmin = in_array($actorRole, ['admin', 'superadmin']);
        $branches = Branch::orderBy('name')->get();

        return view('pages.internal.user_management.list_internal', [
            'isAdminOrSuperadmin' => $isAdminOrSuperadmin,
            'branches' => $branches,
        ]);
    }

    public function internal_list_data(Request $request)
    {
        $query = InternalUser::select(['uuid', 'fullname', 'email', 'phone_number', 'position', 'office', 'branch'])->with('roles'); // Using Spatie roles

        if ($request->has('role') && $request->role != '') {
            $roles = explode(',', $request->role);
            $query->whereHas('roles', function ($q) use ($roles) {
                $q->whereIn('name', $roles);
            });
        }

        if ($request->has('branch') && $request->branch != '') {
            $branches = explode(',', $request->branch);
            $query->whereIn('branch', $branches);
        }

        $currentUser = Auth::guard('internal')->user();

        return DataTables::of($query)
            ->addColumn('role', function ($user) {
                return $user->getRoleNames()->implode(', ') ?: 'N/A';
            })

            ->addColumn('action', function ($user) use ($currentUser) {
                $canRead = Gate::allows('read internal user');
                $canUpdate = Gate::allows('update internal user');
                $canDelete = Gate::allows('delete internal user');

                $actionHtml = '';

                if ($canRead) {
                    $actionHtml .=
                        '
            <a href="' . route('internal.internal.view', $user->uuid) . '" class="btn btn-sm btn-primary text-white" title="View">
                <i class="ti ti-eye"></i>
            </a>
        ';
                }

                if ($canUpdate) {
                    $actionHtml .=
                        '
            <button class="btn btn-sm btn-secondary editInternalUser-modal"
                    data-id="' .
                        $user->uuid .
                        '" title="Edit">
                <i class="ti ti-edit"></i>
            </button>
        ';
                }

                if ($canDelete && (!$currentUser || $currentUser->uuid !== $user->uuid)) {
                    $actionHtml .=
                        '
            <button class="btn btn-sm btn-danger text-white deleteBtn"
                    data-id="' .
                        $user->uuid .
                        '" title="Delete">
                <i class="bx bx-trash-alt"></i>
            </button>
        ';
                }

                return $actionHtml;
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

    public function internal_user_view($id)
    {
        $user = InternalUser::with('roles')->where('uuid', $id)->firstOrFail();
        $activities = $user->activities()->orderBy('created_at', 'desc')->get();
        return view('pages.internal.user_management.view_internal', compact('user', 'activities'));
    }

    public function internal_user_data($id)
    {
        $internal = InternalUser::with('roles')->where('uuid', $id)->first();

        return response()->json([
            'user' => $internal,
        ]);
    }

    public function internal_user_save(Request $request)
    {
        if (auth()->user()->hasRole('boundary officer')) {
            abort(403, 'Unauthorized action. Boundary Officers are restricted from this area.');
        }

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
                    // 'office' => 'required|string|max:255',
                    'role' => 'required|string',
                ]);

                $updateData = [
                    'fullname' => $request->fullname,
                    'email' => $request->email,
                    'no_ic' => $request->no_ic,
                    'phone_number' => $request->phone_number,
                    'position' => $request->position,
                    'office' => $request->office,
                ];

                // Only admin/superadmin can set branch
                $actorRole = $actor->getRoleNames()->first();
                if (in_array($actorRole, ['admin', 'superadmin'])) {
                    $updateData['branch'] = $request->branch;
                }

                $internalUser->update($updateData);

                // Sync role
                $internalUser->syncRoles([$request->role]);

                // Notify edited user (if not self)
                if ($internalUser->uuid !== $actor->uuid) {
                    $internalUser->notify(new InternalUserEditedNotification('Your account was updated', 'Your account details were updated by ' . $actor->fullname, $url));
                }

                // Notify actor
                $actor->notify(new InternalUserEditedNotification('Account updated', 'You updated ' . $internalUser->fullname . '\'s account', $url));

                // Broadcast
                try {
                    event(new InternalUserEdited($internalUser->fullname . ' account was edited by ' . $actor->fullname, $internalUser->uuid));
                } catch (\Exception $e) {
                    \Log::info('Broadcast failed: ' . $e->getMessage());
                }

                return response()->json([
                    'used_id' => $uuid,
                    'message' => 'User Updated',
                ]);
            }

            // =========================
            // CREATE USER
            // =========================
            $request->validate([
                'fullname' => 'required|string|max:255',
                'email' => 'required|email|max:255|unique:internal_users,email',
                'no_ic' => 'required|digits:12|unique:internal_users,no_ic',
                'phone_number' => 'required|unique:internal_users,phone_number',
                'position' => 'required|string|max:255',
                // 'office' => 'required|string|max:255',
                'role' => 'required|string',
            ]);

            $createData = [
                'uuid' => Str::uuid(),
                'fullname' => $request->fullname,
                'email' => $request->email,
                'no_ic' => $request->no_ic,
                'phone_number' => $request->phone_number,
                'position' => $request->position,
                'office' => $request->office,
                'branch' => $request->branch,
                'password' => Hash::make($request->no_ic),
            ];

            // Only admin/superadmin can set branch on creation
            $actorRole = $actor->getRoleNames()->first();
            if (in_array($actorRole, ['admin', 'superadmin'])) {
                $createData['branch'] = $request->branch;
            }

            $internalUser = InternalUser::create($createData);

            $internalUser->assignRole($request->role);

            if ($request->role === 'boundary officer') {
                BoundaryOfficer::create([
                    'user_id' => $internalUser->uuid,
                ]);

                activity()
                    ->useLog('user_activity')
                    ->event('created')
                    ->performedOn($internalUser)
                    ->causedBy($actor)
                    ->log("{$internalUser->fullname} is new user for boundary officer.");
            }

            // Notify creator
            $actor->notify(new InternalUserEditedNotification('User created', 'You created a new user: ' . $internalUser->fullname, $url));

            try {
                event(new InternalUserAdded('A new internal user has been added'));
            } catch (\Exception $e) {
                \Log::info('Broadcast failed: ' . $e->getMessage());
            }

            return response()->json([
                'used_id' => $internalUser->uuid,
                'message' => 'User Created',
            ]);
        });
    }

    public function user_list($type)
    {
        if (auth()->user()->hasRole('boundary officer')) {
            abort(403, 'Unauthorized action. Boundary Officers are restricted from this area.');
        }

        if ($type === 'public') {
            $users = PublicUser::select(['fullname', 'id', 'uuid'])->get();
        } else {
            $users = InternalUser::select(['fullname', 'id', 'uuid'])->get();
        }

        return response()->json([
            'users' => $users,
        ]);
    }

    public function profile()
    {
        // $user = authUser()['user'];

        // $roles = $user->getRoleNames(); // returns a collection of role names

        // $permissions = $user->getAllPermissions(); // returns a collection of Permission models

        $documents = DocumentRequirement::where('is_active', true)
            ->where('module', 'user')
            ->get();

        return view('pages.authentication.profile', [
            'title' => 'Profile',
            'states' => State::all(),
            'documents' => $documents,
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
        $user = $request['type'] === 'public' ? PublicUser::where('uuid', $request['uuid'])->first() : InternalUser::where('uuid', $request['uuid'])->first();

        if (!$user) {
            return response()->json(
                [
                    'message' => 'User not found.',
                ],
                404,
            );
        }

        // Verify old password before allowing change
        if (!Hash::check($validated['old_password'], $user->password)) {
            return response()->json(
                [
                    'message' => 'Old password is incorrect.',
                ],
                400,
            );
        }

        // Update password
        $user->password = Hash::make($validated['new_password']);
        $user->save();

        return response()->json([
            'message' => 'Password updated successfully.',
        ]);
    }


    public function uploadVerificationAttachment(Request $request, VerificationService $verificationService)
    {
        // dd('upload');
        $userId = $request->user_id ?? auth('public')->user()->uuid;

        $filesByDocId = $request->file('attachment', []);
        $documentTypes = $request->input('document_type', []);
        $validFrom = $request->input('valid_from', []);
        $validUntil = $request->input('valid_until', []);

        $result = $verificationService->uploadVerificationAttachment(
            $userId,
            $filesByDocId,
            $documentTypes,
            $validFrom,
            $validUntil
        );

        $user = PublicUser::where('uuid', $userId)->first();

        try {
            event(new InternalUserAdminEvent($user->fullname . ' is uploaded a verification attachement.'));

            event(new PublicUserEvent('You Upload a verification attachment', $user->uuid));
        } catch (\Exception $e) {
            \Log::info('Failed to broadcast event: ' . $e->getMessage());
        }

        $users = InternalUser::role(['admin', 'superadmin'])->get();
        $notificationUrl = route('internal.public.list');
        Notification::send($users, new ApplicationNotification('A user upload a verification attachment', 'A user upload a verification attachment', $user->fullname, $notificationUrl));

        $user->notify(new ApplicationNotification('You Upload a verification attachment', 'You Upload a verification attachment', 'QIS', '/profile'));

        activity()
            ->useLog('user_activity')
            ->event('verified')
            ->performedOn($user)
            ->causedBy(authUser()['user'] ?? $user)
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
        if (auth()->user()->hasRole('boundary officer')) {
            abort(403, 'Unauthorized action. Boundary Officers are restricted from this area.');
        }

        \Log::info("Fetching verification for user: {$id}");

        // Get the verification record with publicUser
        $verification = ApprovedPublic::with(['publicUser'])
            ->where('user_id', $id)
            ->first();

        if (!$verification) {
            \Log::warning("No verification record found for user_id: {$id}");
            return response()->json(['error' => 'Verification not found'], 404);
        }

        // Get the user's attachments from the PublicUser model
        $user = $verification->publicUser;
        $attachments = $user ? $user->attachments : collect();

        // ─── Build the grouped attachments ──────────────────────────────
        $attachmentsGrouped = $attachments
            ->groupBy('document_type')
            ->map(function ($group, $documentType) {
                return [
                    'document_type' => $documentType,
                    'attachments' => $group->map(function ($attachment) {
                        return [
                            'id'                  => $attachment->id,
                            'file_path'           => $attachment->file_path,
                            'file_type'           => $attachment->file_type,
                            'file_size'           => $attachment->file_size,
                            'original_file_name'  => $attachment->original_file_name,
                            'valid_from'          => $attachment->valid_from,
                            'valid_until'         => $attachment->valid_until,
                            'is_read'             => $attachment->is_read,
                            'rejected_reason'     => $attachment->rejected_reason,
                            'created_at'          => $attachment->created_at,
                        ];
                    }),
                ];
            })
            ->values(); // strip the keys (document_type is now a field)

        // ─── Build the response ──────────────────────────────────────────
        return response()->json([
            'user' => $user ? [
                'uuid'         => $user->uuid,
                'fullname'     => $user->fullname,
                'email'        => $user->email,
                'phone_number' => $user->phone_number,
                'account_type' => $user->account_type,
                'no_ic'        => $user->no_ic,
            ] : null,
            'verification' => [
                'id'           => $verification->id,
                'user_id'      => $verification->user_id,
                'doa_verified' => $verification->doa_verified,
                'status'       => $verification->status,
                'approved_by'  => $verification->approved_by,
                'reason'       => $verification->reason,
                'created_at'   => $verification->created_at,
                'updated_at'   => $verification->updated_at,
            ],
            'attachments_grouped' => $attachmentsGrouped,
            'attachments_count'   => $attachments->count(),
        ]);
    }

    private function updateUserVerificationStatus($userId)
    {
        $user = PublicUser::where('uuid', $userId)->firstOrFail();
        $verification = ApprovedPublic::where('user_id', $userId)->firstOrFail();

        // Get all required document types
        $requiredTypes = DocumentRequirement::where('module', 'user')
            ->where('is_required', true)
            ->where('is_active', true)
            ->pluck('name')
            ->toArray();

        // Get the user's attachments for those types
        $attachments = UserAttachment::where('user_id', $userId)
            ->whereIn('document_type', $requiredTypes)
            ->get();

        // Check if all required types are present, read, and not rejected
        $allOk = true;
        foreach ($requiredTypes as $type) {
            $att = $attachments->firstWhere('document_type', $type);
            if (!$att || !$att->is_read || $att->rejected_reason !== null) {
                $allOk = false;
                break;
            }
        }

        if ($allOk && $attachments->count() === count($requiredTypes)) {
            // All required attachments are read and accepted → verify the user
            $verification->doa_verified = 1;
            $verification->status = 'Verified and approved';
            $verification->approved_by = authUser()['user']->uuid ?? null;
            $verification->doa_approved_time = now();
            $verification->reason = null;
            $verification->save();

            $user->doa_verified = 1;
            $user->save();

            return true;
        }

        // If not all read, ensure the user is NOT verified
        if ($user->doa_verified == 1) {
            $verification->doa_verified = 0;
            $verification->status = 'Pending';
            $verification->save();
            $user->doa_verified = 0;
            $user->save();
        }

        return false;
    }

    /**
     * Bulk accept/reject all required attachments.
     */
    public function save_attachment($id, Request $request)
    {
        if (auth()->user()->hasRole('boundary officer')) {
            abort(403, 'Unauthorized action. Boundary Officers are restricted from this area.');
        }

        $internal = authUser()['user'];

        DB::beginTransaction();

        try {
            $user = PublicUser::where('uuid', $id)->firstOrFail();
            $verification = ApprovedPublic::where('user_id', $id)->firstOrFail();

            $requiredTypes = DocumentRequirement::where('module', 'user')
                ->where('is_required', true)
                ->where('is_active', true)
                ->pluck('name')
                ->toArray();

            if ($request->input('approved') === 'yes') {
                // Bulk accept: mark all required attachments as read
                UserAttachment::where('user_id', $user->uuid)
                    ->whereIn('document_type', $requiredTypes)
                    ->update([
                        'is_read' => true,
                        'rejected_reason' => null,
                    ]);

                // Re-evaluate verification status
                $this->updateUserVerificationStatus($user->uuid);

                $isApproved = 1;
                $message = 'All required attachments accepted and user verified.';
            } else {
                // Bulk reject: mark all required attachments as rejected
                $reason = $request->input('reason', 'Rejected by admin');

                UserAttachment::where('user_id', $user->uuid)
                    ->whereIn('document_type', $requiredTypes)
                    ->update([
                        'is_read' => true,
                        'rejected_reason' => $reason,
                    ]);

                // This will set doa_verified = 0 if any rejected
                $this->updateUserVerificationStatus($user->uuid);

                $isApproved = 0;
                $message = 'All required attachments rejected.';
            }

            $user->doa_verified = $isApproved;
            $user->save();

            DB::commit();

            // Notifications and events
            try {
                event(new InternalUserAdminEvent($isApproved ? $user->fullname . ' account is verified' : $user->fullname . ' account verification is rejected'));
                event(new PublicUserEvent($isApproved ? 'Your Account is verified by DOA' : 'Your Account is not verified by DOA', $user->uuid));
            } catch (\Exception $e) {
                Log::info('Failed to broadcast event: ' . $e->getMessage());
            }

            $users = InternalUser::role(['admin', 'superadmin'])->get();
            $notificationUrl = route('internal.public.list');
            Notification::send($users, new ApplicationNotification($isApproved ? $user->fullname . ' account is verified' : $user->fullname . ' account verification is rejected', $isApproved ? $user->fullname . ' account is verified' : $user->fullname . ' account verification is rejected', $user->fullname, $notificationUrl));

            activity()
                ->useLog('user_activity')
                ->event('verified')
                ->performedOn($user)
                ->causedBy(authUser()['user'])
                ->log($isApproved ? "{$user->fullname} was verified by " . authUser()['user']['fullname'] : "{$user->fullname}'s verification is rejected by " . authUser()['user']['fullname']);

            $user->notify(new ApplicationNotification($isApproved ? 'Your account is verified' : 'Your account verification is rejected', $isApproved ? 'Your account is verified' : 'Your account verification is rejected', 'QIS', '/profile'));
            if ($isApproved) {
                $user->notify(new ApplicationNotification('Start apply new application', 'Start apply new application', 'QIS', '/public/new_application'));
            }

            return response()->json([
                'success' => true,
                'message' => $message,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Verification update failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while saving verification status.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Accept a single attachment.
     */
    public function acceptAttachment($attachmentId)
    {
        if (auth()->user()->hasRole('boundary officer')) {
            abort(403, 'Unauthorized action. Boundary Officers are restricted from this area.');
        }

        $attachment = UserAttachment::findOrFail($attachmentId);
        $attachment->is_read = true;
        $attachment->rejected_reason = null;
        $attachment->save();

        // Re-evaluate the user's overall verification status
        $this->updateUserVerificationStatus($attachment->user_id);

        // Optional: notifications? maybe not needed for individual actions.

        return response()->json([
            'success' => true,
            'message' => 'Attachment accepted.',
        ]);
    }

    /**
     * Reject a single attachment.
     */
    public function rejectAttachment($attachmentId, Request $request)
    {
        if (auth()->user()->hasRole('boundary officer')) {
            abort(403, 'Unauthorized action. Boundary Officers are restricted from this area.');
        }

        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $attachment = UserAttachment::findOrFail($attachmentId);
        $attachment->is_read = true;
        $attachment->rejected_reason = $request->input('reason');
        $attachment->save();

        // Re-evaluate – user will not be verified because of rejection reason
        $this->updateUserVerificationStatus($attachment->user_id);

        return response()->json([
            'success' => true,
            'message' => 'Attachment rejected.',
        ]);
    }
}
