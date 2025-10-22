<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'group_name',
    ];

    // Relationships
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_permissions');
    }

    // Helper methods
    public function assignToRole(string $roleName): void
    {
        $role = Role::where('name', $roleName)->first();
        if ($role && !$role->hasPermission($this->name)) {
            $role->permissions()->attach($this);
        }
    }

    public function revokeFromRole(string $roleName): void
    {
        $role = Role::where('name', $roleName)->first();
        if ($role) {
            $role->permissions()->detach($this);
        }
    }

    // Scopes
    public function scopeByGroup($query, $groupName)
    {
        return $query->where('group_name', $groupName);
    }

    public function scopeSystemPermissions($query)
    {
        return $query->where('group_name', 'superadmin');
    }
}
