<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class PermissionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
        $this->middleware('role:super_admin');
    }

    /**
     * Display a listing of permissions.
     */
    public function index()
    {
        $permissions = Permission::with(['roles'])
                                ->withCount('roles')
                                ->when(request('search'), function($query, $search) {
                                    $query->where('display_name', 'like', "%{$search}%")
                                          ->orWhere('name', 'like', "%{$search}%")
                                          ->orWhere('description', 'like', "%{$search}%");
                                })
                                ->when(request('category'), function($query, $category) {
                                    $query->where('category', $category);
                                })
                                ->when(request('status') !== null, function($query) {
                                    $query->where('is_active', request('status'));
                                })
                                ->orderBy('category')
                                ->orderBy('display_name')
                                ->paginate(15);

        $categories = Permission::getCategories();

        return view('superadmin.permissions.index', compact('permissions', 'categories'));
    }

    /**
     * Show the form for creating a new permission.
     */
    public function create()
    {
        $categories = Permission::getCategories();
        $actions = Permission::getActions();

        return view('superadmin.permissions.create', compact('categories', 'actions'));
    }

    /**
     * Store a newly created permission in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:permissions|regex:/^[a-z_]+$/',
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'category' => 'required|string|in:' . implode(',', array_keys(Permission::getCategories())),
            'resource' => 'required|string|max:100',
            'action' => 'required|string|in:' . implode(',', array_keys(Permission::getActions())),
        ]);

        DB::beginTransaction();
        try {
            $permission = Permission::create([
                'name' => $request->name,
                'display_name' => $request->display_name,
                'description' => $request->description,
                'category' => $request->category,
                'resource' => $request->resource,
                'action' => $request->action,
                'is_active' => true
            ]);

            // Log the creation
            Log::info('Permission created', [
                'permission_id' => $permission->id,
                'permission_name' => $permission->name,
                'created_by' => Auth::guard('admin')->id()
            ]);

            DB::commit();

            return redirect()->route('superadmin.permissions.index')
                           ->with('success', 'Permission created successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create permission', [
                'error' => $e->getMessage(),
                'data' => $request->all()
            ]);

            return back()->withInput()
                        ->withErrors(['error' => 'Failed to create permission: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified permission.
     */
    public function show(Permission $permission)
    {
        $permission->load(['roles']);

        return view('superadmin.permissions.show', compact('permission'));
    }

    /**
     * Show the form for editing the specified permission.
     */
    public function edit(Permission $permission)
    {
        $categories = Permission::getCategories();
        $actions = Permission::getActions();

        return view('superadmin.permissions.edit', compact('permission', 'categories', 'actions'));
    }

    /**
     * Update the specified permission in storage.
     */
    public function update(Request $request, Permission $permission)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:100', 'regex:/^[a-z_]+$/', Rule::unique('permissions')->ignore($permission->id)],
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'category' => 'required|string|in:' . implode(',', array_keys(Permission::getCategories())),
            'resource' => 'required|string|max:100',
            'action' => 'required|string|in:' . implode(',', array_keys(Permission::getActions())),
        ]);

        DB::beginTransaction();
        try {
            $permission->update([
                'name' => $request->name,
                'display_name' => $request->display_name,
                'description' => $request->description,
                'category' => $request->category,
                'resource' => $request->resource,
                'action' => $request->action,
            ]);

            // Log the update
            Log::info('Permission updated', [
                'permission_id' => $permission->id,
                'permission_name' => $permission->name,
                'updated_by' => Auth::guard('admin')->id()
            ]);

            DB::commit();

            return redirect()->route('superadmin.permissions.index')
                           ->with('success', 'Permission updated successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update permission', [
                'error' => $e->getMessage(),
                'permission_id' => $permission->id,
                'data' => $request->all()
            ]);

            return back()->withInput()
                        ->withErrors(['error' => 'Failed to update permission: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified permission from storage.
     */
    public function destroy(Permission $permission)
    {
        // Check if permission is assigned to any roles
        if ($permission->roles()->count() > 0) {
            return back()->withErrors(['error' => 'Cannot delete permission that is assigned to roles']);
        }

        DB::beginTransaction();
        try {
            $permission->delete();

            // Log the deletion
            Log::info('Permission deleted', [
                'permission_id' => $permission->id,
                'permission_name' => $permission->name,
                'deleted_by' => Auth::guard('admin')->id()
            ]);

            DB::commit();

            return redirect()->route('superadmin.permissions.index')
                           ->with('success', 'Permission deleted successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete permission', [
                'error' => $e->getMessage(),
                'permission_id' => $permission->id
            ]);

            return back()->withErrors(['error' => 'Failed to delete permission: ' . $e->getMessage()]);
        }
    }

    /**
     * Toggle permission status.
     */
    public function toggleStatus(Permission $permission)
    {
        $permission->update(['is_active' => !$permission->is_active]);

        Log::info('Permission status toggled', [
            'permission_id' => $permission->id,
            'permission_name' => $permission->name,
            'new_status' => $permission->is_active,
            'toggled_by' => Auth::guard('admin')->id()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Permission status updated successfully',
            'is_active' => $permission->is_active
        ]);
    }

    /**
     * Bulk action for permissions.
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:activate,deactivate,delete',
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,id'
        ]);

        DB::beginTransaction();
        try {
            $permissions = Permission::whereIn('id', $request->permissions)->get();

            switch ($request->action) {
                case 'activate':
                    foreach ($permissions as $permission) {
                        $permission->update(['is_active' => true]);
                    }
                    $message = 'Permissions activated successfully';
                    break;

                case 'deactivate':
                    foreach ($permissions as $permission) {
                        $permission->update(['is_active' => false]);
                    }
                    $message = 'Permissions deactivated successfully';
                    break;

                case 'delete':
                    // Check if any permission is assigned to roles
                    $assignedPermissions = $permissions->filter(function($permission) {
                        return $permission->roles()->count() > 0;
                    });

                    if ($assignedPermissions->count() > 0) {
                        throw new \Exception('Cannot delete permissions that are assigned to roles');
                    }

                    foreach ($permissions as $permission) {
                        $permission->delete();
                    }
                    $message = 'Permissions deleted successfully';
                    break;
            }

            Log::info('Bulk permission action', [
                'action' => $request->action,
                'permissions_count' => count($request->permissions),
                'performed_by' => Auth::guard('admin')->id()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $message
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed bulk permission action', [
                'error' => $e->getMessage(),
                'action' => $request->action,
                'permissions' => $request->permissions
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to perform bulk action: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Assign permission to role via API.
     */
    public function assignToRole(Request $request)
    {
        $request->validate([
            'permission_id' => 'required|exists:permissions,id',
            'role_id' => 'required|exists:roles,id'
        ]);

        $permission = Permission::findOrFail($request->permission_id);
        $role = Role::findOrFail($request->role_id);
        $currentUser = Auth::guard('admin')->user();

        DB::beginTransaction();
        try {
            $role->givePermission($permission, $currentUser);

            // Log audit trail
            \App\Models\AuditTrail::create([
                'user_id' => $currentUser->id,
                'action_type' => 'update',
                'role_id' => $role->id,
                'description' => "Permission '{$permission->display_name}' assigned to role '{$role->display_name}'",
                'ip_address' => $request->ip()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Permission assigned successfully',
                'data' => [
                    'permission' => $permission,
                    'role' => $role->load('permissions')
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to assign permission'
            ], 500);
        }
    }

    /**
     * Remove permission from role via API.
     */
    public function removeFromRole(Request $request)
    {
        $request->validate([
            'permission_id' => 'required|exists:permissions,id',
            'role_id' => 'required|exists:roles,id'
        ]);

        $permission = Permission::findOrFail($request->permission_id);
        $role = Role::findOrFail($request->role_id);
        $currentUser = Auth::guard('admin')->user();

        DB::beginTransaction();
        try {
            $role->revokePermission($permission, $currentUser);

            // Log audit trail
            \App\Models\AuditTrail::create([
                'user_id' => $currentUser->id,
                'action_type' => 'update',
                'role_id' => $role->id,
                'description' => "Permission '{$permission->display_name}' revoked from role '{$role->display_name}'",
                'ip_address' => $request->ip()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Permission removed successfully',
                'data' => [
                    'permission' => $permission,
                    'role' => $role->load('permissions')
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove permission'
            ], 500);
        }
    }

    /**
     * Get permission roles via API.
     */
    public function apiGetRoles(Permission $permission)
    {
        return response()->json([
            'success' => true,
            'data' => [
                'permission' => $permission,
                'roles' => $permission->roles
            ]
        ]);
    }
}
