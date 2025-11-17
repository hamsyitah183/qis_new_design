<?php

use Illuminate\Support\Facades\Auth;

use Spatie\Permission\Models\Role;




if (!function_exists('authUser')) {
    function authUser()
    {
        // // Get all roles
        // $allRoles = Role::all();

        // // Or just get their names
        // $allRoleNames = Role::pluck('name');

        // dd($allRoles);        // Full Role models
        // dd($allRoleNames);    // Only names
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

            // dd([
            //     'type' => 'internal',
            //     'user' => $user,
            //     'roles' => $user->getRoleNames(), // Returns a collection of role names
            //     'permissions' => $user->getAllPermissions()->pluck('name'), // Optional: list all permissions
            // ]);

            return [
                'type' => 'public',
                'user' => $user,
            ];
        }

        return null;
    }
}
