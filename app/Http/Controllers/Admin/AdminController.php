<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use App\Models\Role;
use App\Models\Permission;
use App\Models\UserActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    /**
     * Constructor - Add middleware for Superadmin access only
     */
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            // Check if user is authenticated as admin
            if (!auth('admin')->check()) {
                return redirect()->route('admin.login')->with('error', 'Please login to access this area.');
            }

            // Check if user has Super Admin role
            $user = auth('admin')->user();
            if (!$user || !$user->hasRole('Super Admin')) {
                abort(403, 'Access denied. Super Administrator privileges required.');
            }

            return $next($request);
        });
    }

    /**
     * Display a listing of admins.
     */
    public function index(Request $request)
    {
        $query = AdminUser::with(['roles', 'activeRoles']);

        // Search functionality
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', '%' . $search . '%')
                  ->orWhere('email', 'LIKE', '%' . $search . '%');
            });
        }

        // Role filter
        if ($request->filled('role')) {
            $query->whereHas('activeRoles', function($q) use ($request) {
                $q->where('name', $request->role);
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Sort and paginate
        $admins = $query->orderBy('created_at', 'desc')->paginate(15);

        // Get available roles for filter
        $availableRoles = Role::active()->pluck('display_name', 'name');

        // Statistics
        $stats = [
            'total' => AdminUser::count(),
            'active' => AdminUser::where('status', 'Active')->count(),
            'inactive' => AdminUser::where('status', 'Inactive')->count(),
            'super_admins' => AdminUser::whereHas('activeRoles', function($q) {
                $q->where('name', Role::SUPER_ADMIN);
            })->count(),
        ];

        return view('superadmin.admins.index', compact('admins', 'availableRoles', 'stats'));
    }

    /**
     * Show the form for creating a new admin.
     */
    public function create()
    {
        $roles = Role::active()->get();
        $permissions = Permission::active()->get()->groupBy('category');

        return view('superadmin.admins.create', compact('roles', 'permissions'));
    }

    /**
     * Store a newly created admin.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|regex:/^[a-zA-Z\s]+$/',
            'email' => 'required|email|unique:admin_users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|exists:roles,name',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,name',
            'status' => 'required|in:Active,Inactive',
        ]);

        try {
            DB::beginTransaction();

            // Create admin user
            $admin = AdminUser::create([
                'name' => trim($request->name),
                'email' => strtolower(trim($request->email)),
                'password' => Hash::make($request->password),
                'status' => $request->status,
            ]);

            // Assign role
            $role = Role::where('name', $request->role)->first();
            if ($role) {
                $admin->assignRole($role);
            }

            // Assign permissions
            if ($request->has('permissions') && !empty($request->permissions)) {
                $admin->syncPermissions($request->permissions);
            }

            // Log activity
            UserActivity::log(
                'create_admin',
                "Superadmin created new admin user: {$admin->name}",
                $admin,
                null,
                ['assigned_by' => auth('admin')->id()]
            );

            DB::commit();

            return redirect()->route('superadmin.admins.index')
                           ->with('success', 'Admin user created successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to create admin user: ' . $e->getMessage());

            return back()->withInput()->withErrors([
                'error' => 'Failed to create admin user. Please try again.'
            ]);
        }
    }

    /**
     * Show the form for editing the specified admin.
     */
    public function edit(AdminUser $admin)
    {
        // Prevent editing self if it's the last super admin
        $currentUser = auth('admin')->user();
        if ($admin->id === $currentUser->id) {
            $superAdminCount = AdminUser::whereHas('activeRoles', function($q) {
                $q->where('name', Role::SUPER_ADMIN);
            })->where('status', 'Active')->count();

            if ($superAdminCount <= 1) {
                return back()->with('error', 'Cannot edit the last active Super Administrator.');
            }
        }

        $roles = Role::active()->get();
        $permissions = Permission::active()->get()->groupBy('category');

        $currentRole = $admin->activeRoles()->first();
        $currentPermissions = $admin->getAllPermissions();

        return view('superadmin.admins.edit', compact('admin', 'roles', 'permissions', 'currentRole', 'currentPermissions'));
    }

    /**
     * Update the specified admin.
     */
    public function update(Request $request, AdminUser $admin)
    {
        $request->validate([
            'name' => 'required|string|max:255|regex:/^[a-zA-Z\s]+$/',
            'email' => ['required', 'email', Rule::unique('admin_users')->ignore($admin->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'required|exists:roles,name',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,name',
            'status' => 'required|in:Active,Inactive',
        ]);

        try {
            DB::beginTransaction();

            $originalData = $admin->toArray();

            // Update basic info
            $updateData = [
                'name' => trim($request->name),
                'email' => strtolower(trim($request->email)),
                'status' => $request->status,
            ];

            if ($request->filled('password')) {
                $updateData['password'] = Hash::make($request->password);
            }

            $admin->update($updateData);

            // Update role
            $newRole = Role::where('name', $request->role)->first();
            if ($newRole) {
                $admin->syncRoles([$newRole]);
            }

            // Update permissions
            $admin->syncPermissions($request->permissions ?? []);

            // Log activity
            UserActivity::log(
                'update_admin',
                "Superadmin updated admin user: {$admin->name}",
                $admin,
                $originalData,
                $admin->toArray(),
                ['updated_by' => auth('admin')->id()]
            );

            DB::commit();

            return redirect()->route('superadmin.admins.index')
                           ->with('success', 'Admin user updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to update admin user: ' . $e->getMessage());

            return back()->withInput()->withErrors([
                'error' => 'Failed to update admin user. Please try again.'
            ]);
        }
    }

    /**
     * Remove the specified admin.
     */
    public function destroy(AdminUser $admin)
    {
        // Prevent deleting self
        if ($admin->id === auth('admin')->id()) {
            return back()->with('error', 'You cannot delete your own account!');
        }

        // Prevent deleting last super admin
        if ($admin->hasRole(Role::SUPER_ADMIN)) {
            $superAdminCount = AdminUser::whereHas('activeRoles', function($q) {
                $q->where('name', Role::SUPER_ADMIN);
            })->where('status', 'Active')->count();

            if ($superAdminCount <= 1) {
                return back()->with('error', 'Cannot delete the last active Super Administrator!');
            }
        }

        try {
            DB::beginTransaction();

            $adminName = $admin->name;

            // Log before deletion
            UserActivity::log(
                'delete_admin',
                "Superadmin deleted admin user: {$adminName}",
                $admin,
                ['deleted_by' => auth('admin')->id()]
            );

            $admin->delete();

            DB::commit();

            return redirect()->route('superadmin.admins.index')
                           ->with('success', 'Admin user deleted successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete admin user: ' . $e->getMessage());

            return back()->with('error', 'Failed to delete admin user. Please try again.');
        }
    }

    /**
     * Get permissions for a specific role (AJAX)
     */
    public function getRolePermissions(Request $request)
    {
        $roleName = $request->get('role');
        $role = Role::where('name', $roleName)->first();

        if (!$role) {
            return response()->json(['permissions' => []]);
        }

        $permissions = $role->activePermissions()->pluck('name')->toArray();

        return response()->json(['permissions' => $permissions]);
    }
}
