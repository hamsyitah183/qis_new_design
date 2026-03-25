<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ==================================================
        // 🧩 1. Permissions
        // ==================================================
        $internalPermissions = [
            'view dashboard',

            // user management — internal
            'create internal user',
            'read internal user',
            'update internal user',
            'delete internal user',

            // user management — public
            'create public user',
            'read public user',
            'update public user',
            'delete public user',
            'approve public user',

            // activity log
            'read activity log',

            // application
            'view importer list',
            'view exporter list',

            // settings
            'manage settings',

            // role and permission management
            'manage role and permission',
        ];

        foreach ($internalPermissions as $permission) {
            Permission::firstOrCreate([
                'name'       => $permission,
                'guard_name' => 'internal',
            ]);
        }

        // Flush cache again so syncPermissions() sees the newly created permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Public guard only needs view dashboard
        Permission::firstOrCreate([
            'name'       => 'view dashboard',
            'guard_name' => 'public',
        ]);

        // ==================================================
        // 🧩 2. Roles + permissions
        // ==================================================

        // ✅ Helper — fetch permissions by name for a given guard
        $perms = fn (array $names) => Permission::where('guard_name', 'internal')
            ->whereIn('name', $names)
            ->get();

        $allInternal = Permission::where('guard_name', 'internal')->get();

        $roles = [

            // ✅ Superadmin — everything (including manage role and permission)
            'superadmin' => [
                'guard_name'  => 'internal',
                'permissions' => $allInternal,
            ],

            // ✅ Admin — everything except activity log and manage role
            'admin' => [
                'guard_name'  => 'internal',
                'permissions' => $perms([
                    'view dashboard',
                    'create internal user',
                    'read internal user',
                    'update internal user',
                    'delete internal user',
                    'create public user',
                    'read public user',
                    'update public user',
                    'delete public user',
                    'approve public user',
                    'view importer list',
                    'view exporter list',
                    'manage settings',
                    // 'manage role and permission' intentionally excluded — superadmin only
                ]),
            ],

            'officer' => [
                'guard_name'  => 'internal',
                'permissions' => $perms(['view dashboard']),
            ],

            'clerk' => [
                'guard_name'  => 'internal',
                'permissions' => $perms(['view dashboard']),
            ],

            'boundary officer' => [
                'guard_name'  => 'internal',
                'permissions' => $perms(['view dashboard']),
            ],

            'finance' => [
                'guard_name'  => 'internal',
                'permissions' => $perms(['view dashboard']),
            ],

            // Public role
            'public' => [
                'guard_name'  => 'public',
                'permissions' => Permission::where('guard_name', 'public')->get(),
            ],
        ];

        foreach ($roles as $roleName => $data) {
            $role = Role::firstOrCreate([
                'name'       => $roleName,
                'guard_name' => $data['guard_name'],
            ]);

            $role->syncPermissions($data['permissions']);
        }
    }
}