<?php

namespace App\Listeners;

use App\Events\UserRoleModified;
use App\Services\RoleSyncService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class UserRoleModifiedListener implements ShouldQueue
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
    public function handle(UserRoleModified $event): void
    {
        try {
            Log::info('Processing UserRoleModified event', [
                'target_user_id' => $event->targetUser->id,
                'role_id' => $event->role->id,
                'action' => $event->action,
                'actor_id' => $event->actor->id
            ]);

            // Refresh cache for the affected user
            $this->roleSyncService->refreshUserPermissions($event->targetUser);

            // If role was removed, also clear any cached data for that specific role
            if ($event->action === 'removed') {
                Log::info('Role removed from user, cache refreshed', [
                    'user_id' => $event->targetUser->id,
                    'role_id' => $event->role->id
                ]);
            }

            Log::info('UserRoleModified event processed successfully', [
                'target_user_id' => $event->targetUser->id,
                'role_id' => $event->role->id
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to process UserRoleModified event', [
                'target_user_id' => $event->targetUser->id,
                'role_id' => $event->role->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Re-throw to mark job as failed
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(UserRoleModified $event, \Throwable $exception): void
    {
        Log::error('UserRoleModifiedListener job failed', [
            'target_user_id' => $event->targetUser->id,
            'role_id' => $event->role->id,
            'exception' => $exception->getMessage()
        ]);
    }
}
