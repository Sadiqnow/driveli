<?php

namespace App\Repositories;

use App\Models\Role;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Role Repository
 *
 * Handles all data access operations for the Role model.
 * Provides specialized methods for role management and permission assignment.
 *
 * @package App\Repositories
 * @author DriveLink Development Team
 * @since 2.0.0
 */
class RoleRepository extends BaseRepository
{
    /**
     * {@inheritDoc}
     */
    protected function makeModel(): Model
    {
        return new Role();
    }

    /**
     * Get role by name.
     *
     * @param string $name Role name
     * @return Model|null
     */
    public function getByName(string $name): ?Model
    {
        return $this->findOneWhere(['name' => $name]);
    }

    /**
     * Get active roles.
     *
     * @param int $perPage Number of records per page
     * @return LengthAwarePaginator
     */
    public function getActive(int $perPage = 20): LengthAwarePaginator
    {
        return $this->search(
            ['is_active' => true],
            ['level' => 'desc', 'name' => 'asc'],
            $perPage,
            ['permissions', 'users']
        );
    }

    /**
     * Get roles by level.
     *
     * @param int $level Role level
     * @param int $perPage Number of records per page
     * @return LengthAwarePaginator
     */
    public function getByLevel(int $level, int $perPage = 20): LengthAwarePaginator
    {
        return $this->search(
            ['level' => $level],
            ['name' => 'asc'],
            $perPage,
            ['permissions', 'users']
        );
    }

    /**
     * Get roles at or above a specific level.
     *
     * @param int $level Minimum level
     * @param int $perPage Number of records per page
     * @return LengthAwarePaginator
     */
    public function getAtOrAboveLevel(int $level, int $perPage = 20): LengthAwarePaginator
    {
        return $this->newQuery()
            ->with(['permissions', 'users'])
            ->where('level', '>=', $level)
            ->where('is_active', true)
            ->orderBy('level', 'desc')
            ->orderBy('name', 'asc')
            ->paginate($perPage);
    }

    /**
     * Get super admin role.
     *
     * @return Model|null
     */
    public function getSuperAdminRole(): ?Model
    {
        return $this->getByName(Role::SUPER_ADMIN);
    }

    /**
     * Get admin roles.
     *
     * @param int $perPage Number of records per page
     * @return LengthAwarePaginator
     */
    public function getAdminRoles(int $perPage = 20): LengthAwarePaginator
    {
        return $this->getAtOrAboveLevel(Role::LEVEL_ADMIN, $perPage);
    }

    /**
     * Assign permission to role.
     *
     * @param int $roleId Role ID
     * @param int $permissionId Permission ID
     * @param int|null $assignedBy User ID who assigned the permission
     * @return Model
     */
    public function assignPermission(int $roleId, int $permissionId, ?int $assignedBy = null): Model
    {
        $role = $this->findOrFail($roleId);
        $role->givePermission($permissionId, $assignedBy ? \App\Models\AdminUser::find($assignedBy) : null);

        return $role->fresh(['permissions']);
    }

    /**
     * Revoke permission from role.
     *
     * @param int $roleId Role ID
     * @param int $permissionId Permission ID
     * @return Model
     */
    public function revokePermission(int $roleId, int $permissionId): Model
    {
        $role = $this->findOrFail($roleId);
        $role->revokePermission($permissionId);

        return $role->fresh(['permissions']);
    }

    /**
     * Get roles with specific permission.
     *
     * @param string $permissionName Permission name
     * @param int $perPage Number of records per page
     * @return LengthAwarePaginator
     */
    public function getWithPermission(string $permissionName, int $perPage = 20): LengthAwarePaginator
    {
        return $this->newQuery()
            ->with(['permissions', 'users'])
            ->whereHas('activePermissions', function ($query) use ($permissionName) {
                $query->where('name', $permissionName);
            })
            ->where('is_active', true)
            ->orderBy('level', 'desc')
            ->paginate($perPage);
    }

    /**
     * Get role hierarchy.
     *
     * @return Collection
     */
    public function getHierarchy(): Collection
    {
        return $this->newQuery()
            ->with(['permissions', 'users'])
            ->where('is_active', true)
            ->orderBy('level', 'desc')
            ->orderBy('name', 'asc')
            ->get();
    }

    /**
     * Get roles statistics.
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
            'by_level' => $this->newQuery()
                ->selectRaw('level, COUNT(*) as count')
                ->groupBy('level')
                ->pluck('count', 'level')
                ->toArray(),
            'with_users' => $this->newQuery()
                ->whereHas('users')
                ->count(),
        ];
    }

    /**
     * Create default roles.
     *
     * @return array Created roles
     */
    public function createDefaultRoles(): array
    {
        $roles = [
            [
                'name' => Role::SUPER_ADMIN,
                'display_name' => 'Super Administrator',
                'description' => 'Full system access with all permissions',
                'level' => Role::LEVEL_SUPER_ADMIN,
                'is_active' => true,
            ],
            [
                'name' => Role::ADMIN,
                'display_name' => 'Administrator',
                'description' => 'Administrative access to most system features',
                'level' => Role::LEVEL_ADMIN,
                'is_active' => true,
            ],
            [
                'name' => Role::MODERATOR,
                'display_name' => 'Moderator',
                'description' => 'Limited administrative access',
                'level' => Role::LEVEL_MODERATOR,
                'is_active' => true,
            ],
            [
                'name' => Role::VIEWER,
                'display_name' => 'Viewer',
                'description' => 'Read-only access to system data',
                'level' => Role::LEVEL_USER,
                'is_active' => true,
            ],
        ];

        $createdRoles = [];
        foreach ($roles as $roleData) {
            $role = $this->create($roleData);
            $createdRoles[] = $role;
        }

        return $createdRoles;
    }

    /**
     * Check if role exists and is active.
     *
     * @param string $name Role name
     * @return bool
     */
    public function roleExistsAndActive(string $name): bool
    {
        return $this->exists([
            'name' => $name,
            'is_active' => true
        ]);
    }

    /**
     * Get roles that can be assigned by a user with a specific role.
     *
     * @param Role $userRole The user's role
     * @param int $perPage Number of records per page
     * @return LengthAwarePaginator
     */
    public function getAssignableByRole(Role $userRole, int $perPage = 20): LengthAwarePaginator
    {
        return $this->newQuery()
            ->with(['permissions', 'users'])
            ->where('level', '<=', $userRole->level)
            ->where('is_active', true)
            ->orderBy('level', 'desc')
            ->orderBy('name', 'asc')
            ->paginate($perPage);
    }

    /**
     * Soft delete role.
     *
     * @param int $roleId Role ID
     * @return Model
     */
    public function softDelete(int $roleId): Model
    {
        return $this->update($roleId, ['is_active' => false]);
    }

    /**
     * Restore soft deleted role.
     *
     * @param int $roleId Role ID
     * @return Model
     */
    public function restore(int $roleId): Model
    {
        return $this->update($roleId, ['is_active' => true]);
    }
}
