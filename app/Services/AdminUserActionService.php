<?php

namespace App\Services;

use App\Models\AdminUser;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminUserActionService
{
    /**
     * Create new admin user
     */
    public function createAdmin(Request $request)
    {
        DB::beginTransaction();

        try {
            // Create admin user
            $admin = AdminUser::create([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'password' => bcrypt($request->password),
                'status' => $request->status,
                'email_verified_at' => now(),
            ]);

            // Assign role
            $role = Role::where('name', $request->role)->first();
            if ($role) {
                $admin->assignRole($role);
            }

            DB::commit();

            // Log activity
            if (class_exists(\App\Services\ActivityLogger::class)) {
                \App\Services\ActivityLogger::log(
                    'admin_created',
                    "Created new admin user: {$admin->name}",
                    auth('admin')->user(),
                    ['admin_id' => $admin->id, 'role' => $request->role]
                );
            }

            return $admin;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Update admin user
     */
    public function updateAdmin(Request $request, AdminUser $admin)
    {
        DB::beginTransaction();

        try {
            // Update admin data
            $updateData = [
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'status' => $request->status,
            ];

            // Update password if provided
            if ($request->filled('password')) {
                $updateData['password'] = bcrypt($request->password);
            }

            $admin->update($updateData);

            // Update role
            $role = Role::where('name', $request->role)->first();
            if ($role) {
                $admin->roles()->detach();
                $admin->assignRole($role);
            }

            DB::commit();

            // Log activity
            if (class_exists(\App\Services\ActivityLogger::class)) {
                \App\Services\ActivityLogger::log(
                    'admin_updated',
                    "Updated admin user: {$admin->name}",
                    auth('admin')->user(),
                    ['admin_id' => $admin->id, 'changes' => $request->all()]
                );
            }

            return $admin;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Delete admin user
     */
    public function deleteAdmin(AdminUser $admin)
    {
        // Prevent deleting self
        if ($admin->id === auth('admin')->id()) {
            throw new \Exception('You cannot delete your own account.');
        }

        // Prevent deleting other super admins if current user is not super admin
        if ($admin->roles->contains('name', 'Super Admin') && !auth('admin')->user()->roles->contains('name', 'Super Admin')) {
            throw new \Exception('You do not have permission to delete Super Admin accounts.');
        }

        $adminName = $admin->name;

        // Log activity before deletion
        if (class_exists(\App\Services\ActivityLogger::class)) {
            \App\Services\ActivityLogger::log(
                'admin_deleted',
                "Deleted admin user: {$adminName}",
                auth('admin')->user(),
                ['admin_id' => $admin->id]
            );
        }

        $admin->delete();

        return $adminName;
    }

    /**
     * Flag admin user
     */
    public function flagAdmin(Request $request, AdminUser $admin)
    {
        // Prevent flagging self
        if ($admin->id === auth('admin')->id()) {
            throw new \Exception('You cannot flag your own account.');
        }

        $admin->update(['status' => 'Flagged']);

        // Log activity
        if (class_exists(\App\Services\ActivityLogger::class)) {
            \App\Services\ActivityLogger::log(
                'admin_flagged',
                "Flagged admin user: {$admin->name} - Reason: {$request->reason}",
                auth('admin')->user(),
                ['admin_id' => $admin->id, 'reason' => $request->reason]
            );
        }

        return $admin;
    }

    /**
     * Suspend admin user
     */
    public function suspendAdmin(Request $request, AdminUser $admin)
    {
        // Prevent suspending self
        if ($admin->id === auth('admin')->id()) {
            throw new \Exception('You cannot suspend your own account.');
        }

        $admin->update([
            'status' => 'Suspended',
            'suspended_until' => $request->duration ? now()->addDays($request->duration) : null,
            'suspension_reason' => $request->reason
        ]);

        // Log activity
        if (class_exists(\App\Services\ActivityLogger::class)) {
            \App\Services\ActivityLogger::log(
                'admin_suspended',
                "Suspended admin user: {$admin->name} - Reason: {$request->reason}",
                auth('admin')->user(),
                ['admin_id' => $admin->id, 'reason' => $request->reason, 'duration' => $request->duration]
            );
        }

        return $admin;
    }

    /**
     * Approve admin user
     */
    public function approveAdmin(Request $request, AdminUser $admin)
    {
        $admin->update([
            'status' => 'Active',
            'approved_at' => now(),
            'approved_by' => auth('admin')->id(),
            'suspended_until' => null,
            'suspension_reason' => null
        ]);

        // Log activity
        if (class_exists(\App\Services\ActivityLogger::class)) {
            \App\Services\ActivityLogger::log(
                'admin_approved',
                "Approved admin user: {$admin->name}",
                auth('admin')->user(),
                ['admin_id' => $admin->id, 'notes' => $request->notes]
            );
        }

        return $admin;
    }

    /**
     * Reject admin user
     */
    public function rejectAdmin(Request $request, AdminUser $admin)
    {
        $admin->update([
            'status' => 'Rejected',
            'rejection_reason' => $request->reason,
            'rejected_at' => now(),
            'rejected_by' => auth('admin')->id()
        ]);

        // Log activity
        if (class_exists(\App\Services\ActivityLogger::class)) {
            \App\Services\ActivityLogger::log(
                'admin_rejected',
                "Rejected admin user: {$admin->name} - Reason: {$request->reason}",
                auth('admin')->user(),
                ['admin_id' => $admin->id, 'reason' => $request->reason]
            );
        }

        return $admin;
    }
}
