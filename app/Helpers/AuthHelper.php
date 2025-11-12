<?php

use Illuminate\Support\Facades\Auth;

if (!function_exists('authUser')) {
    function authUser()
    {
        if (Auth::guard('internal')->check()) {
            return [
                'type' => 'internal',
                'user' => Auth::guard('internal')->user(),
            ];
        } elseif (Auth::guard('public')->check()) {
            return [
                'type' => 'public',
                'user' => Auth::guard('public')->user(),
            ];
        }

        return null;
    }
}
