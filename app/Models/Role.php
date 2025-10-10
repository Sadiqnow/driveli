<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Role extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'display_name', 
        'description',
        'level',
        'is_active',
        'meta'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'meta' => 'array',
        'level' => 'integer'
    ];

    protected $dates = ['deleted_at'];

    /**
     * Role hierarchy levels
     */
    const LEVEL_USER = 1;
    const LEVEL_MODERATOR = 5;
    const LEVEL_ADMIN = 10;
    const LEVEL_SUPER_ADMIN = 100;

    /**
     * Default role names
     */
    const SUPER_ADMIN = 'super_admin';
    const ADMIN = 'admin';
    const MODERATOR = 'moderator';
    const VIEWER = 'viewer';

    /**
     * Get users with this role
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(AdminUser::class, 'role_user', 'role_id', 'user_id')
                    ->withPivot(['assigned_at', 'assigned_by', 'expires_at', 'is_active', 'meta'])
                    ->withTimestamps();
    }

    /**
     * Get permissions for this role
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'permission_role')
                    ->withPivot(['assigned_at', 'assigned_by', 'is_active', 'meta'])
                    ->withTimestamps();
    }

    /**
     * Get active permissions for this role
     */
    public function activePermissions(): BelongsToMany
    {
        return $this->permissions()->wherePivot('is_active', true);
    }

    /**
     * Check if role has a specific permission
     */
    public function hasPermission(string $permissionName): bool
    {
        return $this->activePermissions()
                    ->where('permissions.name', $permissionName)
                    ->where('permissions.is_active', true)
                    ->exists();
    }

    /**
     * Check if role has any of the given permissions
     */
    public function hasAnyPermission(array $permissions): bool
    {
        return $this->activePermissions()
                    ->whereIn('permissions.name', $permissions)
                    ->where('permissions.is_active', true)
                    ->exists();
    }

    /**
     * Check if role has all of the given permissions
     */
    public function hasAllPermissions(array $permissions): bool
    {
        $rolePermissions = $this->activePermissions()
                               ->where('permissions.is_active', true)
                               ->pluck('permissions.name')
                               ->toArray();
        
        return empty(array_diff($permissions, $rolePermissions));
    }

    /**
     * Give permission to role
     */
    public function givePermission(string|Permission $permission, ?AdminUser $assignedBy = null): self
    {
        $permissionModel = is_string($permission) 
            ? Permission::where('name', $permission)->firstOrFail()
            : $permission;

        $this->permissions()->syncWithoutDetaching([
            $permissionModel->id => [
                'assigned_at' => now(),
                'assigned_by' => $assignedBy?->id,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);

        return $this;
    }

    /**
     * Revoke permission from role
     */
    public function revokePermission(string|Permission $permission): self
    {
        $permissionModel = is_string($permission)
            ? Permission::where('name', $permission)->firstOrFail()
            : $permission;

        $this->permissions()->updateExistingPivot($permissionModel->id, [
            'is_active' => false,
            'updated_at' => now()
        ]);

        return $this;
    }

    /**
     * Check if this role is higher level than another role
     */
    public function isHigherThan(Role $role): bool
    {
        return $this->level > $role->level;
    }

    /**
     * Check if this role is lower level than another role
     */
    public function isLowerThan(Role $role): bool
    {
        return $this->level < $role->level;
    }

    /**
     * Check if role is super admin
     */
    public function isSuperAdmin(): bool
    {
        return $this->name === self::SUPER_ADMIN || $this->level >= self::LEVEL_SUPER_ADMIN;
    }

    /**
     * Check if role is admin level or higher
     */
    public function isAdmin(): bool
    {
        return $this->level >= self::LEVEL_ADMIN;
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
    public function scopeByLevel($query, int $level)
    {
        return $query->where('level', $level);
    }

    /**
     * Scope for roles at or above level
     */
    public function scopeAtOrAboveLevel($query, int $level)
    {
        return $query->where('level', '>=', $level);
    }

    /**
     * Get formatted display name
     */
    protected function displayName(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value ?? ucfirst(str_replace('_', ' ', $this->name))
        );
    }
}