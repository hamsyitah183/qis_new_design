<?php

namespace App\Http\Controllers;

use App\Models\PublicUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

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

        if (Auth::guard($guard)->attempt([
            'email' => $credentials['email'],
            'password' => $credentials['password'],
        ])) {
            $request->session()->regenerate();

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

        $user = PublicUser::create([
            'fullname' => $validated['fullname'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'no_ic' => $validated['no_ic'],
            'account_type' => $validated['account_type'],
            'phone_number' => $validated['phone_number'],
            'address_1' => $validated['address_1'],
            'address_2' => $request->address_2,
            'postcode' => $validated['postcode'],
            'district' => $validated['district'],
            'state' => $validated['state'],
        ]);

        // Send email verification
        $user->notify(new VerifyEmailNotification());

        return response()->json([
            'message' => 'Registration successful! Please check your email to verify your account.',
        ]);
    }

    public function logout(Request $request)
    {
        if (Auth::guard('public')->check()) {
            Auth::guard('public')->logout();
        } elseif (Auth::guard('internal')->check()) {
            Auth::guard('internal')->logout();
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
}
