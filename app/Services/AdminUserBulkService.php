<?php

namespace App\Services;

use App\Models\AdminUser;
use Illuminate\Http\Request;

class AdminUserBulkService
{
    /**
     * Bulk activate admin users
     */
    public function bulkActivate(Request $request)
    {
        $admins = AdminUser::whereIn('id', $request->admin_ids)->get();
        $count = 0;

        foreach ($admins as $admin) {
            // Skip self
            if ($admin->id === auth('admin')->id()) continue;

            $admin->update([
                'status' => 'Active',
                'suspended_until' => null,
                'suspension_reason' => null
            ]);
            $count++;
        }

        // Log activity
        if (class_exists(\App\Services\ActivityLogger::class)) {
            \App\Services\ActivityLogger::log(
                'bulk_admin_activate',
                "Bulk activated {$count} admin users",
                auth('admin')->user(),
                ['admin_ids' => $request->admin_ids]
            );
        }

        return $count;
    }

    /**
     * Bulk deactivate admin users
     */
    public function bulkDeactivate(Request $request)
    {
        $admins = AdminUser::whereIn('id', $request->admin_ids)->get();
        $count = 0;

        foreach ($admins as $admin) {
            // Skip self
            if ($admin->id === auth('admin')->id()) continue;

            $admin->update(['status' => 'Inactive']);
            $count++;
        }

        // Log activity
        if (class_exists(\App\Services\ActivityLogger::class)) {
            \App\Services\ActivityLogger::log(
                'bulk_admin_deactivate',
                "Bulk deactivated {$count} admin users",
                auth('admin')->user(),
                ['admin_ids' => $request->admin_ids]
            );
        }

        return $count;
    }

    /**
     * Bulk delete admin users
     */
    public function bulkDelete(Request $request)
    {
        $admins = AdminUser::whereIn('id', $request->admin_ids)->get();
        $count = 0;

        foreach ($admins as $admin) {
            // Skip self
            if ($admin->id === auth('admin')->id()) continue;

            // Skip other super admins if current user is not super admin
            if ($admin->hasRole('Super Admin') && !auth('admin')->user()->hasRole('Super Admin')) continue;

            $admin->delete();
            $count++;
        }

        // Log activity
        if (class_exists(\App\Services\ActivityLogger::class)) {
            \App\Services\ActivityLogger::log(
                'bulk_admin_delete',
                "Bulk deleted {$count} admin users",
                auth('admin')->user(),
                ['admin_ids' => $request->admin_ids]
            );
        }

        return $count;
    }

    /**
     * Bulk operations on admin users
     */
    public function bulkOperations(Request $request)
    {
        $users = AdminUser::whereIn('id', $request->user_ids);

        switch ($request->action) {
            case 'activate':
                $users->update(['status' => 'Active']);
                $message = 'Users activated successfully';
                break;
            case 'deactivate':
                $users->update(['status' => 'Inactive']);
                $message = 'Users deactivated successfully';
                break;
            case 'delete':
                $users->delete();
                $message = 'Users deleted successfully';
                break;
            case 'restore':
                $users->withTrashed()->restore();
                $message = 'Users restored successfully';
                break;
        }

        // Log activity
        if (class_exists(\App\Services\ActivityLogger::class)) {
            \App\Services\ActivityLogger::log(
                'bulk_user_operation',
                "Bulk {$request->action} operation performed on " . count($request->user_ids) . " users",
                auth('admin')->user(),
                ['action' => $request->action, 'user_ids' => $request->user_ids]
            );
        }

        return $message;
    }
}
