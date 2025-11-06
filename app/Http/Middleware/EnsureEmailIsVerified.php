<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmailIsVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
   public function handle(Request $request, Closure $next)
    {
        // Check authenticated user for both guards
        $user = Auth::guard('public')->user() ?? Auth::guard('internal')->user();

        // If not verified, redirect to verify page
        if ($user && !$user->hasVerifiedEmail()) {
            return redirect()->route('verify.email');
        }

        return $next($request);
    }
}
