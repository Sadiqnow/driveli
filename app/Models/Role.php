<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

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
        'meta' => 'array',
        'level' => 'integer',
    ];

    // Relationships
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Role::class, 'parent_id');
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_permissions');
    }

    public function adminUsers(): BelongsToMany
    {
        return $this->belongsToMany(AdminUser::class, 'user_roles', 'role_id', 'user_id');
    }

    public function userRoles(): HasMany
    {
        return $this->hasMany(UserRole::class);
    }

    // Helper methods
    public function hasPermission(string $permissionName): bool
    {
        return $this->permissions()->where('name', $permissionName)->exists();
    }

    public function grantPermission(string $permissionName): void
    {
        $permission = Permission::where('name', $permissionName)->first();
        if ($permission && !$this->hasPermission($permissionName)) {
            $this->permissions()->attach($permission);
        }
    }

    public function revokePermission(string $permissionName): void
    {
        $permission = Permission::where('name', $permissionName)->first();
        if ($permission) {
            $this->permissions()->detach($permission);
        }
    }

    public function isActive(): bool
    {
        return $this->is_active;
    }

    public function getAllPermissions()
    {
        return $this->permissions;
    }

    public function getChildRoles()
    {
        return $this->children()->with('children')->get();
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByLevel($query, $level)
    {
        return $query->where('level', $level);
    }

    public function scopeSystemRoles($query)
    {
        return $query->where('meta->system_role', true);
    }
}
