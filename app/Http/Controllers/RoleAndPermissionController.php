<?php

namespace App\Http\Controllers;

use App\Services\RoleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Yajra\DataTables\Facades\DataTables;

class RoleAndPermissionController extends Controller
{
    public function role()
    {
        if (auth()->user()->hasRole('boundary officer')) {
            abort(403, 'Unauthorized action. Boundary Officers are restricted from this area.');
        }

        return view('pages.internal.user_management.list_role', [
            'title' => 'List Role'
        ]);
    }

    public function role_list_data(RoleService $roleService)
    {
        if (auth()->user()->hasRole('boundary officer')) {
            abort(403, 'Unauthorized action. Boundary Officers are restricted from this area.');
        }

        return $roleService->roleDataTable();
    }

    public function update_role(Request $request, RoleService $roleService)
    {
        if (auth()->user()->hasRole('boundary officer')) {
            abort(403, 'Unauthorized action. Boundary Officers are restricted from this area.');
        }

        $roleName = $request->input('role'); // the role name
        $userIds = $request->input('users', []); // array of selected users

        $result = $roleService->updateRole($roleName, $userIds);
    }

    public function get_permission()
    {
        if (auth()->user()->hasRole('boundary officer')) {
            abort(403, 'Unauthorized action. Boundary Officers are restricted from this area.');
        }

        // Order by creation date ascending (earliest first)
        $permission = Permission::orderBy('created_at', 'asc')->pluck('name');

        return response()->json([
            'data' => $permission
        ]);
    }

    public function update_permission(Request $request)
    {
        if (auth()->user()->hasRole('boundary officer')) {
            abort(403, 'Unauthorized action. Boundary Officers are restricted from this area.');
        }

        $request->validate([
            'role' => 'required|string|exists:roles,name',
            'permission' => 'array',
            'permission.*' => 'string|exists:permissions,name',
        ]);

        $roleName = $request->input('role');
        $permissions = $request->input('permission', []);

        DB::beginTransaction();

        try {
            // Get the role
            $role = Role::where('name', $roleName)->firstOrFail();

            // Sync the permissions (replaces old permissions)
            $role->syncPermissions($permissions);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Permissions for role '{$roleName}' updated successfully.",
                'permissions' => $role->permissions->pluck('name'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update role permissions: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating permissions.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
