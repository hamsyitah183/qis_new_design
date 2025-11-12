<?php

use Illuminate\Support\Facades\Auth;

if (!function_exists('authUser')) {
    function authUser()
    {
        if (Auth::guard('internal')->check()) {
            $user = Auth::guard('internal')->user();
            // Load any relationships you might need for internal users later
            return [
                'type' => 'internal',
                'user' => $user,
            ];
        } elseif (Auth::guard('public')->check()) {
            $user = Auth::guard('public')->user()->load([
                'approved',
                'approved.approver',
            ]);
            return [
                'type' => 'public',
                'user' => $user,
            ];
        }

        return null;
    }
}
