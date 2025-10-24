<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    use HasFactory;

    // Permission Categories
    const CATEGORY_SYSTEM = 'system';
    const CATEGORY_USER = 'user';
    const CATEGORY_ADMIN = 'admin';
    const CATEGORY_DRIVER = 'driver';
    const CATEGORY_COMPANY = 'company';
    const CATEGORY_REPORT = 'report';

    // Permission Actions
    const ACTION_MANAGE = 'manage';
    const ACTION_VIEW = 'view';
    const ACTION_CREATE = 'create';
    const ACTION_EDIT = 'edit';
    const ACTION_DELETE = 'delete';
    const ACTION_APPROVE = 'approve';

    // Specific Permissions
    const MANAGE_SYSTEM = 'manage_system';
    const VIEW_DASHBOARD = 'view_dashboard';
    const MANAGE_USERS = 'manage_users';
    const MANAGE_ROLES = 'manage_roles';
    const MANAGE_PERMISSIONS = 'manage_permissions';
    const VIEW_DRIVERS = 'view_drivers';
    const CREATE_DRIVERS = 'create_drivers';
    const EDIT_DRIVERS = 'edit_drivers';
    const DELETE_DRIVERS = 'delete_drivers';
    const APPROVE_DRIVERS = 'approve_drivers';
    const VERIFY_DRIVERS = 'verify_drivers';
    const VIEW_COMPANIES = 'view_companies';
    const CREATE_COMPANIES = 'create_companies';
    const EDIT_COMPANIES = 'edit_companies';
    const DELETE_COMPANIES = 'delete_companies';
    const APPROVE_COMPANIES = 'approve_companies';
    const VIEW_REPORTS = 'view_reports';
    const CREATE_REPORTS = 'create_reports';
    const EXPORT_REPORTS = 'export_reports';

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
