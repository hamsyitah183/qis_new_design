<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfOtherGuardAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $guard)
    {
        // If user is trying to access INTERNAL routes
        if ($guard === 'internal') {
            // Check if PUBLIC is logged in
            if (Auth::guard('public')->check()) {
                return redirect()->route('public.dashboard')
                    ->with('message', 'You are logged in as a public user.');
            }
        }

        // If user is trying to access PUBLIC routes
        if ($guard === 'public') {
            // Check if INTERNAL is logged in
            if (Auth::guard('internal')->check()) {
                return redirect()->route('internal.dashboard')
                    ->with('message', 'You are logged in as an internal user.');
            }
        }

        return $next($request);
    }
}
