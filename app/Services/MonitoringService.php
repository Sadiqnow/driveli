<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Models\AdminUser;
use App\Models\Drivers as Driver;
use App\Constants\DrivelinkConstants;

class MonitoringService
{
    /**
     * Log audit trail for admin actions
     */
    public function logAuditTrail(string $action, array $details, $user = null): void
    {
        $user = $user ?? auth()->user();
        
        $auditData = [
            'action' => $action,
            'user_id' => $user?->id,
            'user_type' => get_class($user ?? new \stdClass()),
            'user_email' => $user?->email,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now()->toDateTimeString(),
            'details' => $details,
        ];

        Log::channel('audit')->info('Admin action performed', $auditData);
    }

    /**
     * Log KYC events
     */
    public function logKycEvent(string $event, Driver $driver, array $details = []): void
    {
        $kycData = [
            'event' => $event,
            'driver_id' => $driver->driver_id,
            'driver_email' => $driver->email,
            'kyc_status' => $driver->kyc_status,
            'kyc_step' => $driver->kyc_step,
            'admin_id' => auth()->id(),
            'ip_address' => request()->ip(),
            'timestamp' => now()->toDateTimeString(),
            'details' => $details,
        ];

        Log::channel('kyc')->info("KYC event: {$event}", $kycData);
    }

    /**
     * Log OCR verification events
     */
    public function logOcrEvent(string $event, Driver $driver, array $ocrData = []): void
    {
        $logData = [
            'event' => $event,
            'driver_id' => $driver->driver_id,
            'driver_email' => $driver->email,
            'ocr_verification_status' => $driver->ocr_verification_status,
            'nin_ocr_match_score' => $driver->nin_ocr_match_score,
            'frsc_ocr_match_score' => $driver->frsc_ocr_match_score,
            'timestamp' => now()->toDateTimeString(),
            'ocr_data' => $ocrData,
        ];

        Log::channel('ocr')->info("OCR event: {$event}", $logData);
    }

    /**
     * Log security events
     */
    public function logSecurityEvent(string $event, string $severity = 'warning', array $context = []): void
    {
        $securityData = [
            'event' => $event,
            'severity' => $severity,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'user_id' => auth()->id(),
            'session_id' => session()->getId(),
            'timestamp' => now()->toDateTimeString(),
            'context' => $context,
        ];

        $logMethod = match($severity) {
            'critical' => 'critical',
            'high' => 'error',
            'medium' => 'warning',
            'low' => 'info',
            default => 'warning',
        };

        Log::channel('security')->{$logMethod}("Security event: {$event}", $securityData);

        // Cache security events for rate limiting
        $this->cacheSecurityEvent($event, $securityData);
    }

    /**
     * Monitor system health
     */
    public function checkSystemHealth(): array
    {
        $health = [
            'database' => $this->checkDatabaseHealth(),
            'storage' => $this->checkStorageHealth(),
            'cache' => $this->checkCacheHealth(),
            'performance' => $this->checkPerformanceMetrics(),
            'security' => $this->checkSecurityMetrics(),
        ];

        $overallStatus = $this->calculateOverallHealth($health);
        
        Log::channel('performance')->info('System health check', [
            'overall_status' => $overallStatus,
            'details' => $health,
            'timestamp' => now()->toDateTimeString(),
        ]);

        return [
            'status' => $overallStatus,
            'details' => $health,
            'checked_at' => now()->toDateTimeString(),
        ];
    }

    /**
     * Check database health
     */
    private function checkDatabaseHealth(): array
    {
        try {
            $start = microtime(true);
            DB::select('SELECT 1');
            $responseTime = (microtime(true) - $start) * 1000;

            $driverCount = Driver::count();
            $adminCount = AdminUser::count();

            return [
                'status' => 'healthy',
                'response_time_ms' => round($responseTime, 2),
                'driver_count' => $driverCount,
                'admin_count' => $adminCount,
                'connection' => DB::connection()->getDatabaseName(),
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Check storage health
     */
    private function checkStorageHealth(): array
    {
        try {
            $storagePath = storage_path();
            $freeSpace = disk_free_space($storagePath);
            $totalSpace = disk_total_space($storagePath);
            $usedPercentage = (($totalSpace - $freeSpace) / $totalSpace) * 100;

            return [
                'status' => $usedPercentage > 90 ? 'warning' : 'healthy',
                'free_space_gb' => round($freeSpace / 1024 / 1024 / 1024, 2),
                'total_space_gb' => round($totalSpace / 1024 / 1024 / 1024, 2),
                'used_percentage' => round($usedPercentage, 2),
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Check cache health
     */
    private function checkCacheHealth(): array
    {
        try {
            $testKey = 'health_check_' . time();
            $testValue = 'test_value';
            
            Cache::put($testKey, $testValue, 10);
            $retrieved = Cache::get($testKey);
            Cache::forget($testKey);

            return [
                'status' => $retrieved === $testValue ? 'healthy' : 'unhealthy',
                'driver' => config('cache.default'),
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unhealthy',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get performance metrics
     */
    private function checkPerformanceMetrics(): array
    {
        $metrics = Cache::get('performance_metrics', []);
        
        return [
            'status' => 'healthy',
            'avg_response_time_ms' => $metrics['avg_response_time'] ?? 0,
            'slow_requests_count' => $metrics['slow_requests'] ?? 0,
            'high_memory_requests' => $metrics['high_memory'] ?? 0,
            'high_query_requests' => $metrics['high_queries'] ?? 0,
        ];
    }

    /**
     * Check security metrics
     */
    private function checkSecurityMetrics(): array
    {
        $recentThreats = Cache::get('security_threats_24h', 0);
        $blockedIps = Cache::get('blocked_ips_count', 0);
        $failedLogins = Cache::get('failed_logins_1h', 0);

        $status = match (true) {
            $recentThreats > 50 => 'critical',
            $recentThreats > 20 => 'warning',
            default => 'healthy',
        };

        return [
            'status' => $status,
            'threats_24h' => $recentThreats,
            'blocked_ips' => $blockedIps,
            'failed_logins_1h' => $failedLogins,
        ];
    }

    /**
     * Calculate overall system health
     */
    private function calculateOverallHealth(array $health): string
    {
        $statuses = array_column($health, 'status');
        
        if (in_array('critical', $statuses)) {
            return 'critical';
        }
        
        if (in_array('unhealthy', $statuses)) {
            return 'unhealthy';
        }
        
        if (in_array('warning', $statuses)) {
            return 'warning';
        }
        
        return 'healthy';
    }

    /**
     * Cache security events for analysis
     */
    private function cacheSecurityEvent(string $event, array $data): void
    {
        $cacheKey = 'security_events_' . date('Y-m-d-H');
        $events = Cache::get($cacheKey, []);
        $events[] = $data;
        
        Cache::put($cacheKey, $events, now()->addHours(25));
        
        // Update threat counters
        Cache::increment('security_threats_24h', 1, now()->addHours(24));
        
        if ($data['severity'] === 'critical') {
            Cache::increment('critical_threats_1h', 1, now()->addHour());
        }
    }

    /**
     * Get system statistics for dashboard
     */
    public function getSystemStatistics(): array
    {
        return Cache::remember('system_statistics', 300, function () {
            return [
                'drivers' => [
                    'total' => Driver::count(),
                    'active' => Driver::where('status', DrivelinkConstants::DRIVER_STATUS_ACTIVE)->count(),
                    'pending_kyc' => Driver::where('kyc_status', DrivelinkConstants::KYC_STATUS_IN_PROGRESS)->count(),
                    'verified' => Driver::where('verification_status', DrivelinkConstants::VERIFICATION_STATUS_VERIFIED)->count(),
                ],
                'admins' => [
                    'total' => AdminUser::count(),
                    'active' => AdminUser::where('status', DrivelinkConstants::ADMIN_STATUS_ACTIVE)->count(),
                    'online' => AdminUser::where('last_login_at', '>=', now()->subMinutes(15))->count(),
                ],
                'system' => [
                    'uptime' => $this->getSystemUptime(),
                    'php_version' => PHP_VERSION,
                    'laravel_version' => app()->version(),
                    'environment' => config('app.env'),
                ],
                'security' => [
                    'threats_24h' => Cache::get('security_threats_24h', 0),
                    'blocked_ips' => Cache::get('blocked_ips_count', 0),
                    'failed_logins_1h' => Cache::get('failed_logins_1h', 0),
                ],
            ];
        });
    }

    /**
     * Get system uptime
     */
    private function getSystemUptime(): string
    {
        if (function_exists('shell_exec') && !in_array('shell_exec', explode(',', ini_get('disable_functions')))) {
            $uptime = shell_exec('uptime -p');
            return trim($uptime) ?: 'Unknown';
        }
        
        return 'Unknown';
    }

    /**
     * Alert administrators of critical issues
     */
    public function alertCriticalIssue(string $issue, array $details = []): void
    {
        $alertData = [
            'issue' => $issue,
            'severity' => 'critical',
            'timestamp' => now()->toDateTimeString(),
            'server' => request()->server('SERVER_NAME'),
            'details' => $details,
        ];

        Log::channel('critical')->critical('CRITICAL ISSUE DETECTED', $alertData);
        
        // Here you could add email/SMS/Slack notifications
        // NotificationService::sendCriticalAlert($alertData);
    }
}