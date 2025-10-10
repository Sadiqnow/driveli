<?php

require_once __DIR__.'/vendor/autoload.php';

use Illuminate\Http\Request;

// Bootstrap Laravel application
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

echo "=== TESTING DRIVER VIEWS ===\n";

try {
    // Test driver dashboard access
    echo "1. Testing dashboard access...\n";
    $request = Request::create('/driver/dashboard', 'GET');
    
    // Mock authentication
    $user = new App\Models\DriverNormalized();
    $user->id = 1;
    $user->driver_id = 'TEST-001';
    $user->first_name = 'Test';
    $user->surname = 'Driver';
    $user->email = 'test@example.com';
    $user->verification_status = 'pending';
    $user->status = 'inactive';
    $user->registered_at = now();
    
    // Mock authentication for the request
    $request->setUserResolver(function () use ($user) {
        return $user;
    });
    
    // Test if views exist
    $views = [
        'driver.dashboard',
        'driver.profile.documents', 
        'driver.profile.edit',
        'driver.jobs.available',
        'driver.kyc.summary'
    ];
    
    foreach ($views as $viewName) {
        try {
            if (view()->exists($viewName)) {
                echo "✅ View exists: $viewName\n";
            } else {
                echo "❌ View missing: $viewName\n";
            }
        } catch (Exception $e) {
            echo "❌ Error checking view $viewName: " . $e->getMessage() . "\n";
        }
    }
    
    // Test specific route access
    echo "\n2. Testing route resolution...\n";
    
    $routes = [
        '/driver/dashboard',
        '/driver/profile/documents',
        '/driver/profile/edit', 
        '/driver/jobs/available'
    ];
    
    foreach ($routes as $route) {
        try {
            $testRequest = Request::create($route, 'GET');
            // Just check if route exists, don't execute
            $routeExists = app('router')->getRoutes()->match($testRequest);
            if ($routeExists) {
                echo "✅ Route exists: $route\n";
            }
        } catch (Exception $e) {
            echo "❌ Route missing or error: $route - " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n✅ Driver views test completed!\n";
    
} catch (Exception $e) {
    echo "❌ Test error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}

echo "\n=== DRIVER VIEWS TEST COMPLETE ===\n";