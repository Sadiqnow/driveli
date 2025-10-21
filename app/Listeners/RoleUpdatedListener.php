<?php

namespace App\Listeners;

use App\Events\RoleUpdated;
use App\Services\RoleSyncService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class RoleUpdatedListener implements ShouldQueue
{
    use InteractsWithQueue;

    public RoleSyncService $roleSyncService;

    /**
     * Create the event listener.
     */
    public function __construct(RoleSyncService $roleSyncService)
    {
        $this->roleSyncService = $roleSyncService;
    }

    /**
     * Handle the event.
     */
    public function handle(RoleUpdated $event): void
    {
        try {
            Log::info('Processing RoleUpdated event', [
                'role_id' => $event->role->id,
                'role_name' => $event->role->name,
                'action' => $event->action,
                'actor_id' => $event->actor->id
            ]);

            // Refresh cache for all users with this role
            $this->roleSyncService->refreshUsersWithRole($event->role);

            // If role hierarchy changed, also refresh users with child roles
            if (isset($event->changes['parent_id'])) {
                $this->refreshChildRoleUsers($event->role);
            }

            Log::info('RoleUpdated event processed successfully', [
                'role_id' => $event->role->id
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to process RoleUpdated event', [
                'role_id' => $event->role->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Re-throw to mark job as failed
            throw $e;
        }
    }

    /**
     * Refresh cache for users with child roles when hierarchy changes
     */
    private function refreshChildRoleUsers($role): void
    {
        $childRoles = $role->children()->with('activeUsers')->get();

        foreach ($childRoles as $childRole) {
            $this->roleSyncService->refreshUsersWithRole($childRole);
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(RoleUpdated $event, \Throwable $exception): void
    {
        Log::error('RoleUpdatedListener job failed', [
            'role_id' => $event->role->id,
            'exception' => $exception->getMessage()
        ]);
    }
}
