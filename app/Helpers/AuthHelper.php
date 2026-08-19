<?php

use Illuminate\Support\Facades\Auth;

use Spatie\Permission\Models\Role;




if (!function_exists('authUser')) {
    function authUser()
    {
        // 🔐 INTERNAL USER
        if (Auth::guard('internal')->check()) {
            $user = Auth::guard('internal')->user();

            return [
                'type'        => 'internal',
                'guard'       => 'internal',
                'user'        => $user,
                'roles'       => $user->getRoleNames(),              // Collection (many roles)
                // 'permissions' => $user->getAllPermissions()
                //     ->pluck('name'),                                // Optional
            ];
        }

        // 🌍 PUBLIC USER
        if (Auth::guard('public')->check()) {
            $user = Auth::guard('public')->user()->load([
                'approved',
                'approved.approver',
                'approved.userAttachments',
                'approved.verificationAttachments',
                'attachments'
            ]);

            return [
                'type'        => 'public',
                'guard'       => 'public',
                'user'        => $user,
                'roles'       => $user->getRoleNames(),              // Collection
                
            ];
        }

        return null;
    }
}
