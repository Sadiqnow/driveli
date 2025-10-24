<?php

namespace App\Services;

use App\Models\AdminUser;
use App\Models\UserActivity;
use Illuminate\Support\Facades\Schema;
use Illuminate\Http\Request;

class AdminUserDataService
{
    /**
     * Get paginated admin users with filters and stats
     */
    public function getUsers(Request $request)
    {
        $query = AdminUser::with(['roles', 'permissions'])
            ->withTrashed();

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->whereHas('roles', function($q) use ($request) {
                $q->where('name', $request->role);
            });
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('status', 'Active');
            } elseif ($request->status === 'inactive') {
                $query->where('status', 'Inactive');
            } elseif ($request->status === 'deleted') {
                $query->onlyTrashed();
            }
        }

        if ($request->get('with_trashed') !== '1') {
            $query->whereNull('deleted_at');
        }

        try {
            $users = $query->orderBy('created_at', 'desc')->paginate(15);

            // Get stats
            $stats = [
                'total' => AdminUser::count(),
                'active' => AdminUser::where('status', 'Active')->count(),
                'inactive' => AdminUser::where('status', 'Inactive')->count(),
                'super_admins' => AdminUser::whereHas('roles', function($q) {
                    $q->where('name', 'Super Admin');
                })->count(),
                'admins' => AdminUser::whereHas('roles', function($q) {
                    $q->where('name', 'Admin');
                })->count(),
                'moderators' => AdminUser::whereHas('roles', function($q) {
                    $q->where('name', 'Moderator');
                })->count(),
            ];
        } catch (\Exception $e) {
            // Fallback if role_user table doesn't exist
            $users = AdminUser::orderBy('created_at', 'desc')->paginate(15);
            $stats = [
                'total' => AdminUser::count(),
                'active' => AdminUser::where('status', 'Active')->count(),
                'inactive' => AdminUser::where('status', 'Inactive')->count(),
                'super_admins' => 0,
                'admins' => 0,
                'moderators' => 0,
            ];
        }

        return compact('users', 'stats');
    }

    /**
     * Get admin user details with activities
     */
    public function getUserDetails(AdminUser $admin)
    {
        // Load relationships
        $admin->load(['roles.permissions', 'permissions']);

        // Get activity log for this admin
        $activities = [];
        if (Schema::hasTable('user_activities')) {
            $activities = UserActivity::where('user_type', 'admin')
                ->where('user_id', $admin->id)
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();
        }

        return compact('admin', 'activities');
    }

    /**
     * Search admin users
     */
    public function searchUsers(Request $request)
    {
        $users = AdminUser::with('roles')
            ->where(function($q) use ($request) {
                $q->where('name', 'like', "%{$request->query}%")
                  ->orWhere('email', 'like', "%{$request->query}%");
            })
            ->limit(10)
            ->get()
            ->map(function($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->roles->first()?->display_name ?? 'No Role',
                    'status' => $user->status
                ];
            });

        return $users;
    }

    /**
     * Get users for API
     */
    public function getUsersForApi(Request $request)
    {
        $query = AdminUser::select('id', 'name', 'email', 'status')
            ->with(['roles' => function($q) {
                $q->select('roles.id', 'roles.display_name');
            }]);

        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('name')->get();

        return $users;
    }

    /**
     * Get user roles for API
     */
    public function getUserRolesForApi($userId)
    {
        $user = AdminUser::findOrFail($userId);

        $roles = $user->roles()
            ->select('roles.id', 'roles.display_name', 'roles.description', 'roles.level')
            ->get();

        return $roles;
    }
}
