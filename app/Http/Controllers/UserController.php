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
    // ──────────────────────────────────────────────────────────────
    // Helper: Update user verification status based on all documents
    // ──────────────────────────────────────────────────────────────
    private function updateUserVerificationStatus($userId)
    {
        $user = PublicUser::find($userId);
        if (!$user) {
            return;
        }

        // Get or create ApprovedPublic record
        $verification = ApprovedPublic::firstOrNew(['user_id' => $user->uuid]);
        if (!$verification->exists) {
            $verification->user_id = $user->uuid;
            $verification->doa_verified = 0;
            $verification->status = 'Pending';
            $verification->save();
            $verification->refresh();
        }

        // Get all active required documents (keyed by name to avoid N+1 lookups)
        $requiredDocs = DocumentRequirement::where('module', 'user')
            ->where('is_required', true)
            ->where('is_active', true)
            ->get()
            ->keyBy('name');

        if ($requiredDocs->isEmpty()) {
            return;
        }

        // Always re-fetch fresh from DB — never trust a stale/cached collection here
        $validAttachments = UserAttachment::where('user_id', $user->uuid)
            ->where('is_read', 1)
            ->whereNull('rejected_reason')
            ->get()
            ->groupBy('document_type');

        $allVerified = true;
        foreach ($requiredDocs as $docName => $req) {
            $candidates = $validAttachments->get($docName, collect());

            $hasValid = $candidates->contains(function ($att) use ($req) {
                if ($req->requires_expiry && $att->valid_until) {
                    return now()->lessThanOrEqualTo($att->valid_until);
                }
                return true;
            });

            if (!$hasValid) {
                $allVerified = false;
                break;
            }
        }

        // ─── Update both records ─────────────────────────────────────────
        if ($allVerified) {
            $user->doa_verified = 1;
            $user->save();

            $verification->doa_verified = 1;
            $verification->status = 'Verified and approved';
            $verification->approved_by = authUser()['user']->uuid ?? $verification->approved_by;
            $verification->doa_approved_time = now();
            $verification->reason = null;
            $verification->save();
        } else {
            $user->doa_verified = 0;
            $user->save();

            $verification->doa_verified = 0;
            $verification->status = 'Pending';
            $verification->approved_by = null;
            $verification->doa_approved_time = null;
            $verification->reason = null;
            $verification->save();
        }
    }

    // ──────────────────────────────────────────────────────────────
    // Public User Listing & Data
    // ──────────────────────────────────────────────────────────────
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

        $countryNo = CountryNoPhone::get();
        return view('pages.internal.user_management.list_public', compact('countryNo'));
    }

    public function public_list_data(Request $request)
    {
        if (auth()->user()->hasRole('boundary officer')) {
            abort(403, 'Unauthorized action. Boundary Officers are restricted from this area.');
        }

        $users = PublicUser::query();

        // Filters
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
                $users->whereHas('approved', fn($q) => $q->where('doa_verified', true));
            } elseif ($request->account_verification == 'not_verified') {
                $users->whereDoesntHave('approved', fn($q) => $q->where('doa_verified', true));
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

                $html = '<div class="d-flex align-items-center gap-2">';
                if ($canRead) {
                    $html .= '<a href="' . route('internal.public.view', $user->uuid) . '" class="btn btn-sm btn-primary text-white" title="View"><i class="ti ti-eye"></i></a>';
                }
                if ($canUpdate) {
                    $html .= '<button class="btn btn-sm btn-secondary text-white editPublicUser-modal" data-id="' . $user->uuid . '" title="Edit"><i class="ti ti-pencil"></i></button>';
                }
                if ($canDelete && (!$currentUser || $currentUser->uuid !== $user->uuid)) {
                    $html .= '<button class="btn btn-sm btn-danger text-white deletePublicUser" data-id="' . $user->uuid . '" title="Delete"><i class="bx bx-trash-alt"></i></button>';
                }
                $html .= '</div>';
                return $html;
            })
            ->editColumn('created_at', fn($user) => $user->created_at->format('d-m-Y H:i'))
            ->editColumn('account_type', fn($user) => ucfirst($user->account_type))
            ->editColumn('doa_verified', function ($user) use ($canApprove) {
                $icon = '';
                if (!$user->approved?->doa_verified && !empty($user->approved?->verification_attachment)) {
                    $icon = '<span class="text-warning fs-16 fw-bold"><i class="bi bi-exclamation-circle"></i></span>';
                }
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
            ->rawColumns(['action', 'doa_verified'])
            ->make(true);
    }

    public function public_user_view($id)
    {
        if (auth()->user()->hasRole('boundary officer')) {
            abort(403, 'Unauthorized action. Boundary Officers are restricted from this area.');
        }
        $user = PublicUser::with(['attachments', 'vehicles', 'districtInfo', 'stateInfo'])->where('uuid', $id)->firstOrFail();
        $activities = $user->activities()->orderBy('created_at', 'desc')->get();

        return view('pages.internal.user_management.view_public', compact('user', 'activities'));
    }

    // ──────────────────────────────────────────────────────────────
    // Verification List & Data
    // ──────────────────────────────────────────────────────────────
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
        if (auth()->user()->hasRole('boundary officer')) {
            abort(403, 'Unauthorized action. Boundary Officers are restricted from this area.');
        }
        $users = PublicUser::whereHas('attachments', fn($q) => $q->where('is_read', false))
            ->with(['attachments' => fn($q) => $q->where('is_read', false)])
            ->get();
        return response()->json(['count' => $users->count()]);
    }

    public function verification_list_data(Request $request)
    {
        Gate::authorize('approve public user');

        $query = ApprovedPublic::with(['publicUser', 'publicUser.attachments'])
            ->where('doa_verified', '!=', 1)
            ->where('status', '!=', 'Verification is rejected')
            ->whereHas('publicUser.attachments');

        if ($request->filled('name')) {
            $query->whereHas('publicUser', fn($q) => $q->where('fullname', 'like', '%' . $request->input('name') . '%'));
        }
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->input('start_date'));
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->input('end_date'));
        }

        return DataTables::of($query)
            ->addColumn('fullname', fn($approved) => $approved->publicUser->fullname ?? '')
            ->addColumn('email', fn($approved) => $approved->publicUser->email ?? '')
            ->addColumn('status_badge', function ($approved) {
                $user = $approved->publicUser;
                if (!$user) return '<span class="badge bg-secondary">No user</span>';
                $latest = $user->attachments()->latest()->first();
                if (!$latest) return '<span class="badge bg-secondary">No files</span>';
                if ($latest->rejected_reason) return '<span class="badge bg-danger">Rejected</span>';
                if ($latest->is_read) {
                    if ($latest->valid_until && now()->greaterThan($latest->valid_until)) {
                        return '<span class="badge bg-warning text-dark">Expired</span>';
                    }
                    return '<span class="badge bg-success">Verified</span>';
                }
                return '<span class="badge bg-info">Pending Review</span>';
            })
            ->addColumn('documents', function ($approved) {
                return '<button class="btn btn-sm btn-primary view-documents-btn" data-id="' . $approved->user_id . '" data-en="View Documents" data-bm="Lihat Dokumen">View Documents</button>';
            })
            ->addColumn('action', function ($approved) {
                return '
                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-success accept-btn" data-id="' . $approved->user_id . '">Accept</button>
                    <button class="btn btn-sm btn-danger reject-btn" data-id="' . $approved->user_id . '">Reject</button>
                </div>';
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
        return response()->json(['user' => $public]);
    }

    // ──────────────────────────────────────────────────────────────
    // Public User CRUD
    // ──────────────────────────────────────────────────────────────
    public function public_user_save(Request $request)
    {
        $uuid = $request->input('uuid');

        if ($uuid) {
            // UPDATE
            $public = PublicUser::where('uuid', $uuid)->firstOrFail();
            $validated = $request->validate([
                'fullname'     => 'required|string|max:255',
                'email'        => 'required|email|unique:public_users,email,' . $public->id,
                'no_ic'        => 'required|unique:public_users,no_ic,' . $public->id,
                'account_type' => 'required|in:individu,company',
                'phone_number' => 'required|unique:public_users,phone_number,' . $public->id,
                'address_1'    => 'required',
                'postcode'     => 'required',
                'district'     => 'required',
                'state'        => 'required',
                'pic_name.*'   => 'nullable|string|max:255',
                'pic_position.*' => 'nullable|string|max:255',
                'pic_phone.*'  => 'nullable|string|max:20',
            ]);

            DB::beginTransaction();
            try {
                $personInCharge = null;
                if ($validated['account_type'] === 'company') {
                    $pics = [];
                    foreach ($request->input('pic_name', []) as $i => $name) {
                        $name = trim($name);
                        if (!empty($name) && isset($request->pic_position[$i]) && isset($request->pic_phone[$i])) {
                            $pics[] = [
                                'name'     => $name,
                                'position' => trim($request->pic_position[$i]),
                                'phone'    => trim($request->pic_phone[$i]),
                            ];
                        }
                    }
                    if (!empty($pics)) $personInCharge = $pics;
                }

                $public->update([
                    'fullname'         => $validated['fullname'],
                    'email'            => $validated['email'],
                    'password'         => $request->filled('password') ? Hash::make($request->password) : $public->password,
                    'no_ic'            => $validated['no_ic'],
                    'account_type'     => $validated['account_type'],
                    'phone_number'     => $validated['phone_number'],
                    'address_1'        => $validated['address_1'],
                    'address_2'        => $request->address_2,
                    'postcode'         => $validated['postcode'],
                    'district'         => $validated['district'],
                    'state'            => $validated['state'],
                    'office_number'    => $request->office_number,
                    'person_in_charge' => $personInCharge,
                ]);

                DB::commit();

                // Events & notifications
                try {
                    event(new \App\Events\PublicUserEvent('Your profile has been updated', $public->uuid));
                    event(new \App\Events\PublicUserUpdatedForInternal('A public user updated their profile', $public->uuid));
                } catch (\Exception $e) {
                    \Log::info('Broadcast failed: ' . $e->getMessage());
                }

                $users = InternalUser::all();
                Notification::send($users, new ApplicationNotification($public->fullname . ' account has been updated.', 'Akaun ' . $public->fullname . ' telah dikemas kini.', authUser()['user']->fullname, route('internal.public.list')));
                Notification::send($public, new ApplicationNotification('You updated your account.', 'Anda telah mengemas kini akaun anda.', 'QIS', '/profile'));

                return response()->json(['message' => 'Public User Updated', 'user' => $public]);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['message' => 'Update failed. Please try again.', 'error' => $e->getMessage()], 500);
            }
        } else {
            // CREATE
            $validated = $request->validate([
                'fullname'     => 'required|string|max:255',
                'email'        => 'required|email|unique:public_users,email',
                'no_ic'        => 'required|unique:public_users,no_ic',
                'account_type' => 'required|in:individu,company',
                'phone_number' => 'required|unique:public_users,phone_number',
                'address_1'    => 'required',
                'postcode'     => 'required',
                'district'     => 'required',
                'state'        => 'required',
                'pic_name.*'   => 'nullable|string|max:255',
                'pic_position.*' => 'nullable|string|max:255',
                'pic_phone.*'  => 'nullable|string|max:20',
            ]);

            DB::beginTransaction();
            try {
                $personInCharge = null;
                if ($validated['account_type'] === 'company') {
                    $pics = [];
                    foreach ($request->input('pic_name', []) as $i => $name) {
                        $name = trim($name);
                        if (!empty($name) && isset($request->pic_position[$i]) && isset($request->pic_phone[$i])) {
                            $pics[] = [
                                'name'     => $name,
                                'position' => trim($request->pic_position[$i]),
                                'phone'    => trim($request->pic_phone[$i]),
                            ];
                        }
                    }
                    if (!empty($pics)) $personInCharge = $pics;
                }

                $user = PublicUser::create([
                    'fullname'         => $validated['fullname'],
                    'email'            => $validated['email'],
                    'password'         => '',
                    'doa_verified'     => 1,
                    'no_ic'            => $validated['no_ic'],
                    'account_type'     => $validated['account_type'],
                    'phone_number'     => $validated['phone_number'],
                    'address_1'        => $validated['address_1'],
                    'address_2'        => $request->address_2,
                    'postcode'         => $validated['postcode'],
                    'district'         => $validated['district'],
                    'state'            => $validated['state'],
                    'person_in_charge' => $personInCharge,
                ]);

                \App\Models\ApprovedPublic::where('user_id', $user->uuid)->update([
                    'doa_verified' => 1,
                    'status' => 'Verified and approved',
                    'approved_by' => authUser()['user']->uuid ?? null,
                    'doa_approved_time' => now(),
                ]);

                DB::commit();

                try {
                    event(new \App\Events\PublicUserUpdatedForInternal('A public user created an account', $user->uuid));
                } catch (\Exception $e) {
                    \Log::info('Broadcast failed: ' . $e->getMessage());
                }

                $users = InternalUser::all();
                Notification::send($users, new ApplicationNotification($user->fullname . ' account has been created.', 'Akaun ' . $user->fullname . ' telah dicipta.', authUser()['user']->fullname, route('internal.public.list')));
                Notification::send($user, new ApplicationNotification('You created an account.', 'Anda telah mencipta akaun.', 'QIS', '#'));
                Notification::send($user, new ApplicationNotification('Upload your verification ID.', 'Sila muat naik ID pengesahan anda.', 'QIS', '/profile'));

                return response()->json(['message' => 'Public User Created', 'user' => $user]);
            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json(['message' => 'Registration failed. Please try again.', 'error' => $e->getMessage()], 500);
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
        return response()->json(['user' => $public]);
    }

    // ──────────────────────────────────────────────────────────────
    // Internal User CRUD
    // ──────────────────────────────────────────────────────────────
    public function internal_list()
    {
        Gate::authorize('read internal user');
        $actor = authUser()['user'];
        $actorRole = $actor->getRoleNames()->first();
        $isAdminOrSuperadmin = in_array($actorRole, ['admin', 'superadmin']);
        $branches = Branch::orderBy('name')->get();
        return view('pages.internal.user_management.list_internal', compact('isAdminOrSuperadmin', 'branches'));
    }

    public function internal_list_data(Request $request)
    {
        $query = InternalUser::select(['uuid', 'fullname', 'email', 'phone_number', 'position', 'office', 'branch'])->with('roles');
        if ($request->has('role') && $request->role != '') {
            $roles = explode(',', $request->role);
            $query->whereHas('roles', fn($q) => $q->whereIn('name', $roles));
        }
        if ($request->has('branch') && $request->branch != '') {
            $branches = explode(',', $request->branch);
            $query->whereIn('branch', $branches);
        }

        $currentUser = Auth::guard('internal')->user();

        return DataTables::of($query)
            ->addColumn('role', fn($user) => $user->getRoleNames()->implode(', ') ?: 'N/A')
            ->addColumn('action', function ($user) use ($currentUser) {
                $canRead = Gate::allows('read internal user');
                $canUpdate = Gate::allows('update internal user');
                $canDelete = Gate::allows('delete internal user');

                $html = '';
                if ($canRead) {
                    $html .= '<a href="' . route('internal.internal.view', $user->uuid) . '" class="btn btn-sm btn-primary text-white" title="View"><i class="ti ti-eye"></i></a>';
                }
                if ($canUpdate) {
                    $html .= '<button class="btn btn-sm btn-secondary editInternalUser-modal" data-id="' . $user->uuid . '" title="Edit"><i class="ti ti-edit"></i></button>';
                }
                if ($canDelete && (!$currentUser || $currentUser->uuid !== $user->uuid)) {
                    $html .= '<button class="btn btn-sm btn-danger text-white deleteBtn" data-id="' . $user->uuid . '" title="Delete"><i class="bx bx-trash-alt"></i></button>';
                }
                return $html;
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
            'email'    => 'required|email|unique:internal_users,email,' . $user->id,
            'phone'    => 'required|string|unique:internal_users,phone,' . $user->id,
            'position' => 'nullable|string|max:255',
            'office'   => 'nullable|string|max:255',
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
        return response()->json(['user' => $internal]);
    }

    public function internal_user_save(Request $request)
    {
        if (auth()->user()->hasRole('boundary officer')) {
            abort(403, 'Unauthorized action. Boundary Officers are restricted from this area.');
        }
        $actor = authUser()['user'];
        $url = route('internal.internal.list');

        return DB::transaction(function () use ($request, $actor, $url) {
            $uuid = $request->input('uuid');

            if ($uuid) {
                // UPDATE
                $internalUser = InternalUser::where('uuid', $uuid)->firstOrFail();
                $request->validate([
                    'fullname'     => 'required|string|max:255',
                    'email'        => 'required|email|max:255|unique:internal_users,email,' . $internalUser->id,
                    'no_ic'        => 'required|digits:12|unique:internal_users,no_ic,' . $internalUser->id,
                    'phone_number' => 'required|unique:internal_users,phone_number,' . $internalUser->id,
                    'position'     => 'required|string|max:255',
                    'role'         => 'required|string',
                ]);

                $updateData = [
                    'fullname'     => $request->fullname,
                    'email'        => $request->email,
                    'no_ic'        => $request->no_ic,
                    'phone_number' => $request->phone_number,
                    'position'     => $request->position,
                    'office'       => $request->office,
                ];
                $actorRole = $actor->getRoleNames()->first();
                if (in_array($actorRole, ['admin', 'superadmin'])) {
                    $updateData['branch'] = $request->branch;
                }
                $internalUser->update($updateData);
                $internalUser->syncRoles([$request->role]);

                if ($internalUser->uuid !== $actor->uuid) {
                    $internalUser->notify(new InternalUserEditedNotification('Your account was updated', 'Your account details were updated by ' . $actor->fullname, $url));
                }
                $actor->notify(new InternalUserEditedNotification('Account updated', 'You updated ' . $internalUser->fullname . '\'s account', $url));

                try {
                    event(new InternalUserEdited($internalUser->fullname . ' account was edited by ' . $actor->fullname, $internalUser->uuid));
                } catch (\Exception $e) {
                    \Log::info('Broadcast failed: ' . $e->getMessage());
                }

                return response()->json(['used_id' => $uuid, 'message' => 'User Updated']);
            }

            // CREATE
            $request->validate([
                'fullname'     => 'required|string|max:255',
                'email'        => 'required|email|max:255|unique:internal_users,email',
                'no_ic'        => 'required|digits:12|unique:internal_users,no_ic',
                'phone_number' => 'required|unique:internal_users,phone_number',
                'position'     => 'required|string|max:255',
                'role'         => 'required|string',
            ]);

            $createData = [
                'uuid'         => Str::uuid(),
                'fullname'     => $request->fullname,
                'email'        => $request->email,
                'no_ic'        => $request->no_ic,
                'phone_number' => $request->phone_number,
                'position'     => $request->position,
                'office'       => $request->office,
                'branch'       => $request->branch,
                'password'     => '',
            ];
            $actorRole = $actor->getRoleNames()->first();
            if (in_array($actorRole, ['admin', 'superadmin'])) {
                $createData['branch'] = $request->branch;
            }

            $internalUser = InternalUser::create($createData);
            $internalUser->assignRole($request->role);

            if ($request->role === 'boundary officer') {
                BoundaryOfficer::create(['user_id' => $internalUser->uuid]);
                activity()->useLog('user_activity')->event('created')->performedOn($internalUser)->causedBy($actor)->log("{$internalUser->fullname} is new user for boundary officer.");
            }

            $actor->notify(new InternalUserEditedNotification('User created', 'You created a new user: ' . $internalUser->fullname, $url));

            try {
                event(new InternalUserAdded('A new internal user has been added'));
            } catch (\Exception $e) {
                \Log::info('Broadcast failed: ' . $e->getMessage());
            }

            return response()->json(['used_id' => $internalUser->uuid, 'message' => 'User Created']);
        });
    }

    public function internal_user_delete($id)
    {
        if (auth()->user()->hasRole('boundary officer')) {
            abort(403, 'Unauthorized action. Boundary Officers are restricted from this area.');
        }
        $user = InternalUser::where('uuid', $id)->first();
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        $userName = $user->fullname;
        $actorName = authUser()['user']->fullname;
        $user->delete();
        try {
            event(new InternalUserDeleted($userName . ' account has been deleted by ' . $actorName));
        } catch (\Exception $e) {
            \Log::warning('Broadcast failed: ' . $e->getMessage());
        }
        return response()->json(['message' => 'User deleted successfully']);
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
        return response()->json(['users' => $users]);
    }

    // ──────────────────────────────────────────────────────────────
    // Profile & Password
    // ──────────────────────────────────────────────────────────────
    public function profile()
    {
        $documents = DocumentRequirement::where('is_active', true)->where('module', 'user')->get();
        return view('pages.authentication.profile', [
            'title' => 'Profile',
            'states' => State::all(),
            'documents' => $documents,
        ]);
    }

    public function userData()
    {
        return response()->json(authUser());
    }

    public function updateData(Request $request)
    {
        if ($request->type == 'public') {
            return $this->public_user_save($request);
        } else {
            return $this->internal_user_save($request);
        }
    }

    public function password(Request $request)
    {
        $validated = $request->validate([
            'old_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $user = $request['type'] === 'public'
            ? PublicUser::where('uuid', $request['uuid'])->first()
            : InternalUser::where('uuid', $request['uuid'])->first();

        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }
        if (!Hash::check($validated['old_password'], $user->password)) {
            return response()->json(['message' => 'Old password is incorrect.'], 400);
        }
        $user->password = Hash::make($validated['new_password']);
        $user->save();
        return response()->json(['message' => 'Password updated successfully.']);
    }

    // ──────────────────────────────────────────────────────────────
    // Verification Attachments
    // ──────────────────────────────────────────────────────────────
    public function uploadVerificationAttachment(Request $request, VerificationService $verificationService)
    {
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
            \Log::info('Broadcast failed: ' . $e->getMessage());
        }

        $users = InternalUser::role(['admin', 'superadmin'])->get();
        $notificationUrl = route('internal.public.list');
        Notification::send($users, new ApplicationNotification('A user upload a verification attachment', 'A user upload a verification attachment', $user->fullname, $notificationUrl));
        $user->notify(new ApplicationNotification('You Upload a verification attachment', 'You Upload a verification attachment', 'QIS', '/profile'));

        activity()->useLog('user_activity')->event('verified')->performedOn($user)->causedBy(authUser()['user'] ?? $user)->log("{$user->fullname} is uploading an attachment to get verification.");

        return response()->json($result, $result['success'] ? 200 : 500);
    }

    public function verification_attachment($id)
    {
        if (auth()->user()->hasRole('boundary officer')) {
            abort(403, 'Unauthorized action. Boundary Officers are restricted from this area.');
        }

        \Log::info("Fetching verification for user: {$id}");

        $verification = ApprovedPublic::with(['publicUser'])->where('user_id', $id)->first();
        if (!$verification) {
            \Log::warning("No verification record found for user_id: {$id}");
            return response()->json(['error' => 'Verification not found'], 404);
        }

        $user = $verification->publicUser;
        $attachments = $user ? $user->attachments : collect();

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
            ->values();

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

    // ──────────────────────────────────────────────────────────────
    // Bulk Accept / Reject
    // ──────────────────────────────────────────────────────────────
    public function save_attachment($id, Request $request)
    {
        if (auth()->user()->hasRole('boundary officer')) {
            abort(403, 'Unauthorized action. Boundary Officers are restricted from this area.');
        }

        $user = PublicUser::where('uuid', $id)->firstOrFail();

        $requiredTypes = DocumentRequirement::where('module', 'user')
            ->where('is_required', true)
            ->where('is_active', true)
            ->pluck('name')
            ->toArray();

        DB::beginTransaction();
        try {
            if ($request->input('approved') === 'yes') {
                UserAttachment::where('user_id', $user->uuid)
                    ->whereIn('document_type', $requiredTypes)
                    ->update([
                        'is_read' => true,
                        'rejected_reason' => null,
                    ]);
            } else {
                $reason = $request->input('reason', 'Rejected by admin');
                UserAttachment::where('user_id', $user->uuid)
                    ->whereIn('document_type', $requiredTypes)
                    ->update([
                        'is_read' => true,
                        'rejected_reason' => $reason,
                    ]);
            }

            // Re‑evaluate verification status
            $this->updateUserVerificationStatus($user->uuid);

            DB::commit();

            // ─── Notifications (unchanged) ──────────────────────────────
            $isApproved = $request->input('approved') === 'yes';
            try {
                event(new InternalUserAdminEvent($isApproved ? $user->fullname . ' account is verified' : $user->fullname . ' account verification is rejected'));
                event(new PublicUserEvent($isApproved ? 'Your Account is verified by DOA' : 'Your Account is not verified by DOA', $user->uuid));
            } catch (\Exception $e) {
                Log::info('Broadcast failed: ' . $e->getMessage());
            }

            $users = InternalUser::role(['admin', 'superadmin'])->get();
            $notificationUrl = route('internal.public.list');
            Notification::send($users, new ApplicationNotification($isApproved ? $user->fullname . ' account is verified' : $user->fullname . ' account verification is rejected', $isApproved ? $user->fullname . ' account is verified' : $user->fullname . ' account verification is rejected', $user->fullname, $notificationUrl));

            activity()->useLog('user_activity')->event('verified')->performedOn($user)->causedBy(authUser()['user'])->log($isApproved ? "{$user->fullname} was verified by " . authUser()['user']['fullname'] : "{$user->fullname}'s verification is rejected by " . authUser()['user']['fullname']);

            $user->notify(new ApplicationNotification($isApproved ? 'Your account is verified' : 'Your account verification is rejected', $isApproved ? 'Your account is verified' : 'Your account verification is rejected', 'QIS', '/profile'));
            if ($isApproved) {
                $user->notify(new ApplicationNotification('Start apply new application', 'Start apply new application', 'QIS', '/public/new_application'));
            }

            return response()->json([
                'success' => true,
                'message' => $isApproved ? 'All required attachments accepted and user verified.' : 'All required attachments rejected.',
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

    // ──────────────────────────────────────────────────────────────
    // Single Attachment Accept / Reject
    // ──────────────────────────────────────────────────────────────
    public function acceptAttachment($attachmentId)
    {
        if (auth()->user()->hasRole('boundary officer')) {
            abort(403, 'Unauthorized action. Boundary Officers are restricted from this area.');
        }

        $attachment = UserAttachment::findOrFail($attachmentId);
        $attachment->is_read = true;
        $attachment->rejected_reason = null;
        $attachment->save();

        $this->updateUserVerificationStatus($attachment->user_id);

        return response()->json([
            'success' => true,
            'message' => 'Attachment accepted.',
        ]);
    }

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

        $this->updateUserVerificationStatus($attachment->user_id);

        return response()->json([
            'success' => true,
            'message' => 'Attachment rejected.',
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    // Location Helpers
    // ──────────────────────────────────────────────────────────────
    public function getStates()
    {
        return response()->json(State::all());
    }

    public function getDistricts($state_id)
    {
        return response()->json(District::where('state_id', $state_id)->get());
    }

    public function getPostcodes($district_id)
    {
        return response()->json(Postcode::where('district_id', $district_id)->get());
    }
}
