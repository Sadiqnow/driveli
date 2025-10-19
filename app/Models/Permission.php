<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Permission extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'display_name',
        'description',
        'category',
        'group_name',
        'resource',
        'action',
        'is_active',
        'meta'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'meta' => 'array'
    ];

    protected $dates = ['deleted_at'];

    /**
     * Permission categories
     */
    const CATEGORY_USER = 'user';
    const CATEGORY_ADMIN = 'admin';
    const CATEGORY_DRIVER = 'driver';
    const CATEGORY_COMPANY = 'company';
    const CATEGORY_SYSTEM = 'system';
    const CATEGORY_REPORT = 'report';

    /**
     * Common permission actions
     */
    const ACTION_VIEW = 'view';
    const ACTION_CREATE = 'create';
    const ACTION_EDIT = 'edit';
    const ACTION_DELETE = 'delete';
    const ACTION_MANAGE = 'manage';
    const ACTION_APPROVE = 'approve';
    const ACTION_REJECT = 'reject';

    /**
     * System permissions
     */
    const MANAGE_USERS = 'manage_users';
    const MANAGE_ROLES = 'manage_roles';
    const MANAGE_PERMISSIONS = 'manage_permissions';
    const VIEW_DASHBOARD = 'view_dashboard';
    const MANAGE_SYSTEM = 'manage_system';
    
    /**
     * Driver permissions
     */
    const VIEW_DRIVERS = 'view_drivers';
    const CREATE_DRIVERS = 'create_drivers';
    const EDIT_DRIVERS = 'edit_drivers';
    const DELETE_DRIVERS = 'delete_drivers';
    const APPROVE_DRIVERS = 'approve_drivers';
    const VERIFY_DRIVERS = 'verify_drivers';
    
    /**
     * Company permissions
     */
    const VIEW_COMPANIES = 'view_companies';
    const CREATE_COMPANIES = 'create_companies';
    const EDIT_COMPANIES = 'edit_companies';
    const DELETE_COMPANIES = 'delete_companies';
    const APPROVE_COMPANIES = 'approve_companies';
    
    /**
     * Report permissions
     */
    const VIEW_REPORTS = 'view_reports';
    const EXPORT_REPORTS = 'export_reports';
    const CREATE_REPORTS = 'create_reports';

    /**
     * Get roles that have this permission
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_permissions', 'permission_id', 'role_id')
                    ->withPivot('is_active')
                    ->withTimestamps();
    }

    /**
     * Get active roles that have this permission
     */
    public function activeRoles(): BelongsToMany
    {
        return $this->roles()->wherePivot('is_active', true);
    }

    /**
     * Get users that have this permission through roles
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(AdminUser::class, 'user_roles', 'role_id', 'user_id')
                    ->join('role_permissions', 'roles.id', '=', 'role_permissions.role_id')
                    ->where('role_permissions.permission_id', $this->id)
                    ->where('role_permissions.is_active', true)
                    ->where('user_roles.is_active', true);
    }

    /**
     * Check if permission belongs to a category
     */
    public function inCategory(string $category): bool
    {
        return $this->category === $category;
    }

    /**
     * Check if permission is for a specific resource and action
     */
    public function isFor(string $resource, string $action = null): bool
    {
        $resourceMatch = $this->resource === $resource;
        
        if ($action === null) {
            return $resourceMatch;
        }
        
        return $resourceMatch && $this->action === $action;
    }

    /**
     * Scope for active permissions
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for permissions by category
     */
    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope for permissions by resource
     */
    public function scopeByResource($query, string $resource)
    {
        return $query->where('resource', $resource);
    }

    /**
     * Scope for permissions by action
     */
    public function scopeByAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Get formatted display name
     */
    protected function displayName(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value ?? ucfirst(str_replace(['_', '.'], [' ', ' '], $this->name))
        );
    }

    /**
     * Get all available categories
     */
    public static function getCategories(): array
    {
        return [
            self::CATEGORY_USER => 'User Management',
            self::CATEGORY_ADMIN => 'Admin Management', 
            self::CATEGORY_DRIVER => 'Driver Management',
            self::CATEGORY_COMPANY => 'Company Management',
            self::CATEGORY_SYSTEM => 'System Management',
            self::CATEGORY_REPORT => 'Reports'
        ];
    }

    /**
     * Get all available actions
     */
    public static function getActions(): array
    {
        return [
            self::ACTION_VIEW => 'View',
            self::ACTION_CREATE => 'Create',
            self::ACTION_EDIT => 'Edit',
            self::ACTION_DELETE => 'Delete',
            self::ACTION_MANAGE => 'Manage',
            self::ACTION_APPROVE => 'Approve',
            self::ACTION_REJECT => 'Reject'
        ];
    }
}