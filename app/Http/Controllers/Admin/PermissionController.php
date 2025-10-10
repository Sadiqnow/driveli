<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PermissionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
        $this->middleware('rbac:permission,manage_permissions')->except(['index', 'show']);
        $this->middleware('rbac:permission,view_permissions')->only(['index', 'show']);
    }

    /**
     * Display a listing of the permissions.
     */
    public function index()
    {
        $permissions = Permission::with('roles')
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
        
        return view('admin.permissions.index', compact('permissions', 'categories'));
    }

    /**
     * Show the form for creating a new permission.
     */
    public function create()
    {
        $categories = Permission::getCategories();
        $actions = Permission::getActions();
        
        return view('admin.permissions.create', compact('categories', 'actions'));
    }

    /**
     * Store a newly created permission in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:permissions',
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'category' => 'required|string|max:50',
            'resource' => 'nullable|string|max:100',
            'action' => 'nullable|string|max:50',
            'is_active' => 'boolean',
            'meta' => 'nullable|array'
        ]);

        $permission = Permission::create([
            'name' => Str::slug($request->name, '_'),
            'display_name' => $request->display_name,
            'description' => $request->description,
            'category' => $request->category,
            'resource' => $request->resource,
            'action' => $request->action,
            'is_active' => $request->boolean('is_active', true),
            'meta' => $request->meta ?? []
        ]);

        return redirect()->route('admin.permissions.index')
                        ->with('success', 'Permission created successfully.');
    }

    /**
     * Display the specified permission.
     */
    public function show(Permission $permission)
    {
        $permission->load(['roles.users']);
        
        return view('admin.permissions.show', compact('permission'));
    }

    /**
     * Show the form for editing the specified permission.
     */
    public function edit(Permission $permission)
    {
        $categories = Permission::getCategories();
        $actions = Permission::getActions();
        
        return view('admin.permissions.edit', compact('permission', 'categories', 'actions'));
    }

    /**
     * Update the specified permission in storage.
     */
    public function update(Request $request, Permission $permission)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:permissions,name,' . $permission->id,
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'category' => 'required|string|max:50',
            'resource' => 'nullable|string|max:100',
            'action' => 'nullable|string|max:50',
            'is_active' => 'boolean',
            'meta' => 'nullable|array'
        ]);

        $permission->update([
            'name' => Str::slug($request->name, '_'),
            'display_name' => $request->display_name,
            'description' => $request->description,
            'category' => $request->category,
            'resource' => $request->resource,
            'action' => $request->action,
            'is_active' => $request->boolean('is_active'),
            'meta' => $request->meta ?? []
        ]);

        return redirect()->route('admin.permissions.index')
                        ->with('success', 'Permission updated successfully.');
    }

    /**
     * Remove the specified permission from storage.
     */
    public function destroy(Permission $permission)
    {
        // Check if permission is being used by any roles
        if ($permission->roles()->count() > 0) {
            return redirect()->route('admin.permissions.index')
                            ->with('error', 'Cannot delete permission that is assigned to roles.');
        }

        $permission->delete();

        return redirect()->route('admin.permissions.index')
                        ->with('success', 'Permission deleted successfully.');
    }

    /**
     * Toggle permission status
     */
    public function toggleStatus(Permission $permission)
    {
        $permission->update(['is_active' => !$permission->is_active]);
        
        $status = $permission->is_active ? 'activated' : 'deactivated';
        
        return response()->json([
            'success' => true,
            'message' => "Permission {$status} successfully.",
            'is_active' => $permission->is_active
        ]);
    }

    /**
     * Bulk actions for permissions
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:activate,deactivate,delete',
            'permissions' => 'required|array|min:1',
            'permissions.*' => 'exists:permissions,id'
        ]);

        $permissions = Permission::whereIn('id', $request->permissions);
        
        switch ($request->action) {
            case 'activate':
                $permissions->update(['is_active' => true]);
                $message = 'Permissions activated successfully.';
                break;
                
            case 'deactivate':
                $permissions->update(['is_active' => false]);
                $message = 'Permissions deactivated successfully.';
                break;
                
            case 'delete':
                // Check if any permission is being used
                $usedPermissions = $permissions->withCount('roles')
                                              ->having('roles_count', '>', 0)
                                              ->pluck('display_name');
                
                if ($usedPermissions->count() > 0) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cannot delete permissions that are assigned to roles: ' . $usedPermissions->join(', ')
                    ], 400);
                }
                
                $permissions->delete();
                $message = 'Permissions deleted successfully.';
                break;
        }

        return response()->json([
            'success' => true,
            'message' => $message
        ]);
    }
}