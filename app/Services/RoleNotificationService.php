<?php

namespace App\Services;

use App\Models\AdminUser;
use App\Models\Role;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class RoleNotificationService
{
    /**
     * Send notifications for role changes
     *
     * @param AdminUser $user The user whose roles were changed
     * @param array $addedRoleIds Array of role IDs that were added
     * @param array $removedRoleIds Array of role IDs that were removed
     * @param array $currentRoleNames Array of current role display names
     */
    public function notifyRoleChanges(AdminUser $user, array $addedRoleIds, array $removedRoleIds, array $currentRoleNames)
    {
        try {
            // Send email notifications
            $this->sendEmailNotifications($user, $addedRoleIds, $removedRoleIds, $currentRoleNames);

            // Send in-app notifications
            $this->sendInAppNotifications($user, $addedRoleIds, $removedRoleIds, $currentRoleNames);

        } catch (\Exception $e) {
            Log::error('Failed to send role change notifications', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'added_roles' => $addedRoleIds,
                'removed_roles' => $removedRoleIds
            ]);
        }
    }

    /**
     * Send email notifications for role changes
     */
    private function sendEmailNotifications(AdminUser $user, array $addedRoleIds, array $removedRoleIds, array $currentRoleNames)
    {
        try {
            // Get role details for added roles
            $addedRoles = !empty($addedRoleIds) ? Role::whereIn('id', $addedRoleIds)->get() : collect();

            // Get role details for removed roles
            $removedRoles = !empty($removedRoleIds) ? Role::whereIn('id', $removedRoleIds)->get() : collect();

            // Send email for added roles
            if ($addedRoles->isNotEmpty()) {
                foreach ($addedRoles as $role) {
                    $this->sendRoleAssignedEmail($user, $role);
                }
            }

            // Send email for removed roles
            if ($removedRoles->isNotEmpty()) {
                foreach ($removedRoles as $role) {
                    $this->sendRoleRemovedEmail($user, $role);
                }
            }

        } catch (\Exception $e) {
            Log::error('Failed to send email notifications for role changes', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send in-app notifications for role changes
     */
    private function sendInAppNotifications(AdminUser $user, array $addedRoleIds, array $removedRoleIds, array $currentRoleNames)
    {
        try {
            // Get role details for added roles
            $addedRoles = !empty($addedRoleIds) ? Role::whereIn('id', $addedRoleIds)->get() : collect();

            // Get role details for removed roles
            $removedRoles = !empty($removedRoleIds) ? Role::whereIn('id', $removedRoleIds)->get() : collect();

            // Create in-app notifications for added roles
            if ($addedRoles->isNotEmpty()) {
                foreach ($addedRoles as $role) {
                    $this->createInAppNotification($user, 'role_assigned', [
                        'title' => 'New Role Assigned',
                        'message' => "You have been assigned the '{$role->display_name}' role.",
                        'role_name' => $role->display_name,
                        'role_description' => $role->description,
                        'action_type' => 'role_assigned',
                        'metadata' => [
                            'role_id' => $role->id,
                            'role_name' => $role->name,
                            'assigned_at' => now()->toISOString()
                        ]
                    ]);
                }
            }

            // Create in-app notifications for removed roles
            if ($removedRoles->isNotEmpty()) {
                foreach ($removedRoles as $role) {
                    $this->createInAppNotification($user, 'role_removed', [
                        'title' => 'Role Removed',
                        'message' => "Your '{$role->display_name}' role has been removed.",
                        'role_name' => $role->display_name,
                        'role_description' => $role->description,
                        'action_type' => 'role_removed',
                        'metadata' => [
                            'role_id' => $role->id,
                            'role_name' => $role->name,
                            'removed_at' => now()->toISOString()
                        ]
                    ]);
                }
            }

        } catch (\Exception $e) {
            Log::error('Failed to send in-app notifications for role changes', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send email notification for role assignment
     */
    private function sendRoleAssignedEmail(AdminUser $user, Role $role)
    {
        try {
            // Here you would send an actual email using Laravel's Mail facade
            // For now, we'll log it as the email would be sent

            Log::info('Role assigned email notification', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'role_name' => $role->display_name,
                'role_description' => $role->description,
                'timestamp' => now()
            ]);

            // Example of how you would send the email:
            /*
            Mail::to($user->email)->send(new RoleAssignedNotification($user, $role));
            */

        } catch (\Exception $e) {
            Log::error('Failed to send role assigned email', [
                'user_id' => $user->id,
                'role_id' => $role->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Send email notification for role removal
     */
    private function sendRoleRemovedEmail(AdminUser $user, Role $role)
    {
        try {
            Log::info('Role removed email notification', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'role_name' => $role->display_name,
                'role_description' => $role->description,
                'timestamp' => now()
            ]);

            // Example of how you would send the email:
            /*
            Mail::to($user->email)->send(new RoleRemovedNotification($user, $role));
            */

        } catch (\Exception $e) {
            Log::error('Failed to send role removed email', [
                'user_id' => $user->id,
                'role_id' => $role->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Create an in-app notification
     */
    private function createInAppNotification(AdminUser $user, string $type, array $data)
    {
        try {
            // Check if notifications table exists and create notification
            if (Schema::hasTable('notifications')) {
                DB::table('notifications')->insert([
                    'id' => \Illuminate\Support\Str::uuid(),
                    'type' => 'App\\Notifications\\RoleChangeNotification',
                    'notifiable_type' => AdminUser::class,
                    'notifiable_id' => $user->id,
                    'data' => json_encode($data),
                    'read_at' => null,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }

            Log::info('In-app notification created', [
                'user_id' => $user->id,
                'type' => $type,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to create in-app notification', [
                'user_id' => $user->id,
                'type' => $type,
                'error' => $e->getMessage()
            ]);
        }
    }
}
