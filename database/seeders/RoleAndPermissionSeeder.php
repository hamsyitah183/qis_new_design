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
            'create internal user',
            'approve public user',
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
            'admin' => [
                'guard_name' => 'internal',
                'permissions' => ['view dashboard', 'create internal user', 'approve public user'],
            ],
            'officer' => [
                'guard_name' => 'internal',
                'permissions' => ['view dashboard'],
            ],
            'clerk' => [
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
