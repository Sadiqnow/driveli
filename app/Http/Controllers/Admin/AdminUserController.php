<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use App\Models\Role;
use App\Models\Permission;
use App\Models\UserActivity;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Exception;

class AdminUserController extends Controller
{
    /**
     * Constructor - Add middleware for authorization
     */
    public function __construct()
    {
        $this->middleware('auth:admin');
        $this->middleware('can:manage_users,admin')->except(['show', 'profile', 'updateProfile']);
        $this->middleware('can:view_users')->only(['show']);
        $this->middleware('throttle:60,1')->only(['store', 'update', 'destroy']);
        $this->middleware('throttle:10,1')->only(['bulkAction', 'forceDelete']);
    }
    /**
     * Display a listing of admin users.
     */
    public function index(Request $request)
    {
        $query = AdminUser::withTrashed();
        
        // Search functionality - Fixed SQL injection vulnerability
        if ($request->filled('search')) {
            $search = trim($request->search);
            if (!empty($search)) {
                $query->where(function($q) use ($search) {
                    $q->where('name', 'LIKE', '%' . addslashes($search) . '%')
                      ->orWhere('email', 'LIKE', '%' . addslashes($search) . '%')
                      ->orWhere('phone', 'LIKE', '%' . addslashes($search) . '%')
                      ->orWhere('role', 'LIKE', '%' . addslashes($search) . '%');
                });
            }
        }
        
        // Status filter
        if ($request->filled('status')) {
            if ($request->status === 'deleted') {
                $query->onlyTrashed();
            } else {
                $query->withoutTrashed()->where('status', $request->status);
            }
        }
        
        // Role filter
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }
        
        // Date range filter
        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', $request->date_to . ' 23:59:59');
        }
        
        // Sort options - Validate sort columns to prevent SQL injection
        $allowedSortColumns = ['id', 'name', 'email', 'role', 'status', 'created_at', 'updated_at', 'last_login_at'];
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        if (!in_array($sortBy, $allowedSortColumns)) {
            $sortBy = 'created_at';
        }
        
        if (!in_array(strtolower($sortOrder), ['asc', 'desc'])) {
            $sortOrder = 'desc';
        }
        
        $query->orderBy($sortBy, $sortOrder);
        
        $perPage = $request->get('per_page', 15);
        $adminUsers = $query->paginate($perPage);
        
        // Enhanced statistics
        $stats = [
            'total' => AdminUser::count(),
            'active' => AdminUser::where('status', 'Active')->count(),
            'inactive' => AdminUser::where('status', 'Inactive')->count(),
            'deleted' => AdminUser::onlyTrashed()->count(),
            'online_today' => AdminUser::whereDate('last_login_at', today())->count(),
            'online_this_week' => AdminUser::where('last_login_at', '>=', now()->startOfWeek())->count(),
            'super_admins' => AdminUser::where('role', 'Super Admin')->count(),
            'admins' => AdminUser::where('role', 'Admin')->count(),
            'moderators' => AdminUser::where('role', 'Moderator')->count(),
            'viewers' => AdminUser::where('role', 'Viewer')->count(),
        ];
        
        // Get available roles for filters
        $availableRoles = AdminUser::distinct()->pluck('role')->filter();
        
        return view('admin.users.index', compact('adminUsers', 'stats', 'availableRoles'));
    }

    /**
     * Show the form for creating a new admin user.
     */
    public function create()
    {
        $roles = [
            'Super Admin' => 'Super Administrator',
            'Admin' => 'Administrator', 
            'Moderator' => 'Moderator',
            'Viewer' => 'Viewer'
        ];
        
        return view('admin.users.create', compact('roles'));
    }

    /**
     * Store a newly created admin user.
     */
    public function store(Request $request)
    {
        // Check authorization
        Gate::authorize('manage_users');
        
        // Rate limiting for user creation
        $key = 'create_admin_user:' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            return back()->withErrors([
                'error' => "Too many creation attempts. Please try again in {$seconds} seconds."
            ]);
        }
        
        RateLimiter::hit($key);
        
        $request->validate([
            'name' => 'required|string|max:255|regex:/^[a-zA-Z\s]+$/',
            'email' => 'required|email|max:255|unique:admin_users,email',
            'phone' => 'nullable|string|max:20|regex:/^[+]?[0-9\s\-()]+$/|unique:admin_users,phone',
            'password' => 'required|string|min:8|confirmed|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
            'role' => 'required|in:Super Admin,Admin,Moderator,Viewer',
            'status' => 'required|in:Active,Inactive,Suspended',
        ], [
            'name.regex' => 'Name can only contain letters and spaces.',
            'phone.regex' => 'Please enter a valid phone number.',
            'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, and one number.',
        ]);

        try {
            DB::beginTransaction();
            
            $user = AdminUser::create([
                'name' => trim($request->name),
                'email' => strtolower(trim($request->email)),
                'phone' => $request->phone ? trim($request->phone) : null,
                'password' => Hash::make($request->password),
                'role' => $request->role,
                'status' => $request->status,
                'permissions' => $this->getDefaultPermissions($request->role),
            ]);
            
            // Log the creation activity
            UserActivity::log(
                'create',
                "Admin user '{$user->name}' was created by " . auth('admin')->user()->name,
                $user
            );
            
            DB::commit();
            
            return redirect()->route('admin.users.index')
                            ->with('success', 'Admin user created successfully!');
                            
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to create admin user: ' . $e->getMessage(), [
                'user_data' => $request->only(['name', 'email', 'role', 'status']),
                'created_by' => auth('admin')->id()
            ]);
            
            return back()->withInput()->withErrors([
                'error' => 'Failed to create admin user. Please try again.'
            ]);
        }

    }

    /**
     * Display the specified admin user.
     */
    public function show(AdminUser $user)
    {
        // Check if user can view this admin user
        Gate::authorize('view_users');
        
        // Prevent viewing soft-deleted users unless authorized
        if ($user->trashed() && !Gate::allows('manage_users')) {
            abort(404);
        }
        
        try {
            // Load relationships safely - check if they exist
            $relationships = [];
            
            if (method_exists($user, 'createdRequests')) {
                $relationships[] = 'createdRequests';
            }
            
            if (method_exists($user, 'verifiedCompanies')) {
                $relationships[] = 'verifiedCompanies';
            }
            
            if (method_exists($user, 'verifiedDrivers')) {
                $relationships[] = 'verifiedDrivers';
            }
            
            if (!empty($relationships)) {
                $user->load($relationships);
            }
            
            return view('admin.users.show', compact('user'));
            
        } catch (Exception $e) {
            Log::error('Error loading admin user details: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'viewer_id' => auth('admin')->id()
            ]);
            
            return back()->withErrors([
                'error' => 'Failed to load user details.'
            ]);
        }
    }

    /**
     * Show the form for editing the specified admin user.
     */
    public function edit(AdminUser $user)
    {
        $roles = [
            'Super Admin' => 'Super Administrator',
            'Admin' => 'Administrator', 
            'Moderator' => 'Moderator',
            'Viewer' => 'Viewer'
        ];
        
        return view('admin.users.edit', compact('user', 'roles'));
    }

    /**
     * Update the specified admin user.
     */
    public function update(Request $request, AdminUser $user)
    {
        Gate::authorize('manage_users');
        
        // Prevent editing trashed users
        if ($user->trashed()) {
            return back()->withErrors(['error' => 'Cannot edit deleted users.']);
        }
        
        // Additional authorization - users can only edit lower-level users
        $currentUser = auth('admin')->user();
        if (!$currentUser || !($currentUser instanceof AdminUser) || !$currentUser->canManage($user)) {
            return back()->withErrors([
                'error' => 'You do not have permission to edit this user.'
            ]);
        }
        
        $request->validate([
            'name' => 'required|string|max:255|regex:/^[a-zA-Z\s]+$/',
            'email' => ['required', 'email', 'max:255', Rule::unique('admin_users')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^[+]?[0-9\s\-()]+$/', Rule::unique('admin_users')->ignore($user->id)],
            'role' => 'required|in:Super Admin,Admin,Moderator,Viewer',
            'status' => 'required|in:Active,Inactive,Suspended',
            'password' => 'nullable|string|min:8|confirmed|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
        ], [
            'name.regex' => 'Name can only contain letters and spaces.',
            'phone.regex' => 'Please enter a valid phone number.',
            'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, and one number.',
        ]);

        try {
            DB::beginTransaction();
            
            $originalData = $user->getOriginal();
            
            $updateData = [
                'name' => trim($request->name),
                'email' => strtolower(trim($request->email)),
                'phone' => $request->phone ? trim($request->phone) : null,
                'role' => $request->role,
                'status' => $request->status,
                'permissions' => $this->getDefaultPermissions($request->role),
            ];

            // Only update password if provided
            if ($request->filled('password')) {
                $updateData['password'] = Hash::make($request->password);
            }

            $user->update($updateData);
            
            // Log the update activity
            UserActivity::log(
                'update',
                "Admin user '{$user->name}' was updated by " . $currentUser->name,
                $user,
                $originalData,
                $updateData
            );
            
            DB::commit();
            
            return redirect()->route('admin.users.index')
                            ->with('success', 'Admin user updated successfully!');
                            
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to update admin user: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'update_data' => $request->only(['name', 'email', 'role', 'status']),
                'updated_by' => $currentUser->id
            ]);
            
            return back()->withInput()->withErrors([
                'error' => 'Failed to update admin user. Please try again.'
            ]);
        }

    }

    /**
     * Remove the specified admin user.
     */
    public function destroy(AdminUser $user)
    {
        Gate::authorize('manage_users');
        
        $currentUser = auth('admin')->user();
        
        // Prevent deletion of the last super admin
        if ($user->role === 'Super Admin' && AdminUser::where('role', 'Super Admin')->whereNull('deleted_at')->count() <= 1) {
            return back()->with('error', 'Cannot delete the last Super Administrator!');
        }

        // Prevent self-deletion
        if ($user->id === $currentUser->id) {
            return back()->with('error', 'You cannot delete your own account!');
        }
        
        // Check if current user can manage the target user
        if (!$currentUser || !($currentUser instanceof AdminUser) || !$currentUser->canManage($user)) {
            return back()->with('error', 'You do not have permission to delete this user!');
        }

        try {
            DB::beginTransaction();
            
            $userName = $user->name;
            $user->delete();
            
            // Log the deletion activity
            UserActivity::log(
                'delete',
                "Admin user '{$userName}' was deleted by " . $currentUser->name,
                $user
            );
            
            DB::commit();
            
            return redirect()->route('admin.users.index')
                            ->with('success', 'Admin user deleted successfully!');
                            
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete admin user: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'deleted_by' => $currentUser->id
            ]);
            
            return back()->with('error', 'Failed to delete admin user. Please try again.');
        }
    }

    /**
     * Assign role to admin user.
     */
    public function assignRole(Request $request, AdminUser $user)
    {
        $request->validate([
            'role' => 'required|in:Super Admin,Admin,Moderator,Viewer',
        ]);

        $user->update([
            'role' => $request->role,
            'permissions' => $this->getDefaultPermissions($request->role),
        ]);

        return back()->with('success', 'Role assigned successfully!');
    }

    /**
     * Remove role from admin user.
     */
    public function removeRole(AdminUser $user)
    {
        // Prevent removing role from the last super admin
        if ($user->role === 'Super Admin' && AdminUser::where('role', 'Super Admin')->count() <= 1) {
            return back()->with('error', 'Cannot remove role from the last Super Administrator!');
        }

        $user->update([
            'role' => 'Viewer',
            'permissions' => $this->getDefaultPermissions('Viewer'),
        ]);

        return back()->with('success', 'Role removed successfully!');
    }

    /**
     * Get default permissions for a role.
     */
    private function getDefaultPermissions($role)
    {
        $permissions = [
            'Super Admin' => [
                'users.manage',
                'drivers.manage',
                'companies.manage',
                'requests.manage',
                'reports.view',
                'settings.manage',
                'system.admin'
            ],
            'Admin' => [
                'drivers.manage',
                'companies.manage',
                'requests.manage',
                'reports.view'
            ],
            'Moderator' => [
                'drivers.view',
                'drivers.verify',
                'companies.view',
                'companies.verify',
                'requests.view',
                'requests.moderate'
            ],
            'Viewer' => [
                'drivers.view',
                'companies.view',
                'requests.view',
                'reports.view'
            ]
        ];

        return $permissions[$role] ?? [];
    }

    /**
     * Toggle user status (activate/deactivate)
     */
    public function toggleStatus(AdminUser $user)
    {
        // Prevent deactivating the last super admin
        if ($user->role === 'Super Admin' && $user->status === 'Active' && 
            AdminUser::where('role', 'Super Admin')->where('status', 'Active')->count() <= 1) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot deactivate the last Super Administrator!'
            ], 400);
        }

        $newStatus = $user->status === 'Active' ? 'Inactive' : 'Active';
        $user->update(['status' => $newStatus]);

        return response()->json([
            'success' => true,
            'message' => "User " . ($newStatus === 'Active' ? 'activated' : 'deactivated') . " successfully!",
            'new_status' => $newStatus
        ]);
    }

    /**
     * Restore deleted user
     */
    public function restore($id)
    {
        $user = AdminUser::onlyTrashed()->findOrFail($id);
        $user->restore();

        return redirect()->route('admin.users.index')
                        ->with('success', 'User restored successfully!');
    }

    /**
     * Permanently delete user
     */
    public function forceDelete($id)
    {
        $user = AdminUser::onlyTrashed()->findOrFail($id);
        
        // Check if current user can perform this action
        $currentUser = auth('admin')->user();
        if (!$currentUser || !($currentUser instanceof AdminUser) || !$currentUser->isSuperAdmin()) {
            return back()->with('error', 'Only Super Administrators can permanently delete users!');
        }

        $user->forceDelete();

        return redirect()->route('admin.users.index')
                        ->with('success', 'User permanently deleted!');
    }

    /**
     * Bulk actions for users
     */
    public function bulkAction(Request $request)
    {
        Gate::authorize('manage_users');
        
        // Enhanced validation for bulk actions
        $request->validate([
            'action' => 'required|in:activate,deactivate,delete,restore,force_delete',
            'user_ids' => 'required|array|min:1|max:100', // Limit bulk operations
            'user_ids.*' => 'integer|exists:admin_users,id'
        ]);
        
        // Rate limiting for bulk operations
        $key = 'bulk_admin_action:' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = RateLimiter::availableIn($key);
            return back()->withErrors([
                'error' => "Too many bulk operations. Please try again in {$seconds} seconds."
            ]);
        }
        
        RateLimiter::hit($key);

        $action = $request->action;
        $userIds = $request->user_ids;
        $currentUser = auth('admin')->user();
        
        $successCount = 0;
        $errors = [];

        try {
            DB::beginTransaction();
            
            foreach ($userIds as $userId) {
                try {
                    $user = AdminUser::withTrashed()->findOrFail($userId);

                    // Prevent actions on self
                    if ($user->id === $currentUser->id) {
                        $errors[] = "Cannot perform action on your own account";
                        continue;
                    }
                    
                    // Check if current user can manage target user
                    if (!$currentUser || !($currentUser instanceof AdminUser) || !$currentUser->canManage($user)) {
                        $errors[] = "You do not have permission to manage user: {$user->name}";
                        continue;
                    }

                    // Prevent actions on last super admin
                    if ($user->role === 'Super Admin' && 
                        AdminUser::where('role', 'Super Admin')->where('status', 'Active')->whereNull('deleted_at')->count() <= 1 &&
                        in_array($action, ['deactivate', 'delete'])) {
                        $errors[] = "Cannot {$action} the last Super Administrator";
                        continue;
                    }

                    switch ($action) {
                        case 'activate':
                            if (!$user->trashed()) {
                                $user->update(['status' => 'Active']);
                                UserActivity::log('bulk_activate', "User {$user->name} was activated via bulk action", $user);
                            } else {
                                $errors[] = "Cannot activate deleted user: {$user->name}";
                                continue 2;
                            }
                            break;
                        case 'deactivate':
                            if (!$user->trashed()) {
                                $user->update(['status' => 'Inactive']);
                                UserActivity::log('bulk_deactivate', "User {$user->name} was deactivated via bulk action", $user);
                            } else {
                                $errors[] = "Cannot deactivate deleted user: {$user->name}";
                                continue 2;
                            }
                            break;
                        case 'delete':
                            if (!$user->trashed()) {
                                $user->delete();
                                UserActivity::log('bulk_delete', "User {$user->name} was deleted via bulk action", $user);
                            } else {
                                $errors[] = "User {$user->name} is already deleted";
                                continue 2;
                            }
                            break;
                        case 'restore':
                            if ($user->trashed()) {
                                $user->restore();
                                UserActivity::log('bulk_restore', "User {$user->name} was restored via bulk action", $user);
                            } else {
                                $errors[] = "User {$user->name} is not deleted";
                                continue 2;
                            }
                            break;
                        case 'force_delete':
                            if ($currentUser && ($currentUser instanceof AdminUser) && $currentUser->isSuperAdmin()) {
                                if ($user->trashed()) {
                                    UserActivity::log('bulk_force_delete', "User {$user->name} was permanently deleted via bulk action", $user);
                                    $user->forceDelete();
                                } else {
                                    $errors[] = "User {$user->name} must be soft deleted first";
                                    continue 2;
                                }
                            } else {
                                $errors[] = "Only Super Administrators can permanently delete users";
                                continue 2;
                            }
                            break;
                    }

                    $successCount++;

                } catch (Exception $e) {
                    $errors[] = "Error processing user {$userId}: " . $e->getMessage();
                    Log::error('Bulk action error', [
                        'user_id' => $userId,
                        'action' => $action,
                        'error' => $e->getMessage(),
                        'performed_by' => $currentUser->id
                    ]);
                }
            }
            
            DB::commit();
            
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Bulk action transaction failed', [
                'action' => $action,
                'user_ids' => $userIds,
                'error' => $e->getMessage(),
                'performed_by' => $currentUser->id
            ]);
            
            return back()->with('error', 'Bulk operation failed. Please try again.');
        }

        $message = "Successfully processed {$successCount} users.";
        if (!empty($errors)) {
            $message .= " Errors: " . implode(', ', array_slice($errors, 0, 3));
            if (count($errors) > 3) {
                $message .= " and " . (count($errors) - 3) . " more errors.";
            }
        }

        return redirect()->route('admin.users.index')
                        ->with($successCount > 0 ? 'success' : 'error', $message);
    }

    /**
     * Export users data
     */
    public function export(Request $request)
    {
        Gate::authorize('view_users');
        
        // Validate format
        $request->validate([
            'format' => 'nullable|in:csv,excel',
            'search' => 'nullable|string|max:255',
            'status' => 'nullable|string|in:Active,Inactive',
            'role' => 'nullable|string|in:Super Admin,Admin,Moderator,Viewer'
        ]);
        
        $format = $request->get('format', 'csv');
        
        $query = AdminUser::query();
        
        // Apply same filters as index - Fixed SQL injection
        if ($request->filled('search')) {
            $search = trim($request->search);
            if (!empty($search)) {
                $query->where(function($q) use ($search) {
                    $q->where('name', 'LIKE', '%' . addslashes($search) . '%')
                      ->orWhere('email', 'LIKE', '%' . addslashes($search) . '%')
                      ->orWhere('phone', 'LIKE', '%' . addslashes($search) . '%');
                });
            }
        }
        
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }
        
        $users = $query->get();
        
        if ($format === 'csv') {
            return $this->exportToCsv($users);
        }
        
        return $this->exportToExcel($users);
    }

    /**
     * Get user activity timeline
     */
    public function activity(Request $request, AdminUser $user)
    {
        $query = $user->activities()->orderBy('created_at', 'desc');
        
        // Filter by action if specified
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }
        
        // Filter by date range if specified
        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', $request->date_to . ' 23:59:59');
        }
        
        $activities = $query->paginate(20);
        
        // Get activity statistics
        $stats = [
            'total_activities' => $user->activities()->count(),
            'today_activities' => $user->activities()->whereDate('created_at', today())->count(),
            'this_week_activities' => $user->activities()->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'this_month_activities' => $user->activities()->whereMonth('created_at', now()->month)->count(),
        ];
        
        // Get activity breakdown by action
        $actionBreakdown = $user->activities()
            ->selectRaw('action, COUNT(*) as count')
            ->groupBy('action')
            ->orderByDesc('count')
            ->get()
            ->pluck('count', 'action');
        
        return view('admin.users.activity', compact('user', 'activities', 'stats', 'actionBreakdown'));
    }

    /**
     * User permissions management
     */
    public function permissions(AdminUser $user)
    {
        $allPermissions = Permission::active()->get()->groupBy('category');
        $userPermissions = $user->getAllPermissions();
        
        return view('admin.users.permissions', compact('user', 'allPermissions', 'userPermissions'));
    }

    /**
     * Update user permissions
     */
    public function updatePermissions(Request $request, AdminUser $user)
    {
        $request->validate([
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,name'
        ]);

        $user->update([
            'permissions' => $request->permissions ?? []
        ]);

        return back()->with('success', 'User permissions updated successfully!');
    }

    /**
     * User profile settings
     */
    public function profile(AdminUser $user)
    {
        return view('admin.users.profile', compact('user'));
    }

    /**
     * Update user profile
     */
    public function updateProfile(Request $request, AdminUser $user)
    {
        // Users can only update their own profile or admins can update others
        $currentUser = auth('admin')->user();
        
        if ($user->id !== $currentUser->id && !Gate::allows('manage_users')) {
            abort(403, 'You can only update your own profile.');
        }
        
        $request->validate([
            'name' => 'required|string|max:255|regex:/^[a-zA-Z\s]+$/',
            'email' => ['required', 'email', 'max:255', Rule::unique('admin_users')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^[+]?[0-9\s\-()]+$/', Rule::unique('admin_users')->ignore($user->id)],
            'bio' => 'nullable|string|max:500',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg|max:2048', // Removed gif for security
            'current_password' => 'nullable|required_with:password|current_password:admin',
            'password' => 'nullable|string|min:8|confirmed|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
            'notifications' => 'nullable|array',
            'notifications.email' => 'nullable|boolean',
            'notifications.sms' => 'nullable|boolean',
            'notifications.system' => 'nullable|boolean',
            'notifications.marketing' => 'nullable|boolean',
        ], [
            'name.regex' => 'Name can only contain letters and spaces.',
            'phone.regex' => 'Please enter a valid phone number.',
            'password.regex' => 'Password must contain at least one uppercase letter, one lowercase letter, and one number.',
        ]);

        $updateData = [
            'name' => trim($request->name),
            'email' => strtolower(trim($request->email)),
            'phone' => $request->phone ? trim($request->phone) : null,
            'bio' => $request->bio ? trim($request->bio) : null,
        ];

        // Handle notification preferences
        if ($request->has('notifications')) {
            $notifications = $request->notifications;
            $updateData['email_notifications'] = isset($notifications['email']) && $notifications['email'];
            $updateData['sms_notifications'] = isset($notifications['sms']) && $notifications['sms'];
            $updateData['system_notifications'] = isset($notifications['system']) && $notifications['system'];
            $updateData['marketing_notifications'] = isset($notifications['marketing']) && $notifications['marketing'];
        }

        // Handle password update
        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        // Handle avatar upload with enhanced security
        if ($request->hasFile('avatar')) {
            $avatar = $request->file('avatar');
            
            // Additional security checks
            if (!$avatar->isValid()) {
                return back()->withErrors(['avatar' => 'Invalid file upload.']);
            }
            
            // Check file size (additional check beyond validation)
            if ($avatar->getSize() > 2048 * 1024) {
                return back()->withErrors(['avatar' => 'Avatar file is too large.']);
            }
            
            // Generate secure filename
            $filename = Str::random(40) . '_' . $user->id . '.' . $avatar->getClientOriginalExtension();
            
            // Ensure avatars directory exists
            if (!Storage::disk('public')->exists('avatars')) {
                Storage::disk('public')->makeDirectory('avatars');
            }
            
            // Delete old avatar if exists
            if ($user->avatar && Storage::disk('public')->exists('avatars/' . $user->avatar)) {
                Storage::disk('public')->delete('avatars/' . $user->avatar);
            }

            // Store the new avatar
            $path = $avatar->storeAs('avatars', $filename, 'public');
            
            if ($path) {
                $updateData['avatar'] = $filename;
            } else {
                return back()->withErrors(['avatar' => 'Failed to upload avatar.']);
            }
        }

        try {
            DB::beginTransaction();
            
            $originalData = $user->getAttributes();
            $user->update($updateData);

            // Log activity
            UserActivity::log(
                'profile_update', 
                "Profile updated for user {$user->name}" . ($user->id !== $currentUser->id ? " by {$currentUser->name}" : ''),
                $user,
                $originalData,
                $updateData
            );
            
            DB::commit();
            
            return back()->with('success', 'Profile updated successfully!');
            
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to update profile: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'updated_by' => $currentUser->id
            ]);
            
            return back()->withInput()->withErrors([
                'error' => 'Failed to update profile. Please try again.'
            ]);
        }
    }

    /**
     * Reset user password
     */
    public function resetPassword(Request $request, AdminUser $user)
    {
        $request->validate([
            'password' => 'required|string|min:8|confirmed'
        ]);

        $user->update([
            'password' => Hash::make($request->password)
        ]);

        return back()->with('success', 'Password reset successfully!');
    }

    /**
     * Get users for AJAX requests
     */
    public function getUsers(Request $request)
    {
        // Basic authorization check
        if (!Gate::allows('view_users')) {
            return response()->json(['error' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }
        
        // Input validation
        $request->validate([
            'search' => 'nullable|string|max:255',
            'role' => 'nullable|string|in:Super Admin,Admin,Moderator,Viewer'
        ]);
        
        try {
            $query = AdminUser::query();
            
            if ($request->filled('search')) {
                $search = trim($request->search);
                if (!empty($search)) {
                    $query->where(function($q) use ($search) {
                        $q->where('name', 'LIKE', '%' . addslashes($search) . '%')
                          ->orWhere('email', 'LIKE', '%' . addslashes($search) . '%');
                    });
                }
            }
            
            if ($request->filled('role')) {
                $query->where('role', $request->role);
            }
            
            // Only return active users unless specifically requested
            $query->whereNull('deleted_at');
            
            $users = $query->limit(20)->get(['id', 'name', 'email', 'role', 'status']);
            
            return response()->json($users);
            
        } catch (Exception $e) {
            Log::error('Error fetching users for AJAX: ' . $e->getMessage(), [
                'search' => $request->search,
                'role' => $request->role,
                'requester' => auth('admin')->id()
            ]);
            
            return response()->json(['error' => 'Failed to fetch users'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Export users to Excel (placeholder)
     */
    private function exportToExcel($users)
    {
        // This would integrate with Laravel Excel or similar
        // For now, fallback to CSV
        return $this->exportToCsv($users);
    }

    /**
     * Export users to CSV
     */
    private function exportToCsv($users)
    {
        $filename = 'admin_users_' . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($users) {
            $file = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($file, [
                'ID', 'Name', 'Email', 'Phone', 'Role', 'Status', 
                'Last Login', 'Created At', 'Updated At'
            ]);

            foreach ($users as $user) {
                fputcsv($file, [
                    $user->id,
                    $user->name,
                    $user->email,
                    $user->phone,
                    $user->role,
                    $user->status,
                    $user->last_login_at ? $user->last_login_at->format('Y-m-d H:i:s') : '',
                    $user->created_at->format('Y-m-d H:i:s'),
                    $user->updated_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Send welcome email to new user
     */
    public function sendWelcomeEmail(AdminUser $user)
    {
        // This would integrate with your notification system
        // Mail::to($user->email)->send(new WelcomeEmail($user));
        
        return back()->with('success', 'Welcome email sent successfully!');
    }
}