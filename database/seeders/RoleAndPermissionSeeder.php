<?php

namespace Database\Seeders;

use App\Models\InternalUser;
use App\Models\PublicUser;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ==================================================
        // 🧩 1. Define Permissions for each guard
        // ==================================================
        $permissions = [
            'view dashboard',

            // ======== user management ====================

            // internal
            'create internal user',
            'read internal user',
            'update internal user',
            'delete internal user',


            // public
            'create public user',
            'read public user',
            'update public user',
            'delete public user',
            'approve public user',


            // activity log
            'read activity log',

            // role
            'update role', 


            // ================================================
        ];

        foreach ($permissions as $permission) {
            // Create for internal guard
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'internal',
            ]);

            // Create for public guard (only view dashboard makes sense)
            if ($permission === 'view dashboard') {
                Permission::firstOrCreate([
                    'name' => $permission,
                    'guard_name' => 'public',
                ]);
            }
        }

        // ==================================================
        // 🧩 2. Define Roles and assign permissions
        // ==================================================
        $roles = [
            // Internal roles
            'superadmin' => [
                'guard_name' => 'internal',
                'permissions' => ['view dashboard', 'create internal user', 'create public user', 'approve public user'],
            ],
            'admin' => [
                'guard_name' => 'internal',
                'permissions' => ['view dashboard', 'create internal user', 'create public user', 'approve public user'],
            ],
            'officer' => [
                'guard_name' => 'internal',
                'permissions' => ['view dashboard'],
            ],
            'clerk' => [
                'guard_name' => 'internal',
                'permissions' => ['view dashboard'],
            ],
            'boundary officer' => [
                'guard_name' => 'internal',
                'permissions' => ['view dashboard'],
            ],
            'finance' => [
                'guard_name' => 'internal',
                'permissions' => ['view dashboard'],
            ],

            // Public role
            'public' => [
                'guard_name' => 'public',
                'permissions' => ['view dashboard'],
            ],
        ];

        foreach ($roles as $roleName => $data) {
            $role = Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => $data['guard_name'],
            ]);

            $role->syncPermissions($data['permissions']);
        }
    }
}
