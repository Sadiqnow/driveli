<?php

namespace App\Repositories;

use App\Models\AdminUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Admin User Repository
 * 
 * Handles all data access operations for the AdminUser model.
 * Provides specialized methods for admin user queries and operations.
 * 
 * @package App\Repositories
 * @author DriveLink Development Team
 * @since 2.0.0
 */
class AdminUserRepository extends BaseRepository
{
    /**
     * {@inheritDoc}
     */
    protected function makeModel(): Model
    {
        return new AdminUser();
    }

    /**
     * Get admin users by role.
     *
     * @param string $role Admin role
     * @param int $perPage Number of records per page
     * @return LengthAwarePaginator
     */
    public function getByRole(string $role, int $perPage = 15): LengthAwarePaginator
    {
        return $this->search(
            ['role' => $role],
            ['created_at' => 'desc'],
            $perPage,
            ['roles', 'permissions']
        );
    }

    /**
     * Get active admin users.
     *
     * @param int|null $perPage Number of records per page (null for all)
     * @return Collection|LengthAwarePaginator
     */
    public function getActive(?int $perPage = null)
    {
        return $this->search(
            ['is_active' => true],
            ['created_at' => 'desc'],
            $perPage,
            ['roles']
        );
    }

    /**
     * Search admin users with filters.
     *
     * @param array $filters Search filters
     * @param int $perPage Number of records per page
     * @return LengthAwarePaginator
     */
    public function searchAdmins(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        $query = $this->newQuery()->with(['roles', 'permissions']);

        // Apply search term
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }

        // Apply role filter
        if (!empty($filters['role'])) {
            $query->where('role', $filters['role']);
        }

        // Apply status filter
        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        // Apply sorting
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        return $query->paginate($perPage);
    }

    /**
     * Find admin by email.
     *
     * @param string $email Admin email
     * @return Model|null
     */
    public function findByEmail(string $email): ?Model
    {
        return $this->findOneBy('email', $email, ['*'], ['roles', 'permissions']);
    }

    /**
     * Check if email exists.
     *
     * @param string $email Email to check
     * @param int|null $excludeId Admin ID to exclude from check
     * @return bool
     */
    public function emailExists(string $email, ?int $excludeId = null): bool
    {
        $query = $this->newQuery()->where('email', $email);
        
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        
        return $query->exists();
    }

    /**
     * Get admin statistics.
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
            'super_admins' => (clone $baseQuery)->where('role', 'super_admin')->count(),
            'admins' => (clone $baseQuery)->where('role', 'admin')->count(),
            'viewers' => (clone $baseQuery)->where('role', 'viewer')->count(),
        ];
    }

    /**
     * Update admin status.
     *
     * @param int $adminId Admin ID
     * @param bool $isActive Active status
     * @return Model
     */
    public function updateStatus(int $adminId, bool $isActive): Model
    {
        return $this->update($adminId, ['is_active' => $isActive]);
    }

    /**
     * Update admin role.
     *
     * @param int $adminId Admin ID
     * @param string $role New role
     * @return Model
     */
    public function updateRole(int $adminId, string $role): Model
    {
        return $this->update($adminId, ['role' => $role]);
    }

    /**
     * Get admins with specific permission.
     *
     * @param string $permission Permission name
     * @param int $perPage Number of records per page
     * @return LengthAwarePaginator
     */
    public function getWithPermission(string $permission, int $perPage = 20): LengthAwarePaginator
    {
        $query = $this->newQuery()
            ->with(['roles', 'permissions'])
            ->whereHas('permissions', function ($q) use ($permission) {
                $q->where('name', $permission);
            })
            ->orderBy('created_at', 'desc');

        return $query->paginate($perPage);
    }
}
