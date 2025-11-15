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
        $roles = Role::with('permissions', 'users'); // eager load for counts

        return DataTables::of($roles)
            ->addColumn('user_count', function ($role) {
                return $role->users->count();
            })
            ->addColumn('permission_names', function ($role) {
                return $role->permissions->pluck('name')->toArray();
            })
            ->editColumn('name', function ($role) {

                // Role → icon
                $iconRole = [
                    'admin'   => '<i class="ti ti-user-star"></i>',
                    'cleark'  => '<i class="ti ti-user-cog"></i>',
                    'officer' => '<i class="ti ti-user-shield"></i>',
                    'public'  => '<i class="ti ti-users"></i>',
                ];

                // Role → background class
                $bgColor = [
                    'admin'   => 'bg-primary',       // red
                    'cleark'  => 'bg-primary2',      // yellow
                    'officer' => 'bg-primary1',      // blue
                    'public'  => 'bg-primary3',      // green
                ];

                // Fetch icon + bg class
                $icon = $iconRole[$role->name] ?? '<i class="ti ti-user"></i>';
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
                return '<div>
                <button class="btn btn rounded-pill btn-primary-light">
                    ' . $role->users->count() . '
                </button>
            </div>';
            })
            ->editColumn('permissions', function ($role) {
                return '<div>
                <button class="btn btn rounded-pill btn-primary1-light">
                    ' . $role->permissions->count() . '
                </button>
            </div>';
            })
            ->rawColumns(['users', 'permissions', 'name'])
            ->make(true);
    }
}
