<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class AdminUser extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $table = 'admin_users';

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'role',
        'status',
        'permissions',
        'avatar',
        'last_login_at',
        'last_login_ip',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'permissions' => 'array',
    ];

    // Relationships
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles', 'user_id', 'role_id');
    }

    public function superadminActivityLogs(): HasMany
    {
        return $this->hasMany(SuperadminActivityLog::class, 'superadmin_id');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class, 'user_id');
    }

    public function verifiedCompanies(): HasMany
    {
        return $this->hasMany(Company::class, 'verified_by');
    }

    public function verifiedDrivers(): HasMany
    {
        return $this->hasMany(DriverNormalized::class, 'verified_by');
    }

    public function createdCompanyRequests(): HasMany
    {
        return $this->hasMany(CompanyRequest::class, 'created_by');
    }

    public function approvedCompanyRequests(): HasMany
    {
        return $this->hasMany(CompanyRequest::class, 'approved_by');
    }

    public function assignedCompanyRequests(): HasMany
    {
        return $this->hasMany(CompanyRequest::class, 'assigned_to');
    }

    public function cancelledCompanyRequests(): HasMany
    {
        return $this->hasMany(CompanyRequest::class, 'cancelled_by');
    }

    public function matchedDrivers(): HasMany
    {
        return $this->hasMany(DriverMatch::class, 'matched_by_admin');
    }

    public function createdTemplates(): HasMany
    {
        return $this->hasMany(CompanyRequestTemplate::class, 'created_by');
    }

    // Helper methods
    public function hasRole(string $roleName): bool
    {
        return $this->roles()->where('name', $roleName)->exists();
    }

    public function hasPermission(string $permissionName): bool
    {
        // Check direct permissions in JSON field
        if ($this->permissions) {
            $permissions = is_array($this->permissions) ? $this->permissions : json_decode($this->permissions, true);
            if (is_array($permissions) && in_array($permissionName, $permissions)) {
                return true;
            }
        }

        // Check permissions through roles
        return $this->roles()->whereHas('permissions', function ($query) use ($permissionName) {
            $query->where('name', $permissionName);
        })->exists();
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super_admin');
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin') || $this->isSuperAdmin();
    }

    public function canManage(string $resource): bool
    {
        return $this->hasPermission("manage_{$resource}") || $this->isSuperAdmin();
    }

    public function getHighestRoleLevel(): int
    {
        return $this->roles()->min('level') ?? 999;
    }

    public function hasAnyRole(array $roles): bool
    {
        foreach ($roles as $role) {
            if ($this->hasRole($role)) {
                return true;
            }
        }
        return false;
    }

    public function hasAnyPermission(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission)) {
                return true;
            }
        }
        return false;
    }

    public function getRoleNames(): \Illuminate\Support\Collection
    {
        return $this->roles->pluck('name');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeSuperAdmins($query)
    {
        return $query->whereHas('roles', function ($q) {
            $q->where('name', 'super_admin');
        });
    }

    public function scopeAdmins($query)
    {
        return $query->whereHas('roles', function ($q) {
            $q->where('name', 'admin');
        });
    }
}
