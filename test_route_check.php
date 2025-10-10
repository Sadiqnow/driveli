<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Test if admin.drivers.index route exists
try {
    $url = route('admin.drivers.index');
    echo "✅ Route 'admin.drivers.index' exists: " . $url . PHP_EOL;
} catch (Exception $e) {
    echo "❌ Route 'admin.drivers.index' does not exist: " . $e->getMessage() . PHP_EOL;
}

// Test if AdminDriverController exists
try {
    $controller = new App\Http\Controllers\Admin\DriverController();
    echo "✅ AdminDriverController exists and can be instantiated" . PHP_EOL;
} catch (Exception $e) {
    echo "❌ AdminDriverController error: " . $e->getMessage() . PHP_EOL;
}

// List all admin.drivers routes
echo "\n=== Admin Driver Routes ===\n";
$routes = Route::getRoutes();
foreach ($routes as $route) {
    $name = $route->getName();
    if ($name && str_contains($name, 'admin.drivers')) {
        echo $name . " -> " . $route->uri() . " [" . implode(',', $route->methods()) . "]" . PHP_EOL;
    }
}