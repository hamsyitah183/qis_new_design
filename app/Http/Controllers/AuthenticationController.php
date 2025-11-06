<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticationController extends Controller
{
    //
    public function login()
    {
        return view('pages.authentication.login');
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
        return view('pages.authentication.register');
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
}
