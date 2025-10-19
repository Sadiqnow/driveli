<?php

namespace App\Observers;

use App\Models\Role;
use App\Models\AuditLog;

class RoleObserver
{
    /**
     * Handle the Role "created" event.
     *
     * @param  \App\Models\Role  $role
     * @return void
     */
    public function created(Role $role)
    {
        AuditLog::log(
            'create',
            'Role',
            $role->id,
            null,
            $role->toArray(),
            "Role '{$role->name}' was created"
        );
    }

    /**
     * Handle the Role "updated" event.
     *
     * @param  \App\Models\Role  $role
     * @return void
     */
    public function updated(Role $role)
    {
        $oldValues = $role->getOriginal();
        $newValues = $role->getChanges();

        // Only log if there were actual changes
        if (!empty($newValues)) {
            AuditLog::log(
                'update',
                'Role',
                $role->id,
                $oldValues,
                array_merge($oldValues, $newValues),
                "Role '{$role->name}' was updated"
            );
        }
    }

    /**
     * Handle the Role "deleted" event.
     *
     * @param  \App\Models\Role  $role
     * @return void
     */
    public function deleted(Role $role)
    {
        AuditLog::log(
            'delete',
            'Role',
            $role->id,
            $role->toArray(),
            null,
            "Role '{$role->name}' was deleted"
        );
    }

    /**
     * Handle the Role "restored" event.
     *
     * @param  \App\Models\Role  $role
     * @return void
     */
    public function restored(Role $role)
    {
        AuditLog::log(
            'restore',
            'Role',
            $role->id,
            null,
            $role->toArray(),
            "Role '{$role->name}' was restored"
        );
    }

    /**
     * Handle the Role "force deleted" event.
     *
     * @param  \App\Models\Role  $role
     * @return void
     */
    public function forceDeleted(Role $role)
    {
        AuditLog::log(
            'force_delete',
            'Role',
            $role->id,
            $role->toArray(),
            null,
            "Role '{$role->name}' was permanently deleted"
        );
    }
}
