<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Testing Driver Authentication Routes...\n";
echo str_repeat("=", 50) . "\n";

// Test driver authentication routes
$driverRoutes = [
    'driver.login' => 'GET /driver/login',
    'driver.register' => 'GET /driver/register', 
    'driver.dashboard' => 'GET /driver/dashboard',
    'driver.logout' => 'POST /driver/logout'
];

foreach ($driverRoutes as $routeName => $description) {
    echo "Testing {$description}... ";
    try {
        $url = route($routeName);
        echo "✅ EXISTS ({$url})\n";
    } catch (Exception $e) {
        echo "❌ ERROR: " . $e->getMessage() . "\n";
    }
}

echo "\nTesting Driver Auth Controller...\n";
try {
    $controllerClass = \App\Http\Controllers\Driver\DriverAuthController::class;
    if (class_exists($controllerClass)) {
        echo "✅ DriverAuthController exists\n";
        
        $methods = ['showLogin', 'login', 'showRegister', 'register', 'logout'];
        foreach ($methods as $method) {
            if (method_exists($controllerClass, $method)) {
                echo "  ✅ Method {$method} exists\n";
            } else {
                echo "  ❌ Method {$method} missing\n";
            }
        }
    } else {
        echo "❌ DriverAuthController not found\n";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\nTesting DriverNormalized Model...\n";
try {
    $count = \App\Models\DriverNormalized::count();
    echo "✅ DriverNormalized model accessible. Records: {$count}\n";
} catch (Exception $e) {
    echo "❌ DriverNormalized error: " . $e->getMessage() . "\n";
}

echo "\nTesting Authentication Guards...\n";
try {
    $driverGuard = auth()->guard('driver');
    echo "✅ Driver guard accessible\n";
    
    if ($driverGuard->check()) {
        $user = $driverGuard->user();
        echo "  ✅ Driver currently logged in: " . ($user->email ?? 'Unknown') . "\n";
    } else {
        echo "  ℹ No driver currently logged in\n";
    }
} catch (Exception $e) {
    echo "❌ Driver guard error: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "Driver route testing completed.\n";