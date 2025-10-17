<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\DriverQueryOptimizationService;
use App\Models\SuperadminActivityLog;

echo "=== Testing Superadmin Dashboard Stats ===\n";

try {
    $service = app(DriverQueryOptimizationService::class);
    $stats = $service->getDashboardStats();

    echo "Dashboard Stats:\n";
    print_r($stats);
} catch (Exception $e) {
    echo "Error getting dashboard stats: " . $e->getMessage() . "\n";
}

echo "\n=== Testing Recent Activities ===\n";

try {
    $activities = SuperadminActivityLog::with('superadmin')
        ->orderBy('created_at', 'desc')
        ->limit(5)
        ->get();

    echo "Recent Activities Count: " . $activities->count() . "\n";

    foreach ($activities as $activity) {
        echo "- " . $activity->formatted_action . ": " . $activity->description . " (" . $activity->created_at->diffForHumans() . ")\n";
    }
} catch (Exception $e) {
    echo "Error getting activities: " . $e->getMessage() . "\n";
}

echo "\n=== Testing Driver Count ===\n";

try {
    $driverCount = \App\Models\Drivers::count();
    echo "Total Drivers: $driverCount\n";

    $driversWithIds = \App\Models\Drivers::whereNotNull('driver_id')->count();
    echo "Drivers with IDs: $driversWithIds\n";
} catch (Exception $e) {
    echo "Error getting driver count: " . $e->getMessage() . "\n";
}

echo "\n=== Testing System Health ===\n";

try {
    $controller = app(\App\Http\Controllers\Admin\SuperAdminController::class);
    $health = $controller->systemHealth();

    echo "System Health Response:\n";
    print_r($health->getData());
} catch (Exception $e) {
    echo "Error testing system health: " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n";
