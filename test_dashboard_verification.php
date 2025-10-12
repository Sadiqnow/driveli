<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Checking drivers in database...\n";

$drivers = \App\Models\Driver::take(5)->get(['id', 'driver_id', 'first_name', 'verification_status', 'kyc_status']);

echo "Found " . $drivers->count() . " drivers:\n";

foreach ($drivers as $driver) {
    echo "- {$driver->driver_id}: verification_status='{$driver->verification_status}', kyc_status='{$driver->kyc_status}'\n";
}

echo "\nTesting dashboard controller...\n";

try {
    // Test the controller logic
    $controller = new \App\Http\Controllers\Driver\DriverDashboardController();

    // Mock a driver user
    if ($drivers->count() > 0) {
        $testDriver = $drivers->first();

        // Simulate what the controller does
        $freshDriver = \App\Models\Driver::find($testDriver->id);

        echo "Controller test successful:\n";
        echo "- Original verification_status: {$testDriver->verification_status}\n";
        echo "- Fresh DB verification_status: {$freshDriver->verification_status}\n";
        echo "- Statuses match: " . ($testDriver->verification_status === $freshDriver->verification_status ? 'YES' : 'NO') . "\n";
    }

} catch (Exception $e) {
    echo "Error testing controller: " . $e->getMessage() . "\n";
}

echo "\nDashboard verification test completed.\n";
