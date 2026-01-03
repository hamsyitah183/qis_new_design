<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticatedMulti
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        if ($request->is('broadcasting/auth')) {
            return $next($request);
        }
        
        // 🧩 If already logged in as PUBLIC
        if (Auth::guard('public')->check()) {
            return redirect()->route('public.dashboard');
        }

        // 🧩 If already logged in as INTERNAL
        if (Auth::guard('internal')->check()) {
            return redirect()->route('internal.dashboard');
        }

        return $next($request);
    }
}
