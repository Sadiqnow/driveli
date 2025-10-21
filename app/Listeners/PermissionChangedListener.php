<?php

namespace App\Listeners;

use App\Events\PermissionChanged;
use App\Services\RoleSyncService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class PermissionChangedListener implements ShouldQueue
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
    public function handle(PermissionChanged $event): void
    {
        try {
            Log::info('Processing PermissionChanged event', [
                'permission_id' => $event->permission->id,
                'permission_name' => $event->permission->name,
                'action' => $event->action,
                'actor_id' => $event->actor->id
            ]);

            // Refresh cache for all users with this permission
            $this->roleSyncService->refreshUsersWithPermission($event->permission);

            Log::info('PermissionChanged event processed successfully', [
                'permission_id' => $event->permission->id
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to process PermissionChanged event', [
                'permission_id' => $event->permission->id,
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
    public function failed(PermissionChanged $event, \Throwable $exception): void
    {
        Log::error('PermissionChangedListener job failed', [
            'permission_id' => $event->permission->id,
            'exception' => $exception->getMessage()
        ]);
    }
}
