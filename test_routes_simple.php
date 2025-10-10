<?php

// Simple test to check if Laravel can load routes
try {
    require __DIR__ . '/vendor/autoload.php';
    $app = require_once __DIR__ . '/bootstrap/app.php';
    
    // Bootstrap the application
    $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
    
    echo "Laravel app bootstrapped successfully\n";
    
    // Try to get route collection
    $router = app('router');
    $routes = $router->getRoutes();
    
    echo "Route collection loaded: " . count($routes) . " routes\n";
    
    // Check for specific driver routes
    $driverRoutes = [];
    foreach ($routes as $route) {
        $name = $route->getName();
        if ($name && strpos($name, 'driver.') === 0) {
            $driverRoutes[] = $name;
        }
    }
    
    echo "Driver routes found: " . count($driverRoutes) . "\n";
    
    if (in_array('driver.settings', $driverRoutes)) {
        echo "✓ driver.settings route found!\n";
    } else {
        echo "✗ driver.settings route NOT found\n";
    }
    
    if (in_array('driver.profile.show', $driverRoutes)) {
        echo "✓ driver.profile.show route found!\n";
    } else {
        echo "✗ driver.profile.show route NOT found\n";
    }
    
    echo "\nFirst 10 driver routes:\n";
    foreach (array_slice($driverRoutes, 0, 10) as $route) {
        echo "- $route\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}