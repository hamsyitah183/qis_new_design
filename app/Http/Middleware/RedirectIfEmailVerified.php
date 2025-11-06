<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfEmailVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        // Get the logged-in user from any guard
        $user = Auth::guard('public')->user() ?? Auth::guard('internal')->user();

        if (!$user) {
            // Not logged in
            return redirect()->route('login');
        }

        if ($user->hasVerifiedEmail()) {
            // Already verified
            if (Auth::guard('public')->check()) {
                return redirect()->route('public.dashboard');
            } elseif (Auth::guard('internal')->check()) {
                return redirect()->route('internal.dashboard');
            }
        }

        return $next($request);
    }
}
