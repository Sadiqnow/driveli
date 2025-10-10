<?php

namespace App\Services;

use App\Models\AdminUser;
use App\Models\DriverNormalized;
use App\Models\Company;
use App\Models\CompanyRequest;
use App\Models\DriverMatch;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class AdminService
{
    /**
     * Create a new admin user.
     *
     * @param array $data
     * @return AdminUser
     */
    public function createAdmin(array $data): AdminUser
    {
        return DB::transaction(function () use ($data) {
            $admin = AdminUser::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'phone' => $data['phone'],
                'role' => $data['role'],
                'status' => 'Active',
                'permissions' => $data['permissions'] ?? null,
            ]);

            // Handle avatar upload
            if (isset($data['avatar'])) {
                $avatarPath = $this->storeAdminAvatar($data['avatar']);
                $admin->update(['avatar' => $avatarPath]);
            }

            return $admin;
        });
    }

    /**
     * Update admin user information.
     *
     * @param AdminUser $admin
     * @param array $data
     * @return AdminUser
     */
    public function updateAdmin(AdminUser $admin, array $data): AdminUser
    {
        return DB::transaction(function () use ($admin, $data) {
            $updateData = array_filter([
                'name' => $data['name'] ?? $admin->name,
                'email' => $data['email'] ?? $admin->email,
                'phone' => $data['phone'] ?? $admin->phone,
                'role' => $data['role'] ?? $admin->role,
                'status' => $data['status'] ?? $admin->status,
                'permissions' => $data['permissions'] ?? $admin->permissions,
            ]);

            // Update password if provided
            if (isset($data['password'])) {
                $updateData['password'] = Hash::make($data['password']);
            }

            $admin->update($updateData);

            // Handle avatar update
            if (isset($data['avatar'])) {
                $this->updateAdminAvatar($admin, $data['avatar']);
            }

            return $admin->fresh();
        });
    }

    /**
     * Get admin dashboard statistics.
     *
     * @return array
     */
    public function getDashboardStats(): array
    {
        return Cache::remember('admin_dashboard_stats', 1800, function () {
            return [
            'drivers' => [
                'total' => DriverNormalized::count(),
                'verified' => DriverNormalized::verified()->count(),
                'pending' => DriverNormalized::where('verification_status', 'pending')->count(),
                'active' => DriverNormalized::active()->count(),
                'recent' => DriverNormalized::where('created_at', '>=', now()->subDays(7))->count(),
            ],
            'companies' => [
                'total' => Company::count(),
                'verified' => Company::where('verification_status', 'Verified')->count(),
                'pending' => Company::where('verification_status', 'Pending')->count(),
                'active' => Company::where('status', 'Active')->count(),
                'recent' => Company::where('created_at', '>=', now()->subDays(7))->count(),
            ],
            'requests' => [
                'total' => CompanyRequest::count(),
                'active' => CompanyRequest::where('status', 'Active')->count(),
                'pending' => CompanyRequest::where('status', 'Pending')->count(),
                'completed' => CompanyRequest::where('status', 'Completed')->count(),
                'recent' => CompanyRequest::where('created_at', '>=', now()->subDays(7))->count(),
            ],
            'matches' => [
                'total' => DriverMatch::count(),
                'confirmed' => DriverMatch::where('status', 'Confirmed')->count(),
                'completed' => DriverMatch::where('status', 'Completed')->count(),
                'cancelled' => DriverMatch::where('status', 'Cancelled')->count(),
                'recent' => DriverMatch::where('created_at', '>=', now()->subDays(7))->count(),
            ],
            'activity' => [
                'new_drivers_today' => DriverNormalized::whereDate('created_at', today())->count(),
                'verifications_today' => DriverNormalized::whereDate('verified_at', today())->count(),
                'new_companies_today' => Company::whereDate('created_at', today())->count(),
                'new_requests_today' => CompanyRequest::whereDate('created_at', today())->count(),
            ],
            'system' => [
                'last_updated' => now()->toISOString(),
                'cache_status' => 'active'
            ]
        ];
        });
    }

    /**
     * Get recent activity for admin dashboard.
     *
     * @param int $limit
     * @return array
     */
    public function getRecentActivity(int $limit = 20): array
    {
        return Cache::remember("admin_recent_activity_{$limit}", 900, function () use ($limit) {
            $activities = collect();

        // Recent driver registrations
        $recentDrivers = DriverNormalized::select('id', 'driver_id', 'first_name', 'surname', 'created_at')
            ->latest()
            ->limit($limit / 4)
            ->get()
            ->map(function ($driver) {
                return [
                    'type' => 'driver_registration',
                    'title' => 'New Driver Registration',
                    'description' => "Driver {$driver->first_name} {$driver->surname} ({$driver->driver_id}) registered",
                    'timestamp' => $driver->created_at,
                    'icon' => 'user-plus',
                    'color' => 'success',
                ];
            });

        // Recent driver verifications
        $recentVerifications = DriverNormalized::select('id', 'driver_id', 'first_name', 'surname', 'verified_at')
            ->whereNotNull('verified_at')
            ->latest('verified_at')
            ->limit($limit / 4)
            ->get()
            ->map(function ($driver) {
                return [
                    'type' => 'driver_verification',
                    'title' => 'Driver Verified',
                    'description' => "Driver {$driver->first_name} {$driver->surname} ({$driver->driver_id}) was verified",
                    'timestamp' => $driver->verified_at,
                    'icon' => 'check-circle',
                    'color' => 'info',
                ];
            });

        // Recent company registrations
        $recentCompanies = Company::select('id', 'company_id', 'name', 'created_at')
            ->latest()
            ->limit($limit / 4)
            ->get()
            ->map(function ($company) {
                return [
                    'type' => 'company_registration',
                    'title' => 'New Company Registration',
                    'description' => "Company {$company->name} ({$company->company_id}) registered",
                    'timestamp' => $company->created_at,
                    'icon' => 'building',
                    'color' => 'warning',
                ];
            });

        // Recent requests
        $recentRequests = CompanyRequest::with('company')
            ->select('id', 'company_id', 'status', 'created_at')
            ->latest()
            ->limit($limit / 4)
            ->get()
            ->map(function ($request) {
                return [
                    'type' => 'company_request',
                    'title' => 'New Driver Request',
                    'description' => "Company {$request->company->name} submitted a new request",
                    'timestamp' => $request->created_at,
                    'icon' => 'file-text',
                    'color' => 'primary',
                ];
            });

        // Merge and sort activities
        $activities = $activities
            ->merge($recentDrivers)
            ->merge($recentVerifications)
            ->merge($recentCompanies)
            ->merge($recentRequests)
            ->sortByDesc('timestamp')
            ->take($limit)
            ->values();

            return $activities->toArray();
        });
    }

    /**
     * Get admin users with filters and pagination.
     *
     * @param array $filters
     * @param int $perPage
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getAdmins(array $filters = [], int $perPage = 15)
    {
        $query = AdminUser::query();

        // Apply filters
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['role'])) {
            $query->where('role', $filters['role']);
        }

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Apply sorting
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        return $query->paginate($perPage);
    }

    /**
     * Authenticate admin user.
     *
     * @param string $email
     * @param string $password
     * @param bool $remember
     * @return AdminUser|null
     */
    public function authenticateAdmin(string $email, string $password, bool $remember = false): ?AdminUser
    {
        $admin = AdminUser::where('email', $email)->first();

        if ($admin && Hash::check($password, $admin->password) && $admin->isActive()) {
            // Update last login information
            $admin->updateLastLogin();

            return $admin;
        }

        return null;
    }

    /**
     * Change admin password.
     *
     * @param AdminUser $admin
     * @param string $currentPassword
     * @param string $newPassword
     * @return bool
     */
    public function changePassword(AdminUser $admin, string $currentPassword, string $newPassword): bool
    {
        if (!Hash::check($currentPassword, $admin->password)) {
            return false;
        }

        $admin->update(['password' => Hash::make($newPassword)]);
        return true;
    }

    /**
     * Update admin permissions.
     *
     * @param AdminUser $admin
     * @param array $permissions
     * @return AdminUser
     */
    public function updatePermissions(AdminUser $admin, array $permissions): AdminUser
    {
        if ($admin->isSuperAdmin()) {
            throw new \InvalidArgumentException('Cannot modify Super Admin permissions.');
        }

        $admin->update(['permissions' => $permissions]);
        return $admin;
    }

    /**
     * Deactivate admin user.
     *
     * @param AdminUser $admin
     * @return AdminUser
     */
    public function deactivateAdmin(AdminUser $admin): AdminUser
    {
        if ($admin->isSuperAdmin()) {
            throw new \InvalidArgumentException('Cannot deactivate Super Admin.');
        }

        $admin->update(['status' => 'Inactive']);
        return $admin;
    }

    /**
     * Get admin performance metrics.
     *
     * @param AdminUser $admin
     * @return array
     */
    public function getAdminPerformance(AdminUser $admin): array
    {
        return [
            'drivers_verified' => $admin->verifiedDrivers()->count(),
            'drivers_verified_this_month' => $admin->verifiedDrivers()
                ->where('verified_at', '>=', now()->startOfMonth())
                ->count(),
            'last_login' => $admin->last_login_at,
            'login_frequency' => $this->calculateLoginFrequency($admin),
            'permissions_count' => count($admin->permissions ?? []),
            'role' => $admin->role,
            'status' => $admin->status,
        ];
    }

    /**
     * Store admin avatar file.
     *
     * @param UploadedFile $file
     * @return string
     */
    private function storeAdminAvatar(UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();
        $filename = Str::uuid() . '.' . $extension;
        
        return $file->storeAs('admins/avatars', $filename, 'public');
    }

    /**
     * Update admin avatar.
     *
     * @param AdminUser $admin
     * @param UploadedFile $file
     * @return void
     */
    private function updateAdminAvatar(AdminUser $admin, UploadedFile $file): void
    {
        // Delete old avatar
        if ($admin->avatar) {
            Storage::disk('public')->delete($admin->avatar);
        }

        // Store new avatar
        $path = $this->storeAdminAvatar($file);
        $admin->update(['avatar' => $path]);
    }

    /**
     * Calculate admin login frequency.
     *
     * @param AdminUser $admin
     * @return string
     */
    private function calculateLoginFrequency(AdminUser $admin): string
    {
        if (!$admin->last_login_at) {
            return 'Never logged in';
        }

        $daysSinceLastLogin = $admin->last_login_at->diffInDays(now());

        if ($daysSinceLastLogin === 0) {
            return 'Active today';
        } elseif ($daysSinceLastLogin <= 7) {
            return 'Active this week';
        } elseif ($daysSinceLastLogin <= 30) {
            return 'Active this month';
        } else {
            return 'Inactive';
        }
    }

    /**
     * Clear admin dashboard caches
     */
    public function clearAdminCaches(): void
    {
        $cacheKeys = [
            'admin_dashboard_stats',
        ];

        foreach ($cacheKeys as $key) {
            Cache::forget($key);
        }

        // Clear recent activity caches (multiple variations)
        for ($i = 10; $i <= 50; $i += 10) {
            Cache::forget("admin_recent_activity_{$i}");
        }

        Log::info('Admin caches cleared');
    }

    /**
     * Get system performance metrics
     */
    public function getSystemMetrics(): array
    {
        return Cache::remember('system_performance_metrics', 3600, function () {
            return [
                'database_health' => $this->checkDatabaseHealth(),
                'cache_status' => $this->checkCacheStatus(),
                'verification_rate' => $this->calculateVerificationRate(),
                'response_time' => $this->getAverageResponseTime(),
                'profile_completion_rate' => $this->getProfileCompletionRate(),
            ];
        });
    }

    private function checkDatabaseHealth(): bool
    {
        try {
            DB::connection()->getPdo();
            return true;
        } catch (\Exception $e) {
            Log::error('Database health check failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    private function checkCacheStatus(): bool
    {
        try {
            $testKey = 'health_check_' . time();
            Cache::put($testKey, 'test', 60);
            $result = Cache::get($testKey) === 'test';
            Cache::forget($testKey);
            return $result;
        } catch (\Exception $e) {
            Log::error('Cache health check failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    private function calculateVerificationRate(): float
    {
        $total = DriverNormalized::count();
        if ($total === 0) return 0;
        
        $verified = DriverNormalized::verified()->count();
        return round(($verified / $total) * 100, 2);
    }

    private function getAverageResponseTime(): float
    {
        $avgTime = DriverNormalized::whereNotNull('verified_at')
            ->whereNotNull('created_at')
            ->select(DB::raw('AVG(TIMESTAMPDIFF(HOUR, created_at, verified_at)) as avg_hours'))
            ->value('avg_hours');

        return round($avgTime ?? 0, 1);
    }

    private function getProfileCompletionRate(): float
    {
        $total = DriverNormalized::count();
        if ($total === 0) return 0;

        $complete = DriverNormalized::whereNotNull('profile_picture')
            ->whereNotNull('nin_number')
            ->whereNotNull('license_number')
            ->whereHas('locations')
            ->whereHas('nextOfKin')
            ->whereHas('bankingDetails')
            ->count();

        return round(($complete / $total) * 100, 2);
    }
}