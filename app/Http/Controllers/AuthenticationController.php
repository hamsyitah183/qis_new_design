<?php

namespace App\Http\Controllers;

use App\Models\InternalUser;
use App\Models\PublicUser;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Spatie\Activitylog\Models\Activity;

class AuthenticationController extends Controller
{
    //
    public function login()
    {
        return view('pages.authentication.login', [
            'title' => 'Login'
        ]);
    }

    public function loginAction(Request $request)
    {
        $credentials = $request->validate([
            'userType' => 'required|in:public,internal',
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $guard = $credentials['userType'];

        // Retrieve user first
        $user = ($guard === 'public' ? PublicUser::class : InternalUser::class)
            ::where('email', $credentials['email'])
            ->first();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found.',
            ], 422);
        }

        // Attempt login first
        if (Auth::guard($guard)->attempt([
            'email' => $credentials['email'],
            'password' => $credentials['password'],
        ])) {
            $request->session()->regenerate();

            // After login, check if email not verified
            if (method_exists($user, 'hasVerifiedEmail') && !$user->hasVerifiedEmail()) {
                // Send verification email
                $user->notify(new VerifyEmailNotification());

                return response()->json([
                    'status' => 'unverified',
                    'message' => 'Your email is not verified. A verification email has been sent.',
                    'redirect' => route('verify.email'),
                ]);
            }

            // Normal redirect if verified
            $redirect = $guard === 'public'
                ? route('public.dashboard')
                : route('internal.dashboard');

            return response()->json([
                'status' => 'success',
                'message' => 'Login successful!',
                'redirect' => $redirect,
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Invalid credentials or user type.',
        ], 422);
    }



    public function register()
    {
        return view('pages.authentication.register', [
            'title' => 'Register'
        ]);
    }

    public function registerPublic(Request $request)
    {
        $validated = $request->validate([
            'fullname' => 'required|string|max:255',
            'email' => 'required|email|unique:public_users,email',
            'password' => 'required|min:8',
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
                'fullname'     => $validated['fullname'],
                'email'        => $validated['email'],
                'password'     => Hash::make($validated['password']),
                'no_ic'        => $validated['no_ic'],
                'account_type' => $validated['account_type'],
                'phone_number' => $validated['phone_number'],
                'address_1'    => $validated['address_1'],
                'address_2'    => $request->address_2,
                'postcode'     => $validated['postcode'],
                'district'     => $validated['district'],
                'state'        => $validated['state'],
            ]);

            // Automatically log in the user
            Auth::guard('public')->login($user);

            //  Send verification email
            $user->notify(new VerifyEmailNotification());

            DB::commit();

            return response()->json([
                'message' => 'Registration successful! Please verify your email to continue.',
                'redirect' => route('verify.email'), // user is already logged in
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

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
            $username = $user->name ?? $user->fullname ?? 'Unknown user';

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
                ->log("{$username} logged out from the system ({$guard} guard).");
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
            'email' => $user->email
        ]);
    }

    public function verify_link(Request $request)
    {
        $guard = $request->query('guard', 'public'); // default to public if not set
        $id = $request->route('id');
        $hash = $request->route('hash');

        // Determine which model to use
        $user = $guard === 'internal'
            ? InternalUser::findOrFail($id)
            : PublicUser::findOrFail($id);

        // Validate hash
        if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            abort(403, 'Invalid verification link.');
        }

        // Check if already verified
        if ($user->hasVerifiedEmail()) {
            return redirect()->route('login.form')->with('message', 'Email already verified.');
        }

        // Mark as verified
        $user->markEmailAsVerified();
        event(new Verified($user));

        auth()->guard($guard)->login($user);

        return redirect()->route(
            $guard === 'public' ? 'public.dashboard' : 'internal.dashboard'
        )->with('message', 'Your email has been successfully verified!');
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
}
