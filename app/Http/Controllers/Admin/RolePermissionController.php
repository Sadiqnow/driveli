<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RolePermissionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
        $this->middleware('SuperAdminDriverAccess');
    }

    /**
     * Assign role to user
     */
    public function assignRole(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:admin_users,id',
            'role_name' => 'required|string|exists:roles,name'
        ]);

        try {
            $user = \App\Models\AdminUser::findOrFail($request->user_id);
            $role = \App\Models\Role::where('name', $request->role_name)->firstOrFail();

            // Remove existing roles and assign new one
            $user->roles()->detach();
            $user->assignRole($role);

            // Log activity
            if (class_exists(\App\Services\ActivityLogger::class)) {
                \App\Services\ActivityLogger::log(
                    'role_assigned',
                    "Assigned role '{$role->display_name}' to user {$user->name}",
                    $user,
                    ['role_id' => $role->id, 'role_name' => $role->name]
                );
            }

            return response()->json([
                'success' => true,
                'message' => "Role '{$role->display_name}' assigned successfully to {$user->name}"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to assign role: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove role from user
     */
    public function removeRole(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:admin_users,id',
            'role_name' => 'required|string'
        ]);

        try {
            $user = \App\Models\AdminUser::findOrFail($request->user_id);
            $role = \App\Models\Role::where('name', $request->role_name)->first();

            if ($role) {
                $user->removeRole($role);

                // Log activity
                if (class_exists(\App\Services\ActivityLogger::class)) {
                    \App\Services\ActivityLogger::log(
                        'role_removed',
                        "Removed role '{$role->display_name}' from user {$user->name}",
                        $user,
                        ['role_id' => $role->id, 'role_name' => $role->name]
                    );
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Role removed successfully from {$user->name}"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove role: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sync user roles
     */
    public function manageUserRoles(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:admin_users,id',
            'role_ids' => 'required|array',
            'role_ids.*' => 'exists:roles,id',
            'notes' => 'nullable|string|max:1000'
        ]);

        try {
            DB::beginTransaction();

            $user = \App\Models\AdminUser::findOrFail($request->user_id);

            // Prevent super admin from modifying their own roles (security measure)
            if ($user->id === auth('admin')->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You cannot modify your own roles.'
                ], 403);
            }

            // Get current roles for logging
            $currentRoleIds = $user->roles()->pluck('roles.id')->toArray();
            $currentRoleNames = $user->roles()->pluck('roles.display_name')->toArray();

            // Sync roles using Eloquent sync() method
            $user->roles()->sync($request->role_ids);

            // Get new roles for logging
            $newRoleIds = $user->fresh()->roles()->pluck('roles.id')->toArray();
            $newRoleNames = $user->fresh()->roles()->pluck('roles.display_name')->toArray();

            // Calculate changes for detailed logging
            $addedRoles = array_diff($newRoleIds, $currentRoleIds);
            $removedRoles = array_diff($currentRoleIds, $newRoleIds);

            $changes = [
                'previous_roles' => $currentRoleNames,
                'new_roles' => $newRoleNames,
                'added_role_ids' => array_values($addedRoles),
                'removed_role_ids' => array_values($removedRoles),
                'notes' => $request->notes
            ];

            // Log activity
            if (class_exists(\App\Services\ActivityLogger::class)) {
                $action = 'roles_synced';
                $description = "Synced roles for user {$user->name}";
                if (!empty($addedRoles)) {
                    $description .= " - Added: " . implode(', ', array_intersect_key($newRoleNames, array_flip($addedRoles)));
                }
                if (!empty($removedRoles)) {
                    $description .= " - Removed: " . implode(', ', array_intersect_key($currentRoleNames, array_flip($removedRoles)));
                }

                \App\Services\ActivityLogger::log(
                    auth('admin')->user(),
                    $action,
                    $description,
                    $user,
                    null,
                    null,
                    $changes
                );
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Roles synced successfully for {$user->name}",
                'data' => [
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'previous_roles' => $currentRoleNames,
                    'current_roles' => $newRoleNames,
                    'added_count' => count($addedRoles),
                    'removed_count' => count($removedRoles)
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to sync roles: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Assign permissions to role
     */
    public function assignPermissionsToRole(Request $request)
    {
        $request->validate([
            'role_id' => 'required|exists:roles,id',
            'permission_ids' => 'required|array',
            'permission_ids.*' => 'exists:permissions,id',
            'notes' => 'nullable|string|max:1000'
        ]);

        try {
            DB::beginTransaction();

            $role = \App\Models\Role::findOrFail($request->role_id);

            // Get current permissions for logging
            $currentPermissionIds = $role->permissions()->pluck('permissions.id')->toArray();
            $currentPermissionNames = $role->permissions()->pluck('permissions.display_name')->toArray();

            // Sync permissions using Eloquent sync() method - this clears old permissions and assigns new ones
            $role->permissions()->sync($request->permission_ids);

            // Get new permissions for logging
            $newPermissionIds = $role->fresh()->permissions()->pluck('permissions.id')->toArray();
            $newPermissionNames = $role->fresh()->permissions()->pluck('permissions.display_name')->toArray();

            // Calculate changes for detailed logging
            $addedPermissions = array_diff($newPermissionIds, $currentPermissionIds);
            $removedPermissions = array_diff($currentPermissionIds, $newPermissionIds);

            $changes = [
                'previous_permissions' => $currentPermissionNames,
                'new_permissions' => $newPermissionNames,
                'added_permission_ids' => array_values($addedPermissions),
                'removed_permission_ids' => array_values($removedPermissions),
                'notes' => $request->notes
            ];

            // Log activity
            if (class_exists(\App\Services\ActivityLogger::class)) {
                $action = 'permissions_assigned';
                $description = "Assigned permissions to role '{$role->display_name}'";
                if (!empty($addedPermissions)) {
                    $description .= " - Added: " . implode(', ', array_intersect_key($newPermissionNames, array_flip($addedPermissions)));
                }
                if (!empty($removedPermissions)) {
                    $description .= " - Removed: " . implode(', ', array_intersect_key($currentPermissionNames, array_flip($removedPermissions)));
                }

                \App\Services\ActivityLogger::log(
                    auth('admin')->user(),
                    $action,
                    $description,
                    $role,
                    null,
                    null,
                    $changes
                );
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Permissions assigned successfully to role '{$role->display_name}'",
                'data' => [
                    'role_id' => $role->id,
                    'role_name' => $role->display_name,
                    'previous_permissions' => $currentPermissionNames,
                    'current_permissions' => $newPermissionNames,
                    'added_count' => count($addedPermissions),
                    'removed_count' => count($removedPermissions)
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to assign permissions to role: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show user roles management page
     */
    public function userRoles()
    {
        return view('superadmin.users.roles');
    }
}
