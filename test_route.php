<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    // Test if the route exists
    $url = route('admin.drivers.files.list', ['driver' => 1]);
    echo "Route 'admin.drivers.files.list' exists and generates URL: " . $url . PHP_EOL;
    echo "SUCCESS: Route is properly defined!" . PHP_EOL;
} catch (Exception $e) {
    echo "ERROR: Route 'admin.drivers.files.list' not found: " . $e->getMessage() . PHP_EOL;
}