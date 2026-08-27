<?php

namespace App\Http\Controllers;

use App\Events\InternalUserAdminEvent;
use App\Events\PublicUserEvent;
use App\Models\CountryNoPhone;
use App\Models\DocumentRequirement;
use App\Models\InternalUser;
use App\Models\PublicUser;
use App\Notifications\ApplicationNotification;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Spatie\Activitylog\Models\Activity;
use App\Services\VerificationService;
use Illuminate\Support\Facades\Notification;

class AuthenticationController extends Controller
{
    //
    public function login()
    {
        return view('pages.authentication.login', [
            'title' => 'Login',
        ]);
    }
    public function login2()
    {
        return view('pages.authentication.login2', [
            'title' => 'Login',
        ]);
    }

    public function loginAction(Request $request)
    {
        // Get language from request (default 'en')
        $lang = $request->input('lang', 'en');
        app()->setLocale($lang);

        $credentials = $request->validate([
            'userType' => 'required|in:public,internal',
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $guard = $credentials['userType'];

        // Retrieve user first
        $user = ($guard === 'public' ? PublicUser::class : InternalUser::class)::where('email', $credentials['email'])->first();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => __('auth.user_not_found'), // translated
            ], 422);
        }

        // Attempt login first
        if (
            Auth::guard($guard)->attempt([
                'email' => $credentials['email'],
                'password' => $credentials['password'],
            ])
        ) {
            if ($request->hasSession()) {
                $request->session()->regenerate();
            }

            // After login, check if email not verified
            if (method_exists($user, 'hasVerifiedEmail') && !$user->hasVerifiedEmail()) {
                // Send verification email
                $user->notify(new VerifyEmailNotification());
                return response()->json([
                    'status' => 'unverified',
                    'message' => __('auth.email_unverified'), // translated
                    'redirect' => route('verify.email'),
                ]);
            }

            // Normal redirect if verified
            $redirect = $guard === 'public' ? route('public.dashboard') : route('internal.dashboard');
            return response()->json([
                'status' => 'success',
                'message' => __('auth.login_success'), // translated
                'redirect' => $redirect,
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => __('auth.failed'), // translated
        ], 422);
    }

    public function loginActionApi(Request $request)
    {
        // Get language from request (default 'en')
        $lang = $request->input('lang', 'en');
        app()->setLocale($lang);


        $credentials = $request->validate([
            'userType' => 'required|in:public,internal',
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $guard = $credentials['userType'];

        // Retrieve user first
        $user = ($guard === 'public' ? PublicUser::class : InternalUser::class)::where('email', $credentials['email'])->first();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => __('auth.user_not_found'), // translated
            ], 422);
        }

        // API login is stateless: verify credentials without creating a session.
        if (!Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => __('auth.invalid_credentials'), // translated
            ], 422);
        }

        // After login, check if email not verified
        if (method_exists($user, 'hasVerifiedEmail') && !$user->hasVerifiedEmail()) {
            $user->notify(new VerifyEmailNotification());
            return response()->json([
                'status' => 'unverified',
                'message' => __('auth.email_unverified'), // translated
                'redirect' => route('verify.email'),
            ]);
        }

        // Return user payload expected by mobile app.
        return response()->json([
            'status' => 'success',
            'message' => __('auth.login_success'), // translated
            'user' => [
                'uuid' => $user->uuid,
                'name' => $user->name ?? $user->fullname,
                'email' => $user->email,
                'userType' => $guard,
            ],
        ]);
    }

    // public function register()
    // {
    //     return view('pages.authentication.register', [
    //         'title' => 'Register'
    //     ]);
    // }

    public function registerPublic(Request $request, VerificationService $verificationService)
    {
        $countryCode = $request->phoneNumber ?? '+60';
        $countryCode = preg_replace('/[^0-9+]/', '', $countryCode);
        if (!str_starts_with($countryCode, '+')) {
            $countryCode = '+' . $countryCode;
        }

        $number = preg_replace('/\D/', '', $request->phone_number);
        $number = ltrim($number, '0');
        if (str_starts_with($number, '60')) {
            $number = substr($number, 2);
        }
        $fullPhoneNumber = $countryCode . $number;

        $request->merge(['phone_number' => $fullPhoneNumber]);

        // ─── Validation ────────────────────────────────────────────────
        $validated = $request->validate([
            'fullname'       => 'required|string|max:255',
            'email'          => 'required|email|unique:public_users,email',
            'password'       => [
                'required',
                'min:8',
                'confirmed',
                'regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
                'regex:/[@$!%*#?&]/',
            ],
            'no_ic'          => 'required|unique:public_users,no_ic',
            'account_type'   => 'required|in:individu,company',
            'phone_number'   => 'required|unique:public_users,phone_number',
            'address_1'      => 'required',
            'postcode'       => 'required',
            'district'       => 'required',
            'state'          => 'required',
            // PIC fields (optional)
            'pic_name.*'     => 'nullable|string|max:255',
            'pic_position.*' => 'nullable|string|max:255',
            'pic_phone.*'    => 'nullable|string|max:20',
        ]);

        try {
            DB::beginTransaction();

            // ─── Create user ─────────────────────────────────────────────
            $user = PublicUser::create([
                'fullname'    => $validated['fullname'],
                'email'       => $validated['email'],
                'password'    => Hash::make($validated['password']),
                'no_ic'       => $validated['no_ic'],
                'account_type' => $validated['account_type'],
                'phone_number' => $validated['phone_number'],
                'address_1'   => $validated['address_1'],
                'address_2'   => $request->address_2,
                'postcode'    => $validated['postcode'],
                'district'    => $validated['district'],
                'state'       => $validated['state'],
            ]);

            // ─── Person In Charge (only for company) ─────────────────────
            if ($validated['account_type'] === 'company') {
                $picNames     = $request->input('pic_name', []);
                $picPositions = $request->input('pic_position', []);
                $picPhones    = $request->input('pic_phone', []);
                $personInCharge = [];

                foreach ($picNames as $index => $name) {
                    $name = trim($name);
                    if (!empty($name) && isset($picPositions[$index]) && isset($picPhones[$index])) {
                        $personInCharge[] = [
                            'name'     => $name,
                            'position' => trim($picPositions[$index]),
                            'phone'    => trim($picPhones[$index]),
                        ];
                    }
                }

                if (!empty($personInCharge)) {
                    $user->person_in_charge = $personInCharge;
                    $user->save();
                }
            }

            // ─── Handle attachments ───────────────────────────────────────
            $attachmentFiles = $request->file('attachment'); // [docId => [UploadedFile, ...]]
            $result = null;

            if (!empty($attachmentFiles)) {
                $documentTypes = $request->input('document_type', []);
                $validFrom     = $request->input('valid_from', []);
                $validUntil    = $request->input('valid_until', []);

                $result = $verificationService->uploadVerificationAttachment(
                    $user->uuid,
                    $attachmentFiles,
                    $documentTypes,
                    $validFrom,
                    $validUntil
                );

                if (!$result || $result['success'] !== true) {
                    DB::rollBack();
                    return response()->json([
                        'message' => 'Upload failed: ' . ($result['message'] ?? 'Unknown error'),
                    ], 500);
                }
            }

            // ─── Log in ────────────────────────────────────────────────────
            Auth::guard('public')->login($user);

            // ─── Notifications and events (unchanged) ────────────────────
            $user->notify(new VerifyEmailNotification());

            try {
                event(new InternalUserAdminEvent('A new account is created'));
            } catch (\Exception $e) {
                Log::warning('Pusher connection failed: ' . $e->getMessage());
            }

            $admins = InternalUser::role(['admin', 'superadmin'])->get();
            $notificationUrl = route('internal.public.list');
            Notification::send($admins, new ApplicationNotification('A new account is created', 'A new account is created', $user->fullname, $notificationUrl));
            $user->notify(new ApplicationNotification('You created an account', 'You created an account', 'QIS', '/profile'));

            if (!empty($result) && $result['success'] === true) {
                try {
                    event(new InternalUserAdminEvent($user->fullname . ' uploaded a verification attachment.'));
                    event(new PublicUserEvent('You uploaded a verification attachment', $user->uuid));
                } catch (\Exception $e) {
                    Log::warning('Pusher event failed: ' . $e->getMessage());
                }

                $adminUsers = InternalUser::role(['admin'])->get();
                Notification::send($adminUsers, new ApplicationNotification(
                    'A user uploaded a verification attachment',
                    'A user uploaded a verification attachment',
                    $user->fullname,
                    '/internal/user_public/verification'
                ));
                $user->notify(new ApplicationNotification('You uploaded a verification attachment', 'You uploaded a verification attachment', 'QIS', '/profile'));
            } else {
                $user->notify(new ApplicationNotification(
                    'Upload a verification attachment to get verified by DOA.',
                    'Upload a verification attachment to get verified by DOA.',
                    'QIS',
                    '/profile'
                ));
            }

            DB::commit();

            return response()->json([
                'message'  => 'Registration successful! Please verify your email to continue.',
                'redirect' => route('verify.email'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Registration failed: ' . $e->getMessage());
            return response()->json([
                'message' => 'Registration failed. Please try again.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }


    public function logout(Request $request)
    {
        $user = null;
        $guard = null;

        if (Auth::guard('public')->check()) {
            $user = Auth::guard('public')->user();
            $guard = 'public';
            Auth::guard('public')->logout();
        } elseif (Auth::guard('internal')->check()) {
            $user = Auth::guard('internal')->user();
            $guard = 'internal';
            Auth::guard('internal')->logout();
        }

        if ($user) {
            $username = $user->name ?? ($user->fullname ?? 'Unknown user');

            activity()
                ->tap(function (Activity $activity) {
                    $activity->log_name = 'user_activity';
                })
                ->event('logout')
                ->causedBy($user)
                ->performedOn($user)
                ->withProperties([
                    'ip' => $request->ip(),
                    'guard' => $guard,
                ])
                ->log("{$username} logged out from the system ({$guard} user ).");
        }

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

    // verify email
    public function verify_email()
    {
        $user = auth('public')->user() ?? auth('internal')->user();
        return view('pages.authentication.verify_email', [
            'title' => 'Verify Email',
            'email' => $user->email,
        ]);
    }

    public function verify_link(Request $request)
    {
        $guard = $request->query('guard', 'public'); // default to public if not set
        $id = $request->route('id');
        $hash = $request->route('hash');

        // Determine which model to use
        $user = $guard === 'internal' ? InternalUser::findOrFail($id) : PublicUser::findOrFail($id);

        // Validate hash
        if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            abort(403, 'Invalid verification link.');
        }

        // Check if already verified
        if ($user->hasVerifiedEmail()) {
            return redirect()->route('login')->with('message', 'Email already verified.');
        }

        // Mark as verified
        $user->markEmailAsVerified();
        event(new Verified($user));

        auth()->guard($guard)->login($user);

        return redirect()
            ->route($guard === 'public' ? 'public.dashboard' : 'internal.dashboard')
            ->with('message', 'Your email has been successfully verified!');
    }

    public function resend_verify_link(Request $request)
    {
        $user = auth('public')->user() ?? auth('internal')->user();

        if (!$user) {
            return back()->withErrors(['message' => 'No authenticated user found.']);
        }

        $user->notify(new VerifyEmailNotification());

        return back()->with('message', 'Verification link sent!');
    }

    public function register()
    {
        $countryNo = CountryNoPhone::get();
        $documents =  DocumentRequirement::where('is_active', 1)
            ->where('module', 'user')
            ->orderBy('id')
            ->get();

        // dd($countryNo);
        return view('pages.authentication.register_test', [
            'title' => 'Register',
            'countryNo' => $countryNo,
            'documents' => $documents
        ]);
    }
}
