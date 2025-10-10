<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response as BaseResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PerformanceMonitoringMiddleware
{
    /**
     * Handle an incoming request and monitor performance
     */
    public function handle(Request $request, Closure $next): BaseResponse
    {
        $startTime = microtime(true);
        $startMemory = memory_get_usage(true);
        
        // Count database queries before request
        $queryCountBefore = count(DB::getQueryLog());
        DB::enableQueryLog();

        $response = $next($request);

        $endTime = microtime(true);
        $endMemory = memory_get_usage(true);
        
        // Calculate metrics
        $executionTime = ($endTime - $startTime) * 1000; // Convert to milliseconds
        $memoryUsage = ($endMemory - $startMemory) / 1024 / 1024; // Convert to MB
        $queryCount = count(DB::getQueryLog()) - $queryCountBefore;
        
        $this->logPerformanceMetrics($request, $response, [
            'execution_time_ms' => round($executionTime, 2),
            'memory_usage_mb' => round($memoryUsage, 4),
            'query_count' => $queryCount,
            'peak_memory_mb' => round(memory_get_peak_usage(true) / 1024 / 1024, 4),
            'status_code' => $response->getStatusCode(),
        ]);

        // Add performance headers to response (if supported)
        try {
            if (method_exists($response, 'headers') && $response->headers) {
                $response->headers->set('X-Response-Time', $executionTime . 'ms');
                $response->headers->set('X-Memory-Usage', $memoryUsage . 'MB');
                $response->headers->set('X-Query-Count', $queryCount);
            }
        } catch (\Exception $e) {
            // Silently ignore header setting errors for unsupported response types
            Log::debug('Could not set performance headers', ['error' => $e->getMessage()]);
        }

        return $response;
    }

    /**
     * Log performance metrics
     */
    private function logPerformanceMetrics(Request $request, BaseResponse $response, array $metrics): void
    {
        $logData = [
            'method' => $request->method(),
            'uri' => $request->path(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'user_id' => auth()->id(),
            'route' => $request->route()?->getName(),
        ];

        $logData = array_merge($logData, $metrics);

        // Log slow requests
        if ($metrics['execution_time_ms'] > 1000) { // Slower than 1 second
            Log::channel('performance')->warning('Slow request detected', $logData);
        }

        // Log high memory usage
        if ($metrics['memory_usage_mb'] > 50) { // More than 50MB
            Log::channel('performance')->warning('High memory usage detected', $logData);
        }

        // Log too many queries (potential N+1 problem)
        if ($metrics['query_count'] > 20) {
            Log::channel('performance')->warning('High query count detected', array_merge($logData, [
                'queries' => $this->getSlowQueries(),
            ]));
        }

        // Log all requests for analysis
        Log::channel('performance')->info('Request completed', $logData);
    }

    /**
     * Get slow database queries for logging
     */
    private function getSlowQueries(): array
    {
        $queries = DB::getQueryLog();
        $slowQueries = [];

        foreach ($queries as $query) {
            if ($query['time'] > 100) { // Slower than 100ms
                $slowQueries[] = [
                    'sql' => $query['sql'],
                    'bindings' => $query['bindings'],
                    'time_ms' => $query['time'],
                ];
            }
        }

        return $slowQueries;
    }
}