<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;

class RoleAndPermissionController extends Controller
{
    public function role()
    {
        return view('pages.internal.user_management.list_role', [
            'title' => 'List Role'
        ]);
    }

    public function role_list_data()
    {
        $roles = Role::with('permissions', 'users')
            ->where('name', '!=', 'public'); // exclude public role

        return DataTables::of($roles)
            ->addColumn('user_count', function ($role) {
                return $role->users;
            })
            ->addColumn('permission_names', function ($role) {
                return $role->permissions->pluck('name')->toArray();
            })
            ->editColumn('name', function ($role) {

                // Role → icon
                $iconRole = [
                    'admin'   => '<i class="fs-23 ti ti-user-star"></i>',
                    'cleark'  => '<i class="fs-23 ti ti-user-cog"></i>',
                    'officer' => '<i class="fs-23 ti ti-user-shield"></i>',
                    'public'  => '<i class="fs-23 ti ti-users"></i>',
                ];

                // Role → background class
                $bgColor = [
                    'admin'   => 'bg-primary',       // red
                    'cleark'  => 'bg-primary2',      // yellow
                    'officer' => 'bg-primary1',      // blue
                    'public'  => 'bg-primary3',      // green
                ];

                // Fetch icon + bg class
                $icon = $iconRole[$role->name] ?? '<i class="fs-23 ti ti-user"></i>';
                $bgClass = $bgColor[$role->name] ?? 'bg-secondary';

                // Build HTML
                return '
                    <div class="d-flex align-items-center gap-2">
                        <div class="avatar avatar-md fs-14 ' . $bgClass . ' svg-white d-flex justify-content-center align-items-center">
                            ' . $icon . '
                        </div>
                        <span>' . e($role->name) . '</span>
                    </div>
                ';
            })


            ->editColumn('users', function ($role) {
                $users = $role->users;
                $maxDisplay = 5;
                $count = $users->count();

                $userHTML = '<div class="avatar-list-stacked">';

                $displayUsers = $users->take($maxDisplay);

                foreach ($displayUsers as $user) {

                    // Generate initials
                    $initials = collect(explode(' ', $user->fullname))
                        ->map(fn($word) => strtoupper(substr($word, 0, 1)))
                        ->join('');

                    $userHTML .= '
                    <span class="avatar avatar-sm avatar-rounded border border-white bg-primary text-fixed-whiter"
                        data-bs-toggle="tooltip"
                        data-bs-placement="top"
                        title="' . e($user->fullname) . '">
                        ' . $initials . '
                    </span>';
                }

                // CASE A: More than max → show +X
                if ($count > $maxDisplay) {
                    $extra = $count - $maxDisplay;
                    $userHTML .= '
                    <a class="userModal avatar avatar-sm bg-secondary border border-white text-fixed-white avatar-rounded "
                      data-bs-toggle="tooltip" data-bs-placement="top" title = "More"
                        href="javascript:void(0);" data-role = "' . $role->name .'">
                        +' . $extra . '
                    </a>';
                }

                // CASE B: Less than max → show a "+" add icon
                if ($count < $maxDisplay) {
                    $userHTML .= '
                    <a class="userModal avatar avatar-sm bg-secondary border border-white text-fixed-white avatar-rounded 
                        " data-bs-toggle="tooltip" data-bs-placement="top" title = "Add User"
                        href="javascript:void(0);" data-role = "' . $role->name .'">
                        <i class="ti ti-plus"></i>
                    </a>';
                }

                $userHTML .= '</div>';

                return $userHTML;
            })

            ->editColumn('permissions', function ($role) {

                $permissions = $role->permissions;
                $maxDisplay = 5;
                $count = $permissions->count();
                $displayPermissions = $permissions->take($maxDisplay);

                $permissionHTML = '<div class="d-flex gap-2 flex-wrap">';

                foreach ($displayPermissions as $permission) {
                    $permissionHTML .= '
                <span class="badge bg-dark-transparent p-1">
                    ' . e($permission->name) . '
                </span>';
                }

                // CASE A: More than max
                if ($count > $maxDisplay) {
                    $permissionHTML .= '
                <a class="badge bg-dark-transparent p-1" href="javascript:void(0);">
                    more...
                </a>';
                }

                // CASE B: Less/equal → show pencil + "..."
                if ($count <= $maxDisplay) {
                    $permissionHTML .= '
                <span class="badge bg-secondary-transparent p-1 d-flex align-items-center gap-1">
                    <i class="ti ti-pencil"></i>
                </span>';
                }

                $permissionHTML .= '</div>';

                return $permissionHTML;
            })

            ->rawColumns(['users', 'permissions', 'name'])
            ->make(true);
    }
}
