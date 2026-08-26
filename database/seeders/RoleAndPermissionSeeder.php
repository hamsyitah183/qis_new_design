<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]
            ->forgetCachedPermissions();

        // ==================================================
        // 1. PERMISSIONS
        // ==================================================

        $internalPermissions = [

            // ==================================================
            // Dashboard
            // ==================================================
            'view dashboard',

            // ==================================================
            // User Management — Internal
            // ==================================================
            'create internal user',
            'read internal user',
            'update internal user',
            'delete internal user',

            // ==================================================
            // User Management — Public
            // ==================================================
            'create public user',
            'read public user',
            'update public user',
            'delete public user',
            'approve public user',

            // ==================================================
            // Activity Log
            // ==================================================
            'read activity log',

            // ==================================================
            // Application / Importer / Exporter
            // ==================================================
            'view importer list',
            'view exporter list',
            'read application',
            'approve application',
            'delete application',

            // ==================================================
            // Notification
            // ==================================================
            'view notification',

            // ==================================================
            // Application
            // ==================================================
            'edit application',

            // ==================================================
            // Permit
            // ==================================================
            'approve permit',
            'print permit',
            'scan permit',

            // ==================================================
            // Orders & Invoices
            // ==================================================
            'view orders invoices',

            // ==================================================
            // Reports
            // ==================================================
            'generate financial report',
            'generate operational report',
            'generate performance report',

            // ==================================================
            // Item Management
            // ==================================================
            'manage import permit item',
            'manage consignment item',

            // ==================================================
            // System / Settings
            // ==================================================
            'control panel',
            'manage announcement',
            'manage settings',
            'manage role and permission',
        ];

        // Create all internal permissions
        foreach ($internalPermissions as $permission) {
            Permission::firstOrCreate([
                'name'       => $permission,
                'guard_name' => 'internal',
            ]);
        }

        // ==================================================
        // 2. PUBLIC GUARD PERMISSIONS
        // ==================================================

        Permission::firstOrCreate([
            'name'       => 'view dashboard',
            'guard_name' => 'public',
        ]);

        // ==================================================
        // 3. PERMISSION HELPER
        // ==================================================

        $perms = fn (array $names) =>
            Permission::where('guard_name', 'internal')
                ->whereIn('name', $names)
                ->get();

        // ==================================================
        // 4. ROLES
        // ==================================================

        $roles = [

            // ==================================================
            // SUPERADMIN
            // Everything
            // ==================================================
            'superadmin' => [
                'guard_name' => 'internal',

                'permissions' => Permission::where(
                    'guard_name',
                    'internal'
                )->get(),
            ],

            // ==================================================
            // ADMIN
            // Everything except Activity Log
            // ==================================================
            'admin' => [
                'guard_name' => 'internal',

                'permissions' => $perms([
                    'view dashboard',

                    // Internal User Management
                    'create internal user',
                    'read internal user',
                    'update internal user',
                    'delete internal user',

                    // Public User Management
                    'create public user',
                    'read public user',
                    'update public user',
                    'delete public user',
                    'approve public user',

                    // Application
                    'view importer list',
                    'view exporter list',
                    'read application',
                    'approve application',
                    'delete application',
                    'edit application',

                    // Notification
                    'view notification',

                    // Permit
                    'approve permit',
                    'print permit',
                    'scan permit',

                    // Orders & Invoices
                    'view orders invoices',

                    // Reports
                    'generate financial report',
                    'generate operational report',
                    'generate performance report',

                    // Item Management
                    'manage import permit item',
                    'manage consignment item',

                    // System
                    'control panel',
                    'manage announcement',
                    'manage settings',
                ]),
            ],

            // ==================================================
            // OFFICER
            // ==================================================
            'officer' => [
                'guard_name' => 'internal',

                'permissions' => $perms([
                    'view dashboard',

                    // Application
                    'view importer list',
                    'view exporter list',
                    'read application',
                    'approve application',

                    // Notification
                    'view notification',

                    // Permit
                    'approve permit',
                    'print permit',
                    'scan permit',

                    // Orders & Invoices
                    'view orders invoices',

                    // Reports
                    'generate operational report',
                    'generate performance report',
                ]),
            ],

            // ==================================================
            // CLERK
            // ==================================================
            'clerk' => [
                'guard_name' => 'internal',

                'permissions' => $perms([
                    'view dashboard',

                    // Application
                    'view importer list',
                    'view exporter list',
                    'read application',
                    'approve application',

                    // Notification
                    'view notification',

                  

                    // Orders & Invoices
                    'view orders invoices',

                    // Reports
                    'generate operational report',
                ]),
            ],

            // ==================================================
            // BOUNDARY OFFICER
            // ==================================================
            'boundary officer' => [
                'guard_name' => 'internal',

                'permissions' => $perms([
                    'view dashboard',

                    // Application
                    'view importer list',
                    'view exporter list',
                    'read application',
                    'approve application',

                    // Notification
                    'view notification',

                    // Permit
                   
                    'print permit',
                    'scan permit',

                    // Orders & Invoices
                    'view orders invoices',

                    // Reports
                    'generate operational report',
                    'generate performance report',
                ]),
            ],

            // ==================================================
            // FINANCE
            // ==================================================
            'finance' => [
                'guard_name' => 'internal',

                'permissions' => $perms([
                    'view dashboard',

                    // Application
                    'view importer list',
                    'view exporter list',

                    // Notification
                    'view notification',

                    // Orders & Invoices
                    'view orders invoices',

                    // Financial Report
                    'generate financial report',
                ]),
            ],

            // ==================================================
            // PUBLIC
            // ==================================================
            'public' => [
                'guard_name' => 'public',

                'permissions' => Permission::where(
                    'guard_name',
                    'public'
                )->get(),
            ],
        ];

        // ==================================================
        // 5. CREATE / UPDATE ROLES
        // ==================================================

        foreach ($roles as $roleName => $data) {

            $role = Role::firstOrCreate([
                'name'       => $roleName,
                'guard_name' => $data['guard_name'],
            ]);

            $role->syncPermissions($data['permissions']);
        }

        // ==================================================
        // 6. CLEAR PERMISSION CACHE
        // ==================================================

        app()[\Spatie\Permission\PermissionRegistrar::class]
            ->forgetCachedPermissions();
    }
}