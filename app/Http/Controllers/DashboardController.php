<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    //
    public function dashboard()
    {
        // ✅ Check which guard is logged in
        if (Auth::guard('public')->check()) {
            return $this->public_dashboard();
        }

        if (Auth::guard('internal')->check()) {
            return $this->internal_dashboard();
        }

        // ❌ If no guard is logged in, redirect to login
        return redirect()->route('login');
    }

    protected function public_dashboard()
    {
        return view('pages.public.dashboard'); // Public user dashboard
    }

    protected function internal_dashboard()
    {
        return view('pages.internal.dashboard'); // Internal user dashboard
    }
}
