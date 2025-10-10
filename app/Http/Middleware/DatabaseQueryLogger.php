<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DatabaseQueryLogger
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        if (config('app.debug') && config('app.env') === 'local') {
            $startTime = microtime(true);
            $queryCount = 0;
            
            DB::listen(function ($query) use (&$queryCount) {
                $queryCount++;
                
                // Log slow queries (>100ms)
                if ($query->time > 100) {
                    Log::warning('Slow Database Query', [
                        'sql' => $query->sql,
                        'bindings' => $query->bindings,
                        'time' => $query->time . 'ms',
                        'url' => request()->fullUrl(),
                        'method' => request()->method()
                    ]);
                }
            });
        }

        $response = $next($request);

        if (config('app.debug') && config('app.env') === 'local') {
            $endTime = microtime(true);
            $totalTime = ($endTime - $startTime) * 1000; // Convert to milliseconds
            
            // Add performance headers
            $response->headers->set('X-Query-Count', $queryCount);
            $response->headers->set('X-Response-Time', round($totalTime, 2) . 'ms');
            
            // Log requests with high query counts
            if ($queryCount > 20) {
                Log::warning('High Query Count Request', [
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                    'query_count' => $queryCount,
                    'response_time' => round($totalTime, 2) . 'ms'
                ]);
            }
        }

        return $response;
    }
}