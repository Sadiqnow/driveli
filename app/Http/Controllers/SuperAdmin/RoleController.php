<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Permission;
use App\Models\AdminUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
        $this->middleware('role:super_admin');
    }

    /**
     * Display a listing of roles.
     */
    public function index()
    {
        $roles = Role::with(['permissions', 'activeUsers'])
                    ->withCount('activeUsers')
                    ->when(request('search'), function($query, $search) {
                        $query->where('display_name', 'like', "%{$search}%")
                              ->orWhere('name', 'like', "%{$search}%")
                              ->orWhere('description', 'like', "%{$search}%");
                    })
                    ->when(request('status') !== null, function($query) {
                        $query->where('is_active', request('status'));
                    })
                    ->when(request('level'), function($query, $level) {
                        $query->where('level', $level);
                    })
                    ->orderBy('level', 'desc')
                    ->orderBy('display_name')
                    ->paginate(15);

        $roleLevels = Role::getRoleLevels();

        return view('superadmin.roles.index', compact('roles', 'roleLevels'));
    }

    /**
     * Show the form for creating a new role.
     */
    public function create()
    {
        $permissions = Permission::active()
                                ->orderBy('category')
                                ->orderBy('display_name')
                                ->get()
                                ->groupBy('category');

        $roleLevels = Role::getRoleLevels();

        return view('superadmin.roles.create', compact('permissions', 'roleLevels'));
    }

    /**
     * Store a newly created role in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:roles|regex:/^[a-z_]+$/',
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'level' => 'required|integer|min:1|max:100',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id'
        ]);

        DB::beginTransaction();
        try {
            $role = Role::create([
                'name' => $request->name,
                'display_name' => $request->display_name,
                'description' => $request->description,
                'level' => $request->level,
                'is_active' => true
            ]);

            if ($request->permissions) {
                $role->permissions()->attach($request->permissions, [
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            // Log the creation
            Log::info('Role created', [
                'role_id' => $role->id,
                'role_name' => $role->name,
                'created_by' => Auth::guard('admin')->id()
            ]);

            DB::commit();

            return redirect()->route('superadmin.roles.index')
                           ->with('success', 'Role created successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create role', [
                'error' => $e->getMessage(),
                'data' => $request->all()
            ]);

            return back()->withInput()
                        ->withErrors(['error' => 'Failed to create role: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified role.
     */
    public function show(Role $role)
    {
        $role->load(['permissions', 'activeUsers']);

        return view('superadmin.roles.show', compact('role'));
    }

    /**
     * Show the form for editing the specified role.
     */
    public function edit(Role $role)
    {
        $permissions = Permission::active()
                                ->orderBy('category')
                                ->orderBy('display_name')
                                ->get()
                                ->groupBy('category');

        $roleLevels = Role::getRoleLevels();
        $rolePermissions = $role->permissions->pluck('id')->toArray();

        return view('superadmin.roles.edit', compact('role', 'permissions', 'roleLevels', 'rolePermissions'));
    }

    /**
     * Update the specified role in storage.
     */
    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:100', 'regex:/^[a-z_]+$/', Rule::unique('roles')->ignore($role->id)],
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'level' => 'required|integer|min:1|max:100',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id'
        ]);

        DB::beginTransaction();
        try {
            $role->update([
                'name' => $request->name,
                'display_name' => $request->display_name,
                'description' => $request->description,
                'level' => $request->level
            ]);

            // Sync permissions
            if ($request->has('permissions')) {
                $role->permissions()->sync($request->permissions);
            } else {
                $role->permissions()->detach();
            }

            // Log the update
            Log::info('Role updated', [
                'role_id' => $role->id,
                'role_name' => $role->name,
                'updated_by' => Auth::guard('admin')->id()
            ]);

            DB::commit();

            return redirect()->route('superadmin.roles.index')
                           ->with('success', 'Role updated successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update role', [
                'error' => $e->getMessage(),
                'role_id' => $role->id,
                'data' => $request->all()
            ]);

            return back()->withInput()
                        ->withErrors(['error' => 'Failed to update role: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified role from storage.
     */
    public function destroy(Role $role)
    {
        // Prevent deletion of system roles
        if (in_array($role->name, ['super_admin', 'admin'])) {
            return back()->withErrors(['error' => 'Cannot delete system roles']);
        }

        // Check if role has active users
        if ($role->activeUsers()->count() > 0) {
            return back()->withErrors(['error' => 'Cannot delete role with active users assigned']);
        }

        DB::beginTransaction();
        try {
            $role->permissions()->detach();
            $role->delete();

            // Log the deletion
            Log::info('Role deleted', [
                'role_id' => $role->id,
                'role_name' => $role->name,
                'deleted_by' => Auth::guard('admin')->id()
            ]);

            DB::commit();

            return redirect()->route('superadmin.roles.index')
                           ->with('success', 'Role deleted successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete role', [
                'error' => $e->getMessage(),
                'role_id' => $role->id
            ]);

            return back()->withErrors(['error' => 'Failed to delete role: ' . $e->getMessage()]);
        }
    }

    /**
     * Toggle role status.
     */
    public function toggleStatus(Role $role)
    {
        // Prevent disabling system roles
        if (in_array($role->name, ['super_admin', 'admin'])) {
            return response()->json(['error' => 'Cannot modify system roles'], 403);
        }

        $role->update(['is_active' => !$role->is_active]);

        Log::info('Role status toggled', [
            'role_id' => $role->id,
            'role_name' => $role->name,
            'new_status' => $role->is_active,
            'toggled_by' => Auth::guard('admin')->id()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Role status updated successfully',
            'is_active' => $role->is_active
        ]);
    }

    /**
     * Assign permissions to role via API.
     */
    public function apiAssignPermissions(Request $request, Role $role)
    {
        $request->validate([
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,id'
        ]);

        DB::beginTransaction();
        try {
            $role->permissions()->sync($request->permissions);

            Log::info('Permissions assigned to role', [
                'role_id' => $role->id,
                'role_name' => $role->name,
                'permissions_count' => count($request->permissions),
                'assigned_by' => Auth::guard('admin')->id()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Permissions assigned successfully',
                'data' => [
                    'role' => $role->load('permissions'),
                    'permissions_count' => $role->permissions()->count()
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to assign permissions to role', [
                'error' => $e->getMessage(),
                'role_id' => $role->id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to assign permissions'
            ], 500);
        }
    }

    /**
     * Get role permissions via API.
     */
    public function apiGetPermissions(Role $role)
    {
        return response()->json([
            'success' => true,
            'data' => [
                'role' => $role,
                'permissions' => $role->permissions()->with('category')->get()
            ]
        ]);
    }

    /**
     * Assign role to user.
     */
    public function assignToUser(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:admin_users,id',
            'role_id' => 'required|exists:roles,id'
        ]);

        $user = AdminUser::findOrFail($request->user_id);
        $role = Role::findOrFail($request->role_id);
        $currentUser = Auth::guard('admin')->user();

        // Check if current user can manage this role
        if (!$currentUser->roles()->where('name', 'super_admin')->exists()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        DB::beginTransaction();
        try {
            $role->assignToUser($user, $currentUser);

            // Log audit trail
            \App\Models\AuditTrail::create([
                'user_id' => $currentUser->id,
                'action_type' => 'assign',
                'role_id' => $role->id,
                'target_user_id' => $user->id,
                'description' => "Role '{$role->display_name}' assigned to user '{$user->name}'",
                'ip_address' => $request->ip()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Role assigned successfully',
                'data' => [
                    'user' => $user->load('roles'),
                    'role' => $role
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to assign role'
            ], 500);
        }
    }

    /**
     * Remove role from user.
     */
    public function removeFromUser(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:admin_users,id',
            'role_id' => 'required|exists:roles,id'
        ]);

        $user = AdminUser::findOrFail($request->user_id);
        $role = Role::findOrFail($request->role_id);
        $currentUser = Auth::guard('admin')->user();

        // Check if current user can manage this role
        if (!$currentUser->roles()->where('name', 'super_admin')->exists()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        DB::beginTransaction();
        try {
            $role->removeFromUser($user, $currentUser);

            // Log audit trail
            \App\Models\AuditTrail::create([
                'user_id' => $currentUser->id,
                'action_type' => 'revoke',
                'role_id' => $role->id,
                'target_user_id' => $user->id,
                'description' => "Role '{$role->display_name}' revoked from user '{$user->name}'",
                'ip_address' => $request->ip()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Role removed successfully',
                'data' => [
                    'user' => $user->load('roles'),
                    'role' => $role
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove role'
            ], 500);
        }
    }
}
