<?php

require_once 'vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Contracts\Console\Kernel;
use App\Models\AdminUser;
use Illuminate\Support\Facades\Cache;

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

echo "Testing RBAC Performance & Caching\n";
echo "==================================\n\n";

// Test permission caching performance
$user = AdminUser::where('role', 'Super Admin')->first();

if ($user) {
    echo "Testing Permission Caching Performance:\n";
    echo "========================================\n";

    // Clear any existing cache
    Cache::forget("user_permissions_{$user->id}");

    // First call - should cache
    $start = microtime(true);
    $permissions1 = $user->getAllPermissions();
    $time1 = microtime(true) - $start;

    // Second call - should use cache
    $start = microtime(true);
    $permissions2 = $user->getAllPermissions();
    $time2 = microtime(true) - $start;

    echo "First call (cache miss): " . number_format($time1 * 1000, 2) . "ms\n";
    echo "Second call (cache hit): " . number_format($time2 * 1000, 2) . "ms\n";
    echo "Cache speedup: " . number_format($time1 / $time2, 1) . "x faster\n";
    echo "Permissions cached: " . count($permissions1) . "\n\n";

    // Test cache invalidation
    echo "Testing Cache Invalidation:\n";
    echo "===========================\n";

    $cacheKey = "user_permissions_{$user->id}";
    $cachedBefore = Cache::has($cacheKey);
    echo "Cache exists before invalidation: " . ($cachedBefore ? 'YES' : 'NO') . "\n";

    $user->clearPermissionCache();

    $cachedAfter = Cache::has($cacheKey);
    echo "Cache exists after invalidation: " . ($cachedAfter ? 'YES' : 'NO') . "\n";

    if ($cachedBefore && !$cachedAfter) {
        echo "✅ Cache invalidation working correctly\n\n";
    } else {
        echo "❌ Cache invalidation not working\n\n";
    }

    // Test multiple permission checks
    echo "Testing Multiple Permission Checks:\n";
    echo "===================================\n";

    $permissions = [
        'view_dashboard',
        'manage_drivers',
        'manage_users',
        'view_reports',
        'manage_system'
    ];

    $start = microtime(true);
    $results = [];
    foreach ($permissions as $perm) {
        $results[$perm] = $user->hasPermission($perm);
    }
    $totalTime = microtime(true) - $start;

    echo "Checked " . count($permissions) . " permissions in " . number_format($totalTime * 1000, 2) . "ms\n";
    echo "Average time per check: " . number_format(($totalTime / count($permissions)) * 1000, 2) . "ms\n";

    foreach ($results as $perm => $result) {
        echo "  {$perm}: " . ($result ? '✅' : '❌') . "\n";
    }

    echo "\n";

    // Test role method performance
    echo "Testing Role Method Performance:\n";
    echo "=================================\n";

    $rolesToTest = ['Super Admin', 'Admin', 'Moderator', 'Agent'];

    $start = microtime(true);
    foreach ($rolesToTest as $role) {
        $hasRole = $user->hasRole($role);
    }
    $roleCheckTime = microtime(true) - $start;

    echo "Checked " . count($rolesToTest) . " roles in " . number_format($roleCheckTime * 1000, 2) . "ms\n";
    echo "Average time per role check: " . number_format(($roleCheckTime / count($rolesToTest)) * 1000, 2) . "ms\n";

    foreach ($rolesToTest as $role) {
        $hasRole = $user->hasRole($role);
        echo "  hasRole('{$role}'): " . ($hasRole ? '✅' : '❌') . "\n";
    }

} else {
    echo "❌ No Super Admin user found for testing\n";
}

echo "\nPerformance Test Complete\n";
