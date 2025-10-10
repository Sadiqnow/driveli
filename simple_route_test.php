<?php

echo "Testing Route Registration...\n";

try {
    require_once 'vendor/autoload.php';
    $app = require_once 'bootstrap/app.php';
    $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
    
    echo "✅ Laravel bootstrapped successfully\n";
    
    // Test route generation
    $routes = [
        'admin.drivers.index' => 'admin/drivers',
        'admin.drivers.create' => 'admin/drivers/create',
        'admin.drivers.verification' => 'admin/drivers/verification',
    ];
    
    foreach ($routes as $routeName => $expectedPath) {
        try {
            $url = route($routeName);
            echo "✅ Route '{$routeName}' exists: {$url}\n";
        } catch (Exception $e) {
            echo "❌ Route '{$routeName}' failed: " . $e->getMessage() . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Bootstrap failed: " . $e->getMessage() . "\n";
}

?>