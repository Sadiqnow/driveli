<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Cache;
use App\Models\UserActivity;
use App\Models\Drivers;
use Exception;
// // use App\Models\CompanyRequest;

// // use App\Models\notifications as Notification;
// // use App\Helpers\DrivelinkHelper;

class AdminUser extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name', 'email', 'password', 'phone', 'role', 'status',
        'permissions', 'avatar', 'is_active', 'last_login_at', 'last_login_ip'
    ];

    protected $guarded = [
        'email_verified_at', 'remember_token'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'permissions' => 'array',
        'is_active' => 'boolean',
    ];


    /**
     * Sanitize and validate model data before saving
     */
    private function sanitizeAndValidateData()
    {
        // Sanitize name - prevent XSS and ensure reasonable length
        if ($this->name) {
            $this->name = trim(strip_tags($this->name));
            if (strlen($this->name) > 255) {
                throw new \InvalidArgumentException('Name exceeds maximum length of 255 characters');
            }
            // Additional XSS protection
            $this->name = htmlspecialchars($this->name, ENT_QUOTES, 'UTF-8');
        }

        // Validate and sanitize email
        if ($this->email) {
            $this->email = strtolower(trim($this->email));
            if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
                throw new \InvalidArgumentException('Invalid email format');
            }
            if (strlen($this->email) > 255) {
                throw new \InvalidArgumentException('Email exceeds maximum length');
            }
        }

        // Sanitize phone - enhanced validation
        if ($this->phone) {
            $this->phone = preg_replace('/[^\d\+\-\(\)\s]/', '', $this->phone);
            // Accept longer international formats used in tests (e.g. +234...)
            if (strlen($this->phone) > 20) {
                throw new \InvalidArgumentException('Phone number exceeds maximum length');
            }
        }

        // Validate role using canonical roles from constants (case-insensitive)
        try {
            $allowedRoles = \App\Constants\DrivelinkConstants::getAdminRoles();
        } catch (\Throwable $e) {
            // Fallback to a safe default if constants are missing
            $allowedRoles = ['super_admin', 'admin', 'manager', 'operator', 'viewer'];
        }

        if ($this->role) {
            $roleNormalized = strtolower(str_replace(' ', '_', trim((string)$this->role)));
            $allowedLower = array_map('strtolower', $allowedRoles);
            if (!in_array($roleNormalized, $allowedLower, true)) {
                throw new \InvalidArgumentException('Invalid role specified');
            }
        }

        // Validate status using constants (case-insensitive)
        $allowedStatuses = [
            \App\Constants\DrivelinkConstants::ADMIN_STATUS_ACTIVE ?? 'active',
            \App\Constants\DrivelinkConstants::ADMIN_STATUS_INACTIVE ?? 'inactive',
            \App\Constants\DrivelinkConstants::ADMIN_STATUS_SUSPENDED ?? 'suspended',
        ];

        if ($this->status) {
            $statusNormalized = strtolower(trim((string)$this->status));
            $allowedStatusLower = array_map('strtolower', $allowedStatuses);
            if (!in_array($statusNormalized, $allowedStatusLower, true)) {
                throw new \InvalidArgumentException('Invalid status specified');
            }
        }

        // Sanitize permissions array if present
        if ($this->permissions && is_array($this->permissions)) {
            $this->permissions = array_map(function($permission) {
                return trim(strip_tags($permission));
            }, $this->permissions);
        }
    }

    // Password hashing mutator - secure implementation
    public function setPasswordAttribute($value)
    {
        // Only hash if value is not empty and doesn't look like an existing hash
        if (!empty($value) && !$this->isHashedPassword($value)) {
            $this->attributes['password'] = \Illuminate\Support\Facades\Hash::make($value);
        } elseif (!empty($value)) {
            $this->attributes['password'] = $value;
        }
    }

    /**
     * Check if a password is already hashed
     */
    private function isHashedPassword(string $password): bool
    {
        // Check for bcrypt hashes ($2y$, $2a$, $2b$)
        if (preg_match('/^\$2[ayb]\$\d+\$.{53}$/', $password)) {
            return true;
        }
        
        // Check for Argon2ID hashes
        if (preg_match('/^\$argon2id\$v=\d+\$m=\d+,t=\d+,p=\d+\$.+/', $password)) {
            return true;
        }
        
        // Check for Argon2I hashes
        if (preg_match('/^\$argon2i\$v=\d+\$m=\d+,t=\d+,p=\d+\$.+/', $password)) {
            return true;
        }
        
        return false;
    }

    // Relationships with optimized queries
    public function createdRequests()
    {
        return $this->hasMany(\App\Models\CompanyRequest::class, 'created_by')
                    ->select(['id', 'created_by', 'request_id', 'status', 'created_at'])
                    ->orderBy('created_at', 'desc');
    }
    
    public function verifiedCompanies()
    {
        return $this->hasMany(\App\Models\Company::class, 'verified_by')
                    ->select(['id', 'verified_by', 'name', 'status', 'verified_at'])
                    ->orderBy('verified_at', 'desc');
    }
    
    public function verifiedDrivers()
    {
        return $this->hasMany(\App\Models\Drivers::class, 'verified_by')
                    ->select(['id', 'verified_by', 'first_name', 'surname', 'status', 'verification_status', 'nationality_id'])
                    ->with(['nationality:id,name'])
                    ->orderBy('created_at', 'desc');
    }

    /**
     * Get recent activities with limit to prevent memory issues
     */
    public function recentCreatedRequests($limit = 5)
    {
        return $this->createdRequests()->limit($limit);
    }

    public function recentVerifiedCompanies($limit = 5)
    {
        return $this->verifiedCompanies()->limit($limit);
    }

    public function recentVerifiedDrivers($limit = 5)
    {
        return $this->verifiedDrivers()->limit($limit);
    }

    /**
     * Future role system - ready for implementation when role tables are created
     * Uncomment these methods when roles, permissions, and role_user tables are available
     */
    
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_user', 'user_id', 'role_id')
                    ->withPivot(['assigned_at', 'assigned_by', 'expires_at', 'is_active'])
                    ->withTimestamps();
    }

    public function activeRoles(): BelongsToMany
    {
        return $this->roles()
                    ->wherePivot('is_active', true)
                    ->where(function ($query) {
                        $query->whereNull('role_user.expires_at')
                              ->orWhere('role_user.expires_at', '>', now());
                    });
    }

    // Enhanced Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'Active')
                     ->where('is_active', true);
    }

    public function scopeByRole($query, $role)
    {
        return $query->where('role', $role);
    }

    public function scopeOnline($query, $minutes = 15)
    {
        return $query->where('last_login_at', '>=', now()->subMinutes($minutes));
    }

    public function scopeRecentlyCreated($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public function scopeWithMinimalData($query)
    {
        return $query->select([
            'id', 'name', 'email', 'role', 'status', 'is_active', 
            'last_login_at', 'created_at'
        ]);
    }

    public function scopeForDashboard($query)
    {
        return $query->withMinimalData()
                     ->with(['recentCreatedRequests' => function($q) {
                         $q->limit(3);
                     }]);
    }

    // Accessors
    public function getInitialsAttribute()
    {
        $names = explode(' ', $this->name);
        $initials = '';
        foreach ($names as $name) {
            $initials .= substr($name, 0, 1);
        }
        return strtoupper(substr($initials, 0, 2));
    }

    /**
     * Get formatted phone number
     */
    public function getFormattedPhoneAttribute()
    {
        if (!$this->phone) return null;
        
        // Basic Nigerian phone formatting
        $phone = preg_replace('/[^\d\+]/', '', $this->phone);
        if (strlen($phone) === 11 && substr($phone, 0, 1) === '0') {
            return '+234' . substr($phone, 1);
        }
        return $phone;
    }

    // Role-based methods
    
    // Future role management methods - ready for implementation
    /*
    public function assignRole(string $role, ?AdminUser $assignedBy = null): self
    {
        // Implementation ready for when role system is activated
        return $this;
    }

    public function removeRole(string $role): self
    {
        // Implementation ready for when role system is activated
        return $this;
    }
    */
    
    // Legacy fallback method
    public function hasRole(string $role): bool
    {
        return $this->role === $role || strtolower(str_replace(' ', '_', $this->role)) === strtolower(str_replace(' ', '_', $role));
    }

    
    // Legacy fallback method
    public function hasAnyRole(array $roles): bool
    {
        return in_array($this->role, $roles);
    }

    
    // Legacy fallback method
    public function hasAllRoles(array $roles): bool
    {
        return count($roles) === 1 && $roles[0] === $this->role;
    }

    
    // Legacy fallback method
    public function getAllPermissions(): array
    {
        return $this->permissions ?? [];
    }

    /**
     * Check if user has permission (legacy version)
     */
    public function hasPermission($permission): bool
    {
        // Super admin has all permissions
        if ($this->role === 'super_admin') {
            return true;
        }

        // Check in direct permissions
        $allPermissions = $this->getAllPermissions();
        return in_array($permission, $allPermissions);
    }

    /**
     * Check if user has any of the given permissions
     */
    public function hasAnyPermission(array $permissions): bool
    {
        if ($this->role === 'super_admin') {
            return true;
        }

        $allPermissions = $this->getAllPermissions();
        return !empty(array_intersect($permissions, $allPermissions));
    }

    /**
     * Check if user has all of the given permissions
     */
    public function hasAllPermissions(array $permissions): bool
    {
        if ($this->role === 'super_admin') {
            return true;
        }

        $allPermissions = $this->getAllPermissions();
        return empty(array_diff($permissions, $allPermissions));
    }

    
    // Legacy fallback method
    public function getHighestRoleLevel(): int
    {
        // Simple role hierarchy based on role names
        $levels = [
            'Super Admin' => 100,
            'Admin' => 10,
            'Moderator' => 5,
            'Viewer' => 1
        ];
        return $levels[$this->role] ?? 1;
    }

    /**
     * Get role names as collection (for compatibility with layout)
     */
    public function getRoleNames()
    {
        return collect([$this->role ?? 'Administrator']);
    }

    /**
     * Check if user can manage another user (based on role levels)
     */
    public function canManage(AdminUser $user): bool
    {
        if ($this->role === 'Super Admin') {
            return true;
        }

        return $this->getHighestRoleLevel() > $user->getHighestRoleLevel();
    }

    public function updateLastLogin($ip = null)
    {
        $this->timestamps = false; // Don't update updated_at timestamp
        $this->update([
            'last_login_at' => now(),
            'last_login_ip' => $ip ?? request()->ip(),
        ]);
        $this->timestamps = true; // Re-enable timestamps
    }

    /**
     * Check if account is locked due to failed login attempts
     */
    public function isAccountLocked(): bool
    {
        // This would typically check against a separate table or cache
        // For now, return false as we don't have the failed_login_attempts field
        return false;
    }

    /**
     * Increment failed login attempts
     */
    public function incrementLoginAttempts(): void
    {
        // Track failed attempts in cache keyed by email+ip to keep this lightweight for tests
        try {
            $key = 'admin_failed_login:' . md5(($this->email ?? '') . '|' . request()->ip());
            $attempts = (int) Cache::get($key, 0) + 1;
            Cache::put($key, $attempts, now()->addMinutes(15));

            Log::warning('Failed login attempt', [
                'email' => $this->email,
                'ip' => request()->ip(),
                'timestamp' => now(),
                'attempts' => $attempts
            ]);

            // Alert if we've crossed the configured threshold
            $threshold = \App\Constants\DrivelinkConstants::AUTH_RATE_LIMIT_ATTEMPTS ?? 5;
            if ($attempts >= $threshold) {
                Log::alert('Potential brute force attack detected', [
                    'email' => $this->email,
                    'ip' => request()->ip(),
                    'attempts' => $attempts,
                ]);
            }
        } catch (\Exception $e) {
            // Fallback logging
            Log::warning('Failed login attempt (cache unavailable)', [
                'email' => $this->email,
                'ip' => request()->ip(),
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Reset failed login attempts after successful login
     */
    public function resetLoginAttempts(): void
    {
        // This would typically reset the failed_login_attempts field
        // For now, just log the successful reset
        \Log::info('Login attempts reset', [
            'email' => $this->email,
            'timestamp' => now()
        ]);
    }

    /**
     * Check if user can perform action (additional security layer)
     */
    public function canPerformAction(string $action): bool
    {
        // Check if user is active
        if (!$this->isActive()) {
            return false;
        }

        // Check if account is locked
        if ($this->isAccountLocked()) {
            return false;
        }

        // Check specific permissions
        return $this->hasPermission($action);
    }

    public function isActive()
    {
        return $this->status === 'Active';
    }
    
    public function isSuperAdmin()
    {
        $roleNormalized = strtolower(str_replace(' ', '_', $this->role));
        return $roleNormalized === 'super_admin';
    }

    // Activity logging relationships
    public function activities()
    {
        // return $this->hasMany(UserActivity::class, 'user_id');
        return $this->hasMany('App\Models\UserActivity', 'user_id');
    }

    public function recentActivities($limit = 10)
    {
        return $this->activities()
                    ->orderBy('created_at', 'desc')
                    ->limit($limit);
    }

    // Activity logging methods
    public function logActivity($action, $description, $model = null, $oldValues = null, $newValues = null)
    {
        // return UserActivity::log($action, $description, $model, $oldValues, $newValues);
        // Temporarily disabled until UserActivity model is available
        return null;
    }

    protected static function boot()
    {
        parent::boot();

        // Validate and sanitize data before saving
        static::saving(function ($model) {
            $model->sanitizeAndValidateData();
        });

        // Cache the table existence check to avoid repeated schema queries
        $hasUserActivitiesTable = null;

        // Activity logging with optimized error handling
        static::created(function ($model) use (&$hasUserActivitiesTable) {
            $model->logActivityIfTableExists('create', "User {$model->name} was created", $hasUserActivitiesTable);
        });

        static::updated(function ($model) use (&$hasUserActivitiesTable) {
            $changes = $model->getChanges();
            if (!empty($changes)) {
                unset($changes['updated_at']);
                if (!empty($changes)) {
                    $model->logActivityIfTableExists(
                        'update', 
                        "User {$model->name} was updated",
                        $hasUserActivitiesTable,
                        $model,
                        $model->getOriginal(),
                        $changes
                    );
                }
            }
        });

        static::deleting(function ($model) use (&$hasUserActivitiesTable) {
            $action = $model->isForceDeleting() ? 'force_delete' : 'delete';
            $description = $model->isForceDeleting() 
                ? "User {$model->name} was permanently deleted"
                : "User {$model->name} was deleted (soft delete)";
            
            $model->logActivityIfTableExists($action, $description, $hasUserActivitiesTable);
        });

        static::restored(function ($model) use (&$hasUserActivitiesTable) {
            $model->logActivityIfTableExists('restore', "User {$model->name} was restored", $hasUserActivitiesTable);
        });
    }

    /**
     * Log activity only if user_activities table exists (optimized version)
     */
    private function logActivityIfTableExists(string $action, string $description, &$hasTable, $model = null, $oldValues = null, $newValues = null)
    {
        try {
            // Cache the table existence check
            if ($hasTable === null) {
                $hasTable = \Schema::hasTable('user_activities');
            }

            if ($hasTable) {
                $this->logActivity($action, $description, $model, $oldValues, $newValues);
            }
        } catch (Exception $e) {
            // Log the error but don't break the application
            \Log::warning('Failed to log user activity', [
                'error' => $e->getMessage(),
                'user_id' => $this->id ?? null,
                'action' => $action
            ]);
        }
    }
}