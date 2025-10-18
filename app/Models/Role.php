<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    use HasFactory;

    protected $fillable = [
        'name',
        'display_name',
        'description',
        'level',
        'is_active',
        'guard_name',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'level' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(AdminUser::class, 'user_roles', 'role_id', 'user_id')
                    ->withTimestamps();
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'role_permissions', 'role_id', 'permission_id')
                    ->withTimestamps();
    }

    public function activeUsers(): BelongsToMany
    {
        return $this->users()
                    ->wherePivot('is_active', true)
                    ->where(function ($query) {
                        $query->whereNull('role_user.expires_at')
                              ->orWhere('role_user.expires_at', '>', now());
                    });
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->display_name ?? ucwords(str_replace('_', ' ', $this->name));
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByLevel($query, $level)
    {
        return $query->where('level', '>=', $level);
    }
}
