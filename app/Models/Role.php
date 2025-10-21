<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Events\RoleUpdated;
use App\Events\UserRoleModified;

class Role extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'display_name',
        'description',
        'level',
        'is_active',
        'meta',
        'parent_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'level' => 'integer',
        'meta' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $dates = ['deleted_at'];

    /**
     * Role level constants - Updated hierarchy order
     * SuperAdmin → Admin → Moderator → Agent → Driver → Company → Matching Officer → Verification Manager
     */
    const LEVEL_SUPER_ADMIN = 100;
    const LEVEL_ADMIN = 90;
    const LEVEL_VERIFICATION_MANAGER = 60;
    const LEVEL_MODERATOR = 50;
    const LEVEL_MATCHING_OFFICER = 40;
    const LEVEL_AGENT = 30;
    const LEVEL_DRIVER = 20;
    const LEVEL_COMPANY = 20;

    /**
     * Role name constants
     */
    const SUPER_ADMIN = 'super_admin';
    const ADMIN = 'admin';
    const MODERATOR = 'moderator';
    const AGENT = 'agent';
    const DRIVER = 'driver';
    const COMPANY = 'company';
    const MATCHING_OFFICER = 'matching_officer';
    const VERIFICATION_MANAGER = 'verification_manager';

    /**
     * Get users that have this role
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(AdminUser::class, 'user_roles', 'role_id', 'user_id')
                    ->withPivot(['is_active', 'expires_at'])
                    ->withTimestamps();
    }

    /**
     * Get active users that have this role
     */
    public function activeUsers(): BelongsToMany
    {
        return $this->users()
                    ->wherePivot('is_active', true)
                    ->where(function ($query) {
                        $query->whereNull('user_roles.expires_at')
                              ->orWhere('user_roles.expires_at', '>', now());
                    });
    }

    /**
     * Get permissions for this role
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_permissions', 'role_id', 'permission_id')
                    ->withPivot('is_active')
                    ->withTimestamps();
    }

    /**
     * Get parent role
     */
    public function parent()
    {
        return $this->belongsTo(Role::class, 'parent_id');
    }

    /**
     * Get child roles
     */
    public function children()
    {
        return $this->hasMany(Role::class, 'parent_id');
    }

    /**
     * Get all descendant roles (recursive)
     */
    public function descendants()
    {
        return $this->children()->with('descendants');
    }

    /**
     * Get all ancestor roles (recursive)
     */
    public function ancestors()
    {
        $ancestors = collect();

        $parent = $this->parent;
        while ($parent) {
            $ancestors->push($parent);
            $parent = $parent->parent;
        }

        return $ancestors;
    }

    /**
     * Get all permissions including inherited ones from ancestors
     */
    public function getAllPermissions()
    {
        $permissions = collect();

        // Add permissions from ancestors (higher in hierarchy)
        foreach ($this->ancestors() as $ancestor) {
            $ancestorPermissions = $ancestor->activePermissions()->get();
            foreach ($ancestorPermissions as $permission) {
                $permissions->put($permission->id, $permission);
            }
        }

        // Add permissions from current role (overrides ancestors if same permission)
        $currentPermissions = $this->activePermissions()->get();
        foreach ($currentPermissions as $permission) {
            $permissions->put($permission->id, $permission);
        }

        return $permissions->values();
    }

    /**
     * Get all permission names including inherited ones
     */
    public function getAllPermissionNames(): array
    {
        return $this->getAllPermissions()->pluck('name')->toArray();
    }

    /**
     * Check if this role has a specific permission (including inherited)
     */
    public function hasPermission(string $permissionName): bool
    {
        return in_array($permissionName, $this->getAllPermissionNames());
    }

    /**
     * Get active permissions for this role
     */
    public function activePermissions(): BelongsToMany
    {
        return $this->permissions()->wherePivot('is_active', true);
    }

    /**
     * Get display name attribute
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->attributes['display_name'] ?? ucwords(str_replace(['_', '-'], ' ', $this->name));
    }

    /**
     * Check if role can manage another role
     */
    public function canManage(Role $role): bool
    {
        return $this->level > $role->level;
    }

    /**
     * Assign permission to this role
     */
    public function givePermission(Permission $permission, ?AdminUser $assignedBy = null): self
    {
        $this->permissions()->syncWithoutDetaching([
            $permission->id => [
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);

        // Log permission assignment
        if ($assignedBy) {
            $this->logPermissionChange('assigned', $permission, $assignedBy);

            // Fire event for permission assignment
            event(new RoleUpdated($this, $assignedBy, 'permission_assigned', [
                'permission_id' => $permission->id,
                'permission_name' => $permission->name
            ]));
        }

        return $this;
    }

    /**
     * Remove permission from this role
     */
    public function revokePermission(Permission $permission, ?AdminUser $revokedBy = null): self
    {
        $this->permissions()->updateExistingPivot($permission->id, [
            'is_active' => false,
            'updated_at' => now()
        ]);

        // Log permission revocation
        if ($revokedBy) {
            $this->logPermissionChange('revoked', $permission, $revokedBy);

            // Fire event for permission revocation
            event(new RoleUpdated($this, $revokedBy, 'permission_revoked', [
                'permission_id' => $permission->id,
                'permission_name' => $permission->name
            ]));
        }

        return $this;
    }

    /**
     * Assign role to user
     */
    public function assignToUser(AdminUser $user, ?AdminUser $assignedBy = null): self
    {
        $user->roles()->syncWithoutDetaching([
            $this->id => [
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);

        // Clear user permission cache
        $user->clearPermissionCache();

        // Log role assignment
        if ($assignedBy) {
            $this->logRoleChange('assigned', $user, $assignedBy);

            // Fire event for role assignment
            event(new UserRoleModified($user, $this, $assignedBy, 'assigned'));
        }

        return $this;
    }

    /**
     * Remove role from user
     */
    public function removeFromUser(AdminUser $user, ?AdminUser $removedBy = null): self
    {
        $user->roles()->updateExistingPivot($this->id, [
            'is_active' => false,
            'updated_at' => now()
        ]);

        // Clear user permission cache
        $user->clearPermissionCache();

        // Log role removal
        if ($removedBy) {
            $this->logRoleChange('removed', $user, $removedBy);

            // Fire event for role removal
            event(new UserRoleModified($user, $this, $removedBy, 'removed'));
        }

        return $this;
    }

    /**
     * Log permission changes
     */
    private function logPermissionChange(string $action, Permission $permission, AdminUser $user): void
    {
        try {
            \App\Models\PermissionLog::create([
                'user_id' => $user->id,
                'permission_name' => $permission->name,
                'result' => 'granted',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'route_name' => request()->route() ? request()->route()->getName() : null,
                'method' => request()->method(),
                'metadata' => [
                    'action' => $action,
                    'role_id' => $this->id,
                    'role_name' => $this->name,
                    'permission_id' => $permission->id,
                    'permission_display_name' => $permission->display_name
                ]
            ]);
        } catch (\Exception $e) {
            // Log error but don't break the operation
            \Illuminate\Support\Facades\Log::error('Failed to log permission change', [
                'error' => $e->getMessage(),
                'role' => $this->name,
                'permission' => $permission->name,
                'action' => $action
            ]);
        }
    }

    /**
     * Log role changes
     */
    private function logRoleChange(string $action, AdminUser $targetUser, AdminUser $actor): void
    {
        try {
            \App\Models\PermissionLog::create([
                'user_id' => $actor->id,
                'permission_name' => 'manage_roles',
                'result' => 'granted',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'route_name' => request()->route() ? request()->route()->getName() : null,
                'method' => request()->method(),
                'metadata' => [
                    'action' => $action,
                    'role_id' => $this->id,
                    'role_name' => $this->name,
                    'target_user_id' => $targetUser->id,
                    'target_user_name' => $targetUser->name
                ]
            ]);
        } catch (\Exception $e) {
            // Log error but don't break the operation
            \Illuminate\Support\Facades\Log::error('Failed to log role change', [
                'error' => $e->getMessage(),
                'role' => $this->name,
                'target_user' => $targetUser->name,
                'action' => $action
            ]);
        }
    }

    /**
     * Scope for active roles
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for roles by level
     */
    public function scopeByLevel($query, $level)
    {
        return $query->where('level', '>=', $level);
    }

    /**
     * Scope for roles that can be managed by current user
     */
    public function scopeManageableBy($query, AdminUser $user)
    {
        // Super admin can manage all roles
        if ($user->hasRole('super_admin')) {
            return $query;
        }

        // Get user's highest role level
        $userLevel = $user->roles()->max('level') ?? 0;

        return $query->where('level', '<', $userLevel);
    }

    /**
     * Get predefined role levels
     */
    public static function getRoleLevels(): array
    {
        return [
            self::LEVEL_SUPER_ADMIN => 'Super Admin',
            self::LEVEL_ADMIN => 'Admin',
            self::LEVEL_MODERATOR => 'Moderator',
            self::LEVEL_AGENT => 'Agent',
            self::LEVEL_DRIVER => 'Driver',
            self::LEVEL_COMPANY => 'Company',
            self::LEVEL_MATCHING_OFFICER => 'Matching Officer',
            self::LEVEL_VERIFICATION_MANAGER => 'Verification Manager',
        ];
    }
}
