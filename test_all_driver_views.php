<?php

require_once __DIR__.'/vendor/autoload.php';

// Bootstrap Laravel application
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== TESTING ALL DRIVER VIEWS ===\n";

try {
    // List of all driver views that should exist
    $requiredViews = [
        'driver.dashboard',
        'driver.profile.show',
        'driver.profile.edit', 
        'driver.profile.documents',
        'driver.jobs.available',
        'driver.kyc.index',
        'driver.kyc.summary',
        'driver.kyc.step1',
        'driver.auth.login',
        'driver.auth.register'
    ];

    $missingViews = [];
    $existingViews = [];

    foreach ($requiredViews as $viewName) {
        try {
            if (view()->exists($viewName)) {
                $existingViews[] = $viewName;
                echo "✅ View exists: $viewName\n";
            } else {
                $missingViews[] = $viewName;
                echo "❌ View missing: $viewName\n";
            }
        } catch (Exception $e) {
            $missingViews[] = $viewName;
            echo "❌ Error checking view $viewName: " . $e->getMessage() . "\n";
        }
    }

    echo "\n=== SUMMARY ===\n";
    echo "✅ Existing views: " . count($existingViews) . "\n";
    echo "❌ Missing views: " . count($missingViews) . "\n";

    if (count($missingViews) > 0) {
        echo "\nMissing views:\n";
        foreach ($missingViews as $view) {
            echo "  - $view\n";
        }
    } else {
        echo "\n🎉 All required driver views exist!\n";
    }

    // Test controller methods that use views
    echo "\n=== TESTING CONTROLLER METHODS ===\n";
    
    try {
        $controller = new App\Http\Controllers\Driver\DriverProfileController();
        echo "✅ DriverProfileController instantiated successfully\n";
    } catch (Exception $e) {
        echo "❌ Error with DriverProfileController: " . $e->getMessage() . "\n";
    }

    try {
        $controller = new App\Http\Controllers\Driver\DriverAuthController();
        echo "✅ DriverAuthController instantiated successfully\n";
    } catch (Exception $e) {
        echo "❌ Error with DriverAuthController: " . $e->getMessage() . "\n";
    }

    try {
        $controller = new App\Http\Controllers\Driver\DriverDashboardController();
        echo "✅ DriverDashboardController instantiated successfully\n";
    } catch (Exception $e) {
        echo "❌ Error with DriverDashboardController: " . $e->getMessage() . "\n";
    }

} catch (Exception $e) {
    echo "❌ Test error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}

echo "\n=== DRIVER VIEWS TEST COMPLETE ===\n";