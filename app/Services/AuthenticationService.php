<?php

namespace App\Services;

use App\Models\AdminUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthenticationService
{
    /**
     * Attempt to authenticate an admin user
     */
    public function authenticateAdmin(array $credentials, bool $remember = false): bool
    {
        // Ensure only active admins can authenticate
        $email = $credentials['email'] ?? null;
        if (!$email) return false;

        $admin = AdminUser::where('email', $email)->first();
        if (!$admin) {
            return false;
        }

        // Respect the status field; only allow 'Active' admins to login
        if (isset($admin->status) && strtolower($admin->status) !== 'active') {
            return false;
        }

        return Auth::guard('admin')->attempt($credentials, $remember);
    }

    /**
     * Log successful authentication attempt
     */
    public function logSuccessfulLogin(string $email, Request $request): void
    {
        Log::info('Admin login successful', [
            'email' => $email,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'timestamp' => now()->toISOString()
        ]);
    }

    /**
     * Log failed authentication attempt
     */
    public function logFailedLogin(string $email, Request $request): void
    {
        Log::warning('Admin login failed', [
            'email' => $email,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'timestamp' => now()->toISOString()
        ]);
    }

    /**
     * Increment failed login counter for an email (works even if admin doesn't exist)
     * and emit an alert when threshold is reached.
     */
    public function incrementFailedLoginByEmail(string $email, Request $request): void
    {
        try {
            $key = 'admin_failed_login:' . md5((string)$email . '|' . $request->ip());
            $attempts = (int) cache()->get($key, 0) + 1;
            cache()->put($key, $attempts, now()->addMinutes(15));

            Log::warning('Failed login attempt (email key)', [
                'email' => $email,
                'ip' => $request->ip(),
                'timestamp' => now(),
                'attempts' => $attempts
            ]);

            $threshold = \App\Constants\DrivelinkConstants::AUTH_RATE_LIMIT_ATTEMPTS ?? 5;
            if ($attempts >= $threshold) {
                Log::alert('Potential brute force attack detected', [
                    'email' => $email,
                    'ip' => $request->ip(),
                    'attempts' => $attempts
                ]);
            }
        } catch (\Exception $e) {
            Log::warning('Failed login attempt (cache unavailable)', [
                'email' => $email,
                'ip' => $request->ip(),
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Handle admin logout
     */
    public function logout(Request $request): void
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }

    /**
     * Check if password reset attempts are rate limited
     */
    public function isPasswordResetRateLimited(string $ip): bool
    {
        $key = 'password_reset:' . $ip;
        return Cache::get($key, 0) >= 3;
    }

    /**
     * Increment password reset rate limit counter
     */
    public function incrementPasswordResetAttempts(string $ip): void
    {
        $key = 'password_reset:' . $ip;
        Cache::put($key, Cache::get($key, 0) + 1, now()->addHours(1));
    }

    /**
     * Validate and sanitize email input
     */
    public function validateAndSanitizeEmail(string $email): string
    {
        $email = filter_var(strtolower(trim($email)), FILTER_SANITIZE_EMAIL);
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Invalid email format.');
        }

        return $email;
    }

    /**
     * Generate secure password reset token
     */
    public function generatePasswordResetToken(): string
    {
        return bin2hex(random_bytes(32));
    }

    /**
     * Store password reset token securely
     */
    public function storePasswordResetToken(string $email, string $token, Request $request): bool
    {
        try {
            DB::table('password_resets')->updateOrInsert(
                ['email' => $email],
                [
                    'token' => Hash::make($token),
                    'created_at' => now(),
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent()
                ]
            );
            return true;
        } catch (\Exception $e) {
            Log::error('Password reset token storage failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Find active admin by email
     */
    public function findActiveAdmin(string $email): ?AdminUser
    {
        return AdminUser::where('email', $email)
            ->where('status', 'Active')
            ->first();
    }

    /**
     * Generate password reset URL
     */
    public function generatePasswordResetUrl(string $token, string $email): string
    {
        return url('/admin/reset-password/' . $token . '?email=' . urlencode($email));
    }

    /**
     * Log password reset request for security monitoring
     */
    public function logPasswordResetRequest(string $email, Request $request): void
    {
        Log::info('Password reset requested', [
            'email' => $email,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'timestamp' => now()->toISOString()
        ]);
    }

    /**
     * Verify password reset token
     */
    public function verifyPasswordResetToken(string $email, string $token): bool
    {
        $reset = DB::table('password_resets')
            ->where('email', $email)
            ->first();

        if (!$reset) {
            return false;
        }

        // Check if token is not expired (24 hours)
        if (now()->diffInHours($reset->created_at) > 24) {
            DB::table('password_resets')->where('email', $email)->delete();
            return false;
        }

        return Hash::check($token, $reset->token);
    }

    /**
     * Reset admin password
     */
    public function resetPassword(string $email, string $password): bool
    {
        try {
            $admin = AdminUser::where('email', $email)->first();
            
            if (!$admin) {
                return false;
            }

            $admin->update([
                'password' => Hash::make($password),
                'password_changed_at' => now(),
                'updated_at' => now()
            ]);

            // Remove the password reset token
            DB::table('password_resets')->where('email', $email)->delete();

            Log::info('Password reset completed', [
                'email' => $email,
                'admin_id' => $admin->id,
                'timestamp' => now()->toISOString()
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Password reset failed: ' . $e->getMessage(), [
                'email' => $email
            ]);
            return false;
        }
    }

    /**
     * Check if admin registration is allowed
     */
    public function isRegistrationAllowed(): bool
    {
        $adminCount = AdminUser::count();
        
        // Allow registration only if no admins exist or in local/testing environment
        // Temporarily always allow for development
        return true; // $adminCount === 0 || app()->environment(['local', 'testing']);
    }

    /**
     * Register new admin user
     */
    public function registerAdmin(array $data): AdminUser
    {
        // Temporarily disable registration check for development
        // if (!$this->isRegistrationAllowed()) {
        //     throw new \Exception('Registration is disabled.');
        // }

        try {
            $isFirstAdmin = AdminUser::count() === 0;
            // Respect an explicitly provided role in the registration payload.
            // Only default to 'Super Admin' if this is the first admin and no role was provided.
            $rawRole = $data['role'] ?? null;

            // Normalize common role representations to canonical display names
            $roleMap = [
                'super_admin' => 'Super Admin',
                'super admin' => 'Super Admin',
                'super-admin' => 'Super Admin',
                'Super Admin' => 'Super Admin',
                'admin' => 'Admin',
                'Admin' => 'Admin',
                'manager' => 'Manager',
                'Manager' => 'Manager',
                'moderator' => 'Moderator',
                'Moderator' => 'Moderator'
            ];

            if ($rawRole && is_string($rawRole)) {
                $key = trim($rawRole);
                $keyLower = strtolower($key);
                // Try direct match first
                $role = $roleMap[$key] ?? $roleMap[$keyLower] ?? null;
            } else {
                $role = null;
            }

            if (!$role) {
                $role = $isFirstAdmin ? 'Super Admin' : 'Admin';
            }
            
            $admin = AdminUser::create([
                'name' => $data['name'],
                'email' => $this->validateAndSanitizeEmail($data['email']),
                'password' => Hash::make($data['password']),
                'phone' => $data['phone'] ?? null,
                'role' => $role,
                'status' => 'Active',
                'is_active' => true,
                'permissions' => $this->getDefaultPermissions($role),
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now()
            ]);

            Log::info('New admin registered', [
                'admin_id' => $admin->id,
                'email' => $admin->email,
                'role' => $admin->role,
                'timestamp' => now()->toISOString()
            ]);

            return $admin;
        } catch (\Exception $e) {
            Log::error('Admin registration failed: ' . $e->getMessage(), [
                'email' => $data['email'] ?? 'unknown'
            ]);
            throw $e;
        }
    }

    /**
     * Get default permissions for a role
     */
    private function getDefaultPermissions(string $role): array
    {
        switch ($role) {
            case 'Super Admin':
                return [
                    'manage_users',
                    'manage_drivers',
                    'manage_companies',
                    'manage_requests',
                    'manage_matches',
                    'manage_commissions',
                    'view_reports',
                    'manage_notifications',
                    'manage_settings',
                    'delete_records'
                ];
                
            case 'Admin':
                return [
                    'manage_users',
                    'manage_drivers',
                    'manage_companies',
                    'manage_requests',
                    'view_reports',
                    'manage_notifications'
                ];
                
            case 'Moderator':
                return [
                    'manage_drivers',
                    'manage_requests',
                    'view_reports'
                ];
                
            default:
                return [
                    'view_reports'
                ];
        }
    }

    /**
     * Get authentication statistics
     */
    public function getAuthStats(): array
    {
        return [
            'total_admins' => AdminUser::count(),
            'active_admins' => AdminUser::where('status', 'Active')->count(),
            'recent_logins' => AdminUser::where('last_login_at', '>=', now()->subDays(7))->count(),
            'password_resets_today' => DB::table('password_resets')
                ->whereDate('created_at', today())
                ->count()
        ];
    }
}