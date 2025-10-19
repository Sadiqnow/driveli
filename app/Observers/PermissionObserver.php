<?php

namespace App\Observers;

use App\Models\Permission;
use App\Models\AuditLog;

class PermissionObserver
{
    /**
     * Handle the Permission "created" event.
     *
     * @param  \App\Models\Permission  $permission
     * @return void
     */
    public function created(Permission $permission)
    {
        AuditLog::log(
            'create',
            'Permission',
            $permission->id,
            null,
            $permission->toArray(),
            "Permission '{$permission->name}' was created"
        );
    }

    /**
     * Handle the Permission "updated" event.
     *
     * @param  \App\Models\Permission  $permission
     * @return void
     */
    public function updated(Permission $permission)
    {
        $oldValues = $permission->getOriginal();
        $newValues = $permission->getChanges();

        // Only log if there were actual changes
        if (!empty($newValues)) {
            AuditLog::log(
                'update',
                'Permission',
                $permission->id,
                $oldValues,
                array_merge($oldValues, $newValues),
                "Permission '{$permission->name}' was updated"
            );
        }
    }

    /**
     * Handle the Permission "deleted" event.
     *
     * @param  \App\Models\Permission  $permission
     * @return void
     */
    public function deleted(Permission $permission)
    {
        AuditLog::log(
            'delete',
            'Permission',
            $permission->id,
            $permission->toArray(),
            null,
            "Permission '{$permission->name}' was deleted"
        );
    }

    /**
     * Handle the Permission "restored" event.
     *
     * @param  \App\Models\Permission  $permission
     * @return void
     */
    public function restored(Permission $permission)
    {
        AuditLog::log(
            'restore',
            'Permission',
            $permission->id,
            null,
            $permission->toArray(),
            "Permission '{$permission->name}' was restored"
        );
    }

    /**
     * Handle the Permission "force deleted" event.
     *
     * @param  \App\Models\Permission  $permission
     * @return void
     */
    public function forceDeleted(Permission $permission)
    {
        AuditLog::log(
            'force_delete',
            'Permission',
            $permission->id,
            $permission->toArray(),
            null,
            "Permission '{$permission->name}' was permanently deleted"
        );
    }
}
