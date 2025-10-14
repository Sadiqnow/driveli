<?php

namespace App\Repositories;

use App\Models\Permission;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Permission Repository
 *
 * Handles all data access operations for the Permission model.
 * Provides specialized methods for permission management and role assignment.
 *
 * @package App\Repositories
 * @author DriveLink Development Team
 * @since 2.0.0
 */
class PermissionRepository extends BaseRepository
{
    /**
     * {@inheritDoc}
     */
    protected function makeModel(): Model
    {
        return new Permission();
    }

    /**
     * Get permission by name.
     *
     * @param string $name Permission name
     * @return Model|null
     */
    public function getByName(string $name): ?Model
    {
        return $this->findOneWhere(['name' => $name]);
    }

    /**
     * Get active permissions.
     *
     * @param int $perPage Number of records per page
     * @return LengthAwarePaginator
     */
    public function getActive(int $perPage = 20): LengthAwarePaginator
    {
        return $this->search(
            ['is_active' => true],
            ['category' => 'asc', 'resource' => 'asc', 'action' => 'asc'],
            $perPage,
            ['roles', 'users']
        );
    }

    /**
     * Get permissions by category.
     *
     * @param string $category Permission category
     * @param int $perPage Number of records per page
     * @return LengthAwarePaginator
     */
    public function getByCategory(string $category, int $perPage = 20): LengthAwarePaginator
    {
        return $this->search(
            ['category' => $category, 'is_active' => true],
            ['resource' => 'asc', 'action' => 'asc'],
            $perPage,
            ['roles']
        );
    }

    /**
     * Get permissions by resource.
     *
     * @param string $resource Resource name
     * @param int $perPage Number of records per page
     * @return LengthAwarePaginator
     */
    public function getByResource(string $resource, int $perPage = 20): LengthAwarePaginator
    {
        return $this->search(
            ['resource' => $resource, 'is_active' => true],
            ['action' => 'asc'],
            $perPage,
            ['roles']
        );
    }

    /**
     * Get permissions by action.
     *
     * @param string $action Action name
     * @param int $perPage Number of records per page
     * @return LengthAwarePaginator
     */
    public function getByAction(string $action, int $perPage = 20): LengthAwarePaginator
    {
        return $this->search(
            ['action' => $action, 'is_active' => true],
            ['category' => 'asc', 'resource' => 'asc'],
            $perPage,
            ['roles']
        );
    }

    /**
     * Get permissions for a specific resource and action.
     *
     * @param string $resource Resource name
     * @param string $action Action name
     * @return Collection
     */
    public function getByResourceAndAction(string $resource, string $action): Collection
    {
        return $this->findWhere([
            'resource' => $resource,
            'action' => $action,
            'is_active' => true
        ], ['*'], ['roles']);
    }

    /**
     * Get permissions assigned to a role.
     *
     * @param int $roleId Role ID
     * @param int $perPage Number of records per page
     * @return LengthAwarePaginator
     */
    public function getByRole(int $roleId, int $perPage = 20): LengthAwarePaginator
    {
        return $this->newQuery()
            ->with(['roles'])
            ->whereHas('activeRoles', function ($query) use ($roleId) {
                $query->where('roles.id', $roleId);
            })
            ->where('is_active', true)
            ->orderBy('category', 'asc')
            ->orderBy('resource', 'asc')
            ->orderBy('action', 'asc')
            ->paginate($perPage);
    }

    /**
     * Get permissions not assigned to a role.
     *
     * @param int $roleId Role ID
     * @param int $perPage Number of records per page
     * @return LengthAwarePaginator
     */
    public function getNotAssignedToRole(int $roleId, int $perPage = 20): LengthAwarePaginator
    {
        return $this->newQuery()
            ->with(['roles'])
            ->whereDoesntHave('activeRoles', function ($query) use ($roleId) {
                $query->where('roles.id', $roleId);
            })
            ->where('is_active', true)
            ->orderBy('category', 'asc')
            ->orderBy('resource', 'asc')
            ->orderBy('action', 'asc')
            ->paginate($perPage);
    }

    /**
     * Get permission statistics.
     *
     * @return array
     */
    public function getStatistics(): array
    {
        $baseQuery = $this->newQuery();

        return [
            'total' => $baseQuery->count(),
            'active' => (clone $baseQuery)->where('is_active', true)->count(),
            'inactive' => (clone $baseQuery)->where('is_active', false)->count(),
            'by_category' => $this->newQuery()
                ->selectRaw('category, COUNT(*) as count')
                ->where('is_active', true)
                ->groupBy('category')
                ->pluck('count', 'category')
                ->toArray(),
            'by_resource' => $this->newQuery()
                ->selectRaw('resource, COUNT(*) as count')
                ->where('is_active', true)
                ->groupBy('resource')
                ->pluck('count', 'resource')
                ->toArray(),
            'with_roles' => $this->newQuery()
                ->whereHas('activeRoles')
                ->where('is_active', true)
                ->count(),
        ];
    }

    /**
     * Create default permissions.
     *
     * @return array Created permissions
     */
    public function createDefaultPermissions(): array
    {
        $permissions = [
            // User Management
            ['name' => Permission::MANAGE_USERS, 'display_name' => 'Manage Users', 'category' => Permission::CATEGORY_ADMIN, 'resource' => 'user', 'action' => Permission::ACTION_MANAGE],
            ['name' => Permission::MANAGE_ROLES, 'display_name' => 'Manage Roles', 'category' => Permission::CATEGORY_ADMIN, 'resource' => 'role', 'action' => Permission::ACTION_MANAGE],
            ['name' => Permission::MANAGE_PERMISSIONS, 'display_name' => 'Manage Permissions', 'category' => Permission::CATEGORY_ADMIN, 'resource' => 'permission', 'action' => Permission::ACTION_MANAGE],

            // System
            ['name' => Permission::VIEW_DASHBOARD, 'display_name' => 'View Dashboard', 'category' => Permission::CATEGORY_SYSTEM, 'resource' => 'dashboard', 'action' => Permission::ACTION_VIEW],
            ['name' => Permission::MANAGE_SYSTEM, 'display_name' => 'Manage System', 'category' => Permission::CATEGORY_SYSTEM, 'resource' => 'system', 'action' => Permission::ACTION_MANAGE],

            // Driver Management
            ['name' => Permission::VIEW_DRIVERS, 'display_name' => 'View Drivers', 'category' => Permission::CATEGORY_DRIVER, 'resource' => 'driver', 'action' => Permission::ACTION_VIEW],
            ['name' => Permission::CREATE_DRIVERS, 'display_name' => 'Create Drivers', 'category' => Permission::CATEGORY_DRIVER, 'resource' => 'driver', 'action' => Permission::ACTION_CREATE],
            ['name' => Permission::EDIT_DRIVERS, 'display_name' => 'Edit Drivers', 'category' => Permission::CATEGORY_DRIVER, 'resource' => 'driver', 'action' => Permission::ACTION_EDIT],
            ['name' => Permission::DELETE_DRIVERS, 'display_name' => 'Delete Drivers', 'category' => Permission::CATEGORY_DRIVER, 'resource' => 'driver', 'action' => Permission::ACTION_DELETE],
            ['name' => Permission::APPROVE_DRIVERS, 'display_name' => 'Approve Drivers', 'category' => Permission::CATEGORY_DRIVER, 'resource' => 'driver', 'action' => Permission::ACTION_APPROVE],
            ['name' => Permission::VERIFY_DRIVERS, 'display_name' => 'Verify Drivers', 'category' => Permission::CATEGORY_DRIVER, 'resource' => 'driver', 'action' => Permission::ACTION_MANAGE],

            // Company Management
            ['name' => Permission::VIEW_COMPANIES, 'display_name' => 'View Companies', 'category' => Permission::CATEGORY_COMPANY, 'resource' => 'company', 'action' => Permission::ACTION_VIEW],
            ['name' => Permission::CREATE_COMPANIES, 'display_name' => 'Create Companies', 'category' => Permission::CATEGORY_COMPANY, 'resource' => 'company', 'action' => Permission::ACTION_CREATE],
            ['name' => Permission::EDIT_COMPANIES, 'display_name' => 'Edit Companies', 'category' => Permission::CATEGORY_COMPANY, 'resource' => 'company', 'action' => Permission::ACTION_EDIT],
            ['name' => Permission::DELETE_COMPANIES, 'display_name' => 'Delete Companies', 'category' => Permission::CATEGORY_COMPANY, 'resource' => 'company', 'action' => Permission::ACTION_DELETE],
            ['name' => Permission::APPROVE_COMPANIES, 'display_name' => 'Approve Companies', 'category' => Permission::CATEGORY_COMPANY, 'resource' => 'company', 'action' => Permission::ACTION_APPROVE],

            // Reports
            ['name' => Permission::VIEW_REPORTS, 'display_name' => 'View Reports', 'category' => Permission::CATEGORY_REPORT, 'resource' => 'report', 'action' => Permission::ACTION_VIEW],
            ['name' => Permission::EXPORT_REPORTS, 'display_name' => 'Export Reports', 'category' => Permission::CATEGORY_REPORT, 'resource' => 'report', 'action' => Permission::ACTION_MANAGE],
            ['name' => Permission::CREATE_REPORTS, 'display_name' => 'Create Reports', 'category' => Permission::CATEGORY_REPORT, 'resource' => 'report', 'action' => Permission::ACTION_CREATE],
        ];

        $createdPermissions = [];
        foreach ($permissions as $permissionData) {
            $permissionData['is_active'] = true;
            $permission = $this->create($permissionData);
            $createdPermissions[] = $permission;
        }

        return $createdPermissions;
    }

    /**
     * Check if permission exists and is active.
     *
     * @param string $name Permission name
     * @return bool
     */
    public function permissionExistsAndActive(string $name): bool
    {
        return $this->exists([
            'name' => $name,
            'is_active' => true
        ]);
    }

    /**
     * Search permissions by name or display name.
     *
     * @param string $query Search query
     * @param int $perPage Number of records per page
     * @return LengthAwarePaginator
     */
    public function searchPermissions(string $query, int $perPage = 20): LengthAwarePaginator
    {
        return $this->newQuery()
            ->with(['roles'])
            ->where('is_active', true)
            ->where(function ($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                  ->orWhere('display_name', 'LIKE', "%{$query}%")
                  ->orWhere('description', 'LIKE', "%{$query}%");
            })
            ->orderBy('category', 'asc')
            ->orderBy('resource', 'asc')
            ->orderBy('action', 'asc')
            ->paginate($perPage);
    }

    /**
     * Get permissions grouped by category.
     *
     * @return array
     */
    public function getGroupedByCategory(): array
    {
        $permissions = $this->newQuery()
            ->where('is_active', true)
            ->orderBy('category', 'asc')
            ->orderBy('resource', 'asc')
            ->orderBy('action', 'asc')
            ->get()
            ->groupBy('category')
            ->toArray();

        return $permissions;
    }

    /**
     * Soft delete permission.
     *
     * @param int $permissionId Permission ID
     * @return Model
     */
    public function softDelete(int $permissionId): Model
    {
        return $this->update($permissionId, ['is_active' => false]);
    }

    /**
     * Restore soft deleted permission.
     *
     * @param int $permissionId Permission ID
     * @return Model
     */
    public function restore(int $permissionId): Model
    {
        return $this->update($permissionId, ['is_active' => true]);
    }

    /**
     * Get unique categories.
     *
     * @return array
     */
    public function getUniqueCategories(): array
    {
        return $this->newQuery()
            ->select('category')
            ->where('is_active', true)
            ->distinct()
            ->pluck('category')
            ->toArray();
    }

    /**
     * Get unique resources.
     *
     * @return array
     */
    public function getUniqueResources(): array
    {
        return $this->newQuery()
            ->select('resource')
            ->where('is_active', true)
            ->distinct()
            ->pluck('resource')
            ->toArray();
    }

    /**
     * Get unique actions.
     *
     * @return array
     */
    public function getUniqueActions(): array
    {
        return $this->newQuery()
            ->select('action')
            ->where('is_active', true)
            ->distinct()
            ->pluck('action')
            ->toArray();
    }
}
