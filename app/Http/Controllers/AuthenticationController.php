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
        // ✅ Validate form input
        $credentials = $request->validate([
            'userType' => 'required|in:public,internal',
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        // ✅ Determine which guard to use based on userType
        $guard = $credentials['userType'];

        // ✅ Attempt login using the selected guard
        if (Auth::guard($guard)->attempt([
            'email'    => $credentials['email'],
            'password' => $credentials['password'],
        ])) {
            // Regenerate session for security
            $request->session()->regenerate();

            // ✅ Redirect to the correct dashboard
            if ($guard === 'public') {
                return redirect()->route('public.dashboard');
            } else {
                return redirect()->route('internal.dashboard');
            }
        }

        // ❌ Authentication failed
        return back()->withErrors([
            'email' => 'Invalid credentials or user type.',
        ])->onlyInput('email');
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
