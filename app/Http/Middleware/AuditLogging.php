<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\ActivityLog;

class AuditLogging
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $startTime = microtime(true);

        $response = $next($request);

        $endTime = microtime(true);
        $duration = round(($endTime - $startTime) * 1000, 2); // Duration in milliseconds

        // Log audit trail for sensitive operations
        if ($this->shouldAuditRequest($request)) {
            $this->logAuditTrail($request, $response, $duration);
        }

        return $response;
    }

    /**
     * Determine if request should be audited
     */
    protected function shouldAuditRequest(Request $request): bool
    {
        $auditRoutes = [
            'admin.deactivation.*',
            'admin.monitoring.*',
            'api.driver.location.*',
            'api.admin.deactivation.*',
            'otp.*',
        ];

        $currentRoute = $request->route() ? $request->route()->getName() : null;

        if (!$currentRoute) {
            return false;
        }

        foreach ($auditRoutes as $pattern) {
            if (fnmatch($pattern, $currentRoute)) {
                return true;
            }
        }

        // Also audit POST/PUT/DELETE requests to sensitive endpoints
        if (in_array($request->method(), ['POST', 'PUT', 'DELETE'])) {
            $sensitivePaths = [
                'deactivation',
                'otp',
                'monitoring',
                'location',
            ];

            foreach ($sensitivePaths as $path) {
                if (str_contains($request->path(), $path)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Log audit trail
     */
    protected function logAuditTrail(Request $request, $response, float $duration): void
    {
        try {
            $user = Auth::user();
            $route = $request->route();
            $routeName = $route ? $route->getName() : 'unknown';

            // Extract driver ID from various sources
            $driverId = $this->extractDriverId($request);

            $metadata = [
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'route' => $routeName,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'duration_ms' => $duration,
                'response_status' => $response->getStatusCode(),
                'request_size' => strlen($request->getContent()),
                'response_size' => strlen($response->getContent()),
                'headers' => [
                    'accept' => $request->header('Accept'),
                    'content_type' => $request->header('Content-Type'),
                    'referer' => $request->header('Referer'),
                ],
            ];

            // Add request parameters for sensitive operations (excluding passwords)
            if ($this->shouldLogParameters($request)) {
                $metadata['parameters'] = $this->sanitizeParameters($request->all());
            }

            // Determine activity type based on route
            $activityType = $this->determineActivityType($routeName, $request);

            ActivityLog::create([
                'user_id' => $driverId ?: ($user ? $user->id : null),
                'user_type' => $driverId ? 'driver' : ($user ? 'admin' : 'system'),
                'activity_type' => $activityType,
                'description' => $this->generateActivityDescription($activityType, $routeName),
                'metadata' => $metadata,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'performed_at' => now(),
            ]);

        } catch (\Exception $e) {
            // Log the error but don't break the response
            Log::error('Failed to create audit log', [
                'error' => $e->getMessage(),
                'route' => $routeName ?? 'unknown',
                'ip' => $request->ip(),
            ]);
        }
    }

    /**
     * Extract driver ID from request
     */
    protected function extractDriverId(Request $request)
    {
        // Check route parameters
        if ($request->route()) {
            $driverId = $request->route('driverId') ?: $request->route('id');
            if ($driverId) {
                return $driverId;
            }
        }

        // Check request input
        return $request->input('driver_id') ?: $request->input('user_id');
    }

    /**
     * Determine if parameters should be logged
     */
    protected function shouldLogParameters(Request $request): bool
    {
        // Don't log parameters for file uploads or very large requests
        if ($request->hasFile('*') || strlen($request->getContent()) > 10000) {
            return false;
        }

        // Log parameters for sensitive operations
        $sensitiveMethods = ['POST', 'PUT', 'DELETE'];
        return in_array($request->method(), $sensitiveMethods);
    }

    /**
     * Sanitize parameters for logging (remove sensitive data)
     */
    protected function sanitizeParameters(array $parameters): array
    {
        $sensitiveKeys = ['password', 'password_confirmation', 'otp', 'token', 'secret'];

        foreach ($sensitiveKeys as $key) {
            if (isset($parameters[$key])) {
                $parameters[$key] = '[REDACTED]';
            }
        }

        return $parameters;
    }

    /**
     * Determine activity type based on route and request
     */
    protected function determineActivityType(string $routeName, Request $request): string
    {
        if (str_contains($routeName, 'deactivation')) {
            if (str_contains($routeName, 'approve') || str_contains($routeName, 'verify-otp')) {
                return 'deactivation_approved';
            } elseif (str_contains($routeName, 'reject')) {
                return 'deactivation_rejected';
            } elseif (str_contains($routeName, 'create')) {
                return 'deactivation_requested';
            }
            return 'deactivation_accessed';
        }

        if (str_contains($routeName, 'otp')) {
            return 'otp_requested';
        }

        if (str_contains($routeName, 'monitoring')) {
            return 'monitoring_accessed';
        }

        if (str_contains($routeName, 'location')) {
            return 'location_updated';
        }

        return 'api_accessed';
    }

    /**
     * Generate activity description
     */
    protected function generateActivityDescription(string $activityType, string $routeName): string
    {
        $descriptions = [
            'deactivation_approved' => 'Deactivation request approved',
            'deactivation_rejected' => 'Deactivation request rejected',
            'deactivation_requested' => 'Deactivation request created',
            'deactivation_accessed' => 'Deactivation system accessed',
            'otp_requested' => 'OTP verification requested',
            'monitoring_accessed' => 'Driver monitoring accessed',
            'location_updated' => 'Driver location updated',
            'api_accessed' => 'API endpoint accessed',
        ];

        return $descriptions[$activityType] ?? 'System activity logged';
    }
}
