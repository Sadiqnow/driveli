<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AdminUserDataService;
use App\Services\AdminUserActionService;
use App\Services\AdminUserBulkService;
use App\Models\AdminUser;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AdminUserController extends Controller
{
    protected $dataService;
    protected $actionService;
    protected $bulkService;

    public function __construct(
        AdminUserDataService $dataService,
        AdminUserActionService $actionService,
        AdminUserBulkService $bulkService
    ) {
        $this->middleware('auth:admin');
        $this->middleware('SuperAdminDriverAccess');

        $this->dataService = $dataService;
        $this->actionService = $actionService;
        $this->bulkService = $bulkService;
    }

    /**
     * Display admin users
     */
    public function index(Request $request)
    {
        $data = $this->dataService->getUsers($request);
        return view('admin.superadmin.users', $data);
    }

    /**
     * Show create admin form
     */
    public function create()
    {
        // Get available roles for assignment
        $roles = \App\Models\Role::all();

        return view('admin.superadmin.admins.create', compact('roles'));
    }

    /**
     * Store new admin
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:admin_users,email',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|string|exists:roles,name',
            'status' => 'required|in:Active,Inactive'
        ]);

        try {
            $admin = $this->actionService->createAdmin($request);

            return redirect()->route('admin.superadmin.admins.show', $admin)
                           ->with('success', 'Admin created successfully.');

        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['error' => 'Failed to create admin: ' . $e->getMessage()]);
        }
    }

    /**
     * Show admin details
     */
    public function show(AdminUser $admin)
    {
        $data = $this->dataService->getUserDetails($admin);
        return view('admin.superadmin.admins.show', $data);
    }

    /**
     * Show edit admin form
     */
    public function edit(\App\Models\AdminUser $admin)
    {
        // Get available roles
        $roles = \App\Models\Role::all();
        $currentRole = $admin->roles->first();

        return view('admin.superadmin.admins.edit', compact('admin', 'roles', 'currentRole'));
    }

    /**
     * Update admin
     */
    public function update(Request $request, AdminUser $admin)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:admin_users,email,' . $admin->id,
            'phone' => 'nullable|string|max:20',
            'role' => 'required|string|exists:roles,name',
            'status' => 'required|in:Active,Inactive',
            'password' => 'nullable|string|min:8|confirmed'
        ]);

        try {
            $admin = $this->actionService->updateAdmin($request, $admin);

            return redirect()->route('admin.superadmin.admins.show', $admin)
                           ->with('success', 'Admin updated successfully.');

        } catch (\Exception $e) {
            return back()->withInput()->withErrors(['error' => 'Failed to update admin: ' . $e->getMessage()]);
        }
    }

    /**
     * Delete admin
     */
    public function destroy(AdminUser $admin)
    {
        try {
            $adminName = $this->actionService->deleteAdmin($admin);

            return redirect()->route('admin.superadmin.users')
                           ->with('success', 'Admin deleted successfully.');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to delete admin: ' . $e->getMessage()]);
        }
    }

    /**
     * Flag admin
     */
    public function flag(Request $request, AdminUser $admin)
    {
        $request->validate([
            'reason' => 'required|string|max:1000'
        ]);

        try {
            $admin = $this->actionService->flagAdmin($request, $admin);

            return response()->json([
                'success' => true,
                'message' => 'Admin flagged successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to flag admin: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Suspend admin
     */
    public function suspend(Request $request, AdminUser $admin)
    {
        $request->validate([
            'reason' => 'required|string|max:1000',
            'duration' => 'nullable|integer|min:1|max:365' // days
        ]);

        try {
            $admin = $this->actionService->suspendAdmin($request, $admin);

            return response()->json([
                'success' => true,
                'message' => 'Admin suspended successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to suspend admin: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Approve admin
     */
    public function approve(Request $request, AdminUser $admin)
    {
        $request->validate([
            'notes' => 'nullable|string|max:1000'
        ]);

        try {
            $admin = $this->actionService->approveAdmin($request, $admin);

            return response()->json([
                'success' => true,
                'message' => 'Admin approved successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve admin: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject admin
     */
    public function reject(Request $request, AdminUser $admin)
    {
        $request->validate([
            'reason' => 'required|string|max:1000'
        ]);

        try {
            $admin = $this->actionService->rejectAdmin($request, $admin);

            return response()->json([
                'success' => true,
                'message' => 'Admin rejected successfully.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reject admin: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk operations
     */
    public function bulkActivate(Request $request)
    {
        $request->validate([
            'admin_ids' => 'required|array',
            'admin_ids.*' => 'exists:admin_users,id'
        ]);

        try {
            $count = $this->bulkService->bulkActivate($request);

            return response()->json([
                'success' => true,
                'message' => "{$count} admins activated successfully."
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to bulk activate admins: ' . $e->getMessage()
            ], 500);
        }
    }

    public function bulkDeactivate(Request $request)
    {
        $request->validate([
            'admin_ids' => 'required|array',
            'admin_ids.*' => 'exists:admin_users,id'
        ]);

        try {
            $count = $this->bulkService->bulkDeactivate($request);

            return response()->json([
                'success' => true,
                'message' => "{$count} admins deactivated successfully."
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to bulk deactivate admins: ' . $e->getMessage()
            ], 500);
        }
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'admin_ids' => 'required|array',
            'admin_ids.*' => 'exists:admin_users,id'
        ]);

        try {
            $count = $this->bulkService->bulkDelete($request);

            return response()->json([
                'success' => true,
                'message' => "{$count} admins deleted successfully."
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to bulk delete admins: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search users
     */
    public function search(Request $request)
    {
        $request->validate([
            'query' => 'required|string|min:2'
        ]);

        $users = $this->dataService->searchUsers($request);

        return response()->json([
            'success' => true,
            'users' => $users
        ]);
    }

    /**
     * Bulk user operations
     */
    public function bulkOperations(Request $request)
    {
        $request->validate([
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'exists:admin_users,id',
            'action' => 'required|in:activate,deactivate,delete,restore'
        ]);

        try {
            $message = $this->bulkService->bulkOperations($request);

            return response()->json([
                'success' => true,
                'message' => $message
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Bulk operation failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: Get users for frontend
     */
    public function getUsersApi(Request $request)
    {
        try {
            $users = $this->dataService->getUsersForApi($request);

            return response()->json([
                'success' => true,
                'data' => $users
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load users: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API: Get user roles for frontend
     */
    public function getUserRolesApi($userId)
    {
        try {
            $roles = $this->dataService->getUserRolesForApi($userId);

            return response()->json([
                'success' => true,
                'data' => $roles
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load user roles: ' . $e->getMessage()
            ], 500);
        }
    }
}
