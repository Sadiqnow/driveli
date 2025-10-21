<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Events\RoleUpdated;
use App\Events\UserRoleModified;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
        $this->middleware('rbac:permission,manage_roles')->except(['index', 'show']);
        $this->middleware('rbac:permission,view_roles')->only(['index', 'show']);
    }

    /**
     * Display a listing of the roles.
     */
    public function index()
    {
        $roles = Role::with(['permissions', 'users', 'parent', 'children'])
                    ->withCount(['users', 'permissions'])
                    ->orderBy('level', 'desc')
                    ->paginate(15);

        return view('admin.roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new role.
     */
    public function create()
    {
        $permissions = Permission::where('is_active', true)
                                ->orderBy('category')
                                ->orderBy('display_name')
                                ->get()
                                ->groupBy('category');

        return view('admin.roles.create', compact('permissions'));
    }

    /**
     * Store a newly created role in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:roles,name',
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'level' => 'required|integer|min:1|max:99', // Super admin level 100 is reserved
            'parent_id' => 'nullable|exists:roles,id',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id'
        ]);

        // Ensure user cannot create role higher than their level
        $currentUser = Auth::guard('admin')->user();
        if ($request->level >= $currentUser->getHighestRoleLevel() && !$currentUser->hasRole('super_admin')) {
            return back()->withErrors(['level' => 'You cannot create a role with level equal to or higher than your own.']);
        }

        DB::beginTransaction();
        try {
            $role = Role::create([
                'name' => str_replace(' ', '_', strtolower($request->name)),
                'display_name' => $request->display_name,
                'description' => $request->description,
                'level' => $request->level,
                'parent_id' => $request->parent_id,
                'is_active' => true
            ]);

            // Assign permissions if provided
            if ($request->permissions) {
                $permissions = Permission::whereIn('id', $request->permissions)->get();
                foreach ($permissions as $permission) {
                    $role->givePermission($permission, $currentUser);
                }
            }

            DB::commit();

            // Fire event for role creation
            event(new RoleUpdated($role, $currentUser, 'created'));

            return redirect()->route('admin.roles.index')
                           ->with('success', "Role '{$role->display_name}' created successfully.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to create role: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified role.
     */
    public function show(Role $role)
    {
        $role->load(['permissions', 'users.activeRoles']);
        
        return view('admin.roles.show', compact('role'));
    }

    /**
     * Show the form for editing the specified role.
     */
    public function edit(Role $role)
    {
        // Prevent editing super admin role
        if ($role->name === Role::SUPER_ADMIN) {
            return redirect()->route('admin.roles.index')
                           ->with('error', 'Super Admin role cannot be edited.');
        }

        $permissions = Permission::where('is_active', true)
                                ->orderBy('category')
                                ->orderBy('display_name')
                                ->get()
                                ->groupBy('category');

        $rolePermissions = $role->activePermissions()->pluck('permissions.id')->toArray();

        // Load role with relationships and counts for the view
        $role->load(['permissions', 'users']);
        $role->loadCount(['users', 'permissions']);

        return view('admin.roles.edit', compact('role', 'permissions', 'rolePermissions'));
    }

    /**
     * Update the specified role in storage.
     */
    public function update(Request $request, Role $role)
    {
        // Prevent editing super admin role
        if ($role->name === Role::SUPER_ADMIN) {
            return redirect()->route('admin.roles.index')
                           ->with('error', 'Super Admin role cannot be edited.');
        }

        $request->validate([
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'level' => 'required|integer|min:1|max:99',
            'parent_id' => 'nullable|exists:roles,id',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
            'is_active' => 'boolean'
        ]);

        $currentUser = Auth::guard('admin')->user();
        
        // Ensure user cannot set role level higher than their own
        if ($request->level >= $currentUser->getHighestRoleLevel() && !$currentUser->hasRole('super_admin')) {
            return back()->withErrors(['level' => 'You cannot set a role level equal to or higher than your own.']);
        }

        DB::beginTransaction();
        try {
            $role->update([
                'display_name' => $request->display_name,
                'description' => $request->description,
                'level' => $request->level,
                'parent_id' => $request->parent_id,
                'is_active' => $request->boolean('is_active', true)
            ]);

            // Update permissions
            if ($request->has('permissions')) {
                // Remove all current permissions
                $role->permissions()->updateExistingPivot($role->permissions()->pluck('permissions.id'), [
                    'is_active' => false,
                    'updated_at' => now()
                ]);

                // Add new permissions
                if ($request->permissions) {
                    $permissions = Permission::whereIn('id', $request->permissions)->get();
                    foreach ($permissions as $permission) {
                        $role->givePermission($permission, $currentUser);
                    }
                }
            }

            DB::commit();

            // Fire event for role update
            $changes = $role->getChanges();
            event(new RoleUpdated($role, $currentUser, 'updated', $changes));

            return redirect()->route('admin.roles.index')
                           ->with('success', "Role '{$role->display_name}' updated successfully.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to update role: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified role from storage.
     */
    public function destroy(Role $role)
    {
        // Prevent deletion of super admin role
        if ($role->name === Role::SUPER_ADMIN) {
            return response()->json([
                'success' => false,
                'message' => 'Super Admin role cannot be deleted.'
            ], 403);
        }

        // Check if role is assigned to users
        if ($role->users()->wherePivot('is_active', true)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete role that is assigned to users.'
            ], 400);
        }

        try {
            $role->delete();

            return response()->json([
                'success' => true,
                'message' => "Role '{$role->display_name}' deleted successfully."
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete role: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle role status
     */
    public function toggleStatus(Role $role)
    {
        if ($role->name === Role::SUPER_ADMIN) {
            return response()->json([
                'success' => false,
                'message' => 'Super Admin role status cannot be changed.'
            ], 403);
        }

        $role->update(['is_active' => !$role->is_active]);

        return response()->json([
            'success' => true,
            'message' => "Role '{$role->display_name}' " . ($role->is_active ? 'activated' : 'deactivated') . " successfully.",
            'is_active' => $role->is_active
        ]);
    }

    /**
     * Get permissions for a role (AJAX)
     */
    public function permissions(Role $role)
    {
        $permissions = $role->activePermissions()
                           ->select('permissions.*')
                           ->get()
                           ->groupBy('category');

        return response()->json([
            'success' => true,
            'data' => $permissions
        ]);
    }

    /**
     * API: Fetch all roles
     */
    public function apiIndex()
    {
        $roles = Role::active()
                    ->with(['parent', 'children'])
                    ->withCount(['users', 'permissions'])
                    ->orderBy('level', 'desc')
                    ->get();

        return response()->json([
            'success' => true,
            'data' => $roles
        ]);
    }

    /**
     * API: Get role hierarchy tree
     */
    public function hierarchy()
    {
        $roles = Role::active()
                    ->with(['children' => function ($query) {
                        $query->active()->with('children');
                    }])
                    ->whereNull('parent_id')
                    ->orderBy('level', 'desc')
                    ->get();

        return response()->json([
            'success' => true,
            'data' => $roles
        ]);
    }

    /**
     * API: Update role parent (set hierarchy)
     */
    public function setParent(Request $request, Role $role)
    {
        $request->validate([
            'parent_id' => 'nullable|exists:roles,id'
        ]);

        // Prevent circular references
        if ($request->parent_id) {
            $parentRole = Role::find($request->parent_id);
            if ($parentRole && ($parentRole->id === $role->id || $role->descendants()->where('id', $parentRole->id)->exists())) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot set parent role due to circular reference.'
                ], 400);
            }
        }

        $role->update(['parent_id' => $request->parent_id]);

        $currentUser = Auth::guard('admin')->user();

        // Clear permission caches for all users with this role
        $role->users()->each(function ($user) {
            $user->clearPermissionCache();
        });

        // Fire event for hierarchy change
        event(new RoleUpdated($role, $currentUser, 'hierarchy_changed', ['parent_id' => $request->parent_id]));

        return response()->json([
            'success' => true,
            'message' => 'Role hierarchy updated successfully.',
            'data' => $role->load('parent')
        ]);
    }

    /**
     * API: Assign permissions to a role
     */
    public function apiAssignPermissions(Request $request, Role $role)
    {
        $request->validate([
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,id'
        ]);

        $currentUser = Auth::guard('admin')->user();

        DB::beginTransaction();
        try {
            // Remove all current permissions
            $role->permissions()->updateExistingPivot($role->permissions()->pluck('permissions.id'), [
                'is_active' => false,
                'updated_at' => now()
            ]);

            // Add new permissions
            if ($request->permissions) {
                $permissions = Permission::whereIn('id', $request->permissions)->get();
                foreach ($permissions as $permission) {
                    $role->givePermission($permission, $currentUser);
                }
            }

            DB::commit();

            // Fire event for permission assignment
            event(new RoleUpdated($role, $currentUser, 'permissions_assigned', [
                'assigned_permissions' => $request->permissions
            ]));

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
            return response()->json([
                'success' => false,
                'message' => 'Failed to assign permissions: ' . $e->getMessage()
            ], 500);
        }
    }
}