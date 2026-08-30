<?php

namespace App\Services;

use App\Models\ApprovedPublic;
use App\Models\InternalUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;

class RoleService
{

    public function roleDataTable()
    {
        $roles = Role::with('permissions', 'users')
            ->where('name', '!=', 'public');

        return DataTables::of($roles)

            ->addColumn('user_count', fn($role) => $role->users)

            ->addColumn('permission_names', fn($role) => $role->permissions->pluck('name')->toArray())

            ->editColumn('name', function ($role) {

                $icons = [
                    'admin'   => '<i class="fs-23 ti ti-user-star"></i>',
                    'cleark'  => '<i class="fs-23 ti ti-user-cog"></i>',
                    'officer' => '<i class="fs-23 ti ti-user-shield"></i>',
                    'public'  => '<i class="fs-23 ti ti-users"></i>',
                ];

                $bgColors = [
                    'admin'   => 'bg-primary',
                    'cleark'  => 'bg-primary2',
                    'officer' => 'bg-primary1',
                    'public'  => 'bg-primary3',
                ];

                $icon    = $icons[$role->name] ?? '<i class="fs-23 ti ti-user"></i>';
                $bgClass = $bgColors[$role->name] ?? 'bg-secondary';

                return '
                    <div class="d-flex align-items-center gap-2">
                        <div class="avatar avatar-md fs-14 ' . $bgClass . ' svg-white d-flex justify-content-center align-items-center">
                            ' . $icon . '
                        </div>
                        <span class="ipv-role" data-original="' . e($role->name) . '">' . e($role->name) . '</span>
                    </div>
                ';
            })

            ->editColumn('permissions', function ($role) {

                $permissions = $role->permissions;
                $maxDisplay = 5;
                $count = $permissions->count();
                $list = $permissions->take($maxDisplay);

                $html = '<div class="d-flex gap-2 flex-wrap">';

                foreach ($list as $permission) {
                    $html .= '
                        <span class="badge bg-dark-transparent p-1 ipv-permission" data-original="' . e($permission->name) . '">'
                        . e($permission->name) .
                        '</span>';
                }

                if ($count > $maxDisplay) {
                    $html .= '<a class="permissionModal badge bg-dark-transparent p-1 ipv-more"
                    data-role = "' . $role->name . '" data-original="more...">more...</a>';
                }

                if ($count <= $maxDisplay) {
                    $html .= '
                        <span class="permissionModal badge bg-secondary-transparent p-1 d-flex align-items-center gap-1"
                        data-role = "' . $role->name . '">
                            <i class="ti ti-pencil"></i>
                        </span>';
                }

                $html .= '</div>';
                return $html;
            })

            ->rawColumns(['users', 'permissions', 'name'])
            ->make(true);
    }

    public function updateRole($roleName, $userIds)
    {
        DB::beginTransaction();

        try {
            // 1️⃣ Get the role object
            $role = Role::where('name', $roleName)->firstOrFail();

            // 2️⃣ Get all users that currently have this role
            $currentUsers = InternalUser::role($roleName)->pluck('id')->toArray();

            // 3️⃣ Get the users that should have this role
            $newUsers = InternalUser::whereIn('uuid', $userIds)->pluck('id')->toArray();

            // 4️⃣ Users to remove role from
            $removeUsers = array_diff($currentUsers, $newUsers);

            // 5️⃣ Users to assign role to
            $assignUsers = array_diff($newUsers, $currentUsers);

            // Remove role from users who are no longer selected
            if (!empty($removeUsers)) {
                $usersToRemove = InternalUser::whereIn('id', $removeUsers)->get();
                foreach ($usersToRemove as $user) {
                    $user->removeRole($roleName);
                }
            }

            // Assign role to newly selected users
            if (!empty($assignUsers)) {
                $usersToAssign = InternalUser::whereIn('id', $assignUsers)->get();
                foreach ($usersToAssign as $user) {
                    $user->assignRole($roleName);
                }
            }

            DB::commit();

            return [
                'success' => true,
                'message' => 'Role updated successfully.',
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Update role failed: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'An error occurred while updating the role.',
                'error' => $e->getMessage(),
            ];
        }
    }

    
}
