<?php

require_once __DIR__ . '/vendor/autoload.php';

// Load Laravel application
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing Routes...\n";

try {
    // Test if the route exists
    $router = app('router');
    
    // Get all routes
    $routes = $router->getRoutes();
    
    echo "Total routes: " . $routes->count() . "\n";
    
    // Look for admin.drivers routes
    $adminDriverRoutes = [];
    foreach ($routes as $route) {
        $name = $route->getName();
        if ($name && strpos($name, 'admin.drivers') === 0) {
            $adminDriverRoutes[] = $name;
        }
    }
    
    echo "Admin Driver routes found:\n";
    foreach ($adminDriverRoutes as $routeName) {
        echo "- {$routeName}\n";
    }
    
    // Check specifically for admin.drivers.index
    if (in_array('admin.drivers.index', $adminDriverRoutes)) {
        echo "\n✅ admin.drivers.index route is DEFINED\n";
    } else {
        echo "\n❌ admin.drivers.index route is NOT DEFINED\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}