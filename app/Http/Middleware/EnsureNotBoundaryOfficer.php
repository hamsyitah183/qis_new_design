<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class EnsureNotBoundaryOfficer
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        \Log::info('EnsureNotBoundaryOfficer Middleware Hit');
        
        if (Auth::check()) {
            $user = Auth::user();
            $roles = $user->getRoleNames();
            \Log::info('User ID: ' . $user->id . ' | Roles: ' . $roles);

            // Check using Spatie's method
            if ($user->hasRole('boundary officer')) {
                \Log::info('User HAS role "boundary officer" - Aborting 403');
                abort(403, 'Unauthorized action. Boundary Officers are restricted from this area.');
            } else {
                \Log::info('User DOES NOT HAVE role "boundary officer"');
            }
        } else {
            \Log::info('User is NOT authenticated');
        }

        return $next($request);
    }
}
