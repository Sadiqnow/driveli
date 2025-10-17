<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Drivers;

echo "=== Testing Driver Model ===\n";

try {
    $driver = Drivers::first();

    if ($driver) {
        echo "Driver ID: " . $driver->id . ", Driver_ID field: " . ($driver->driver_id ?? 'NULL') . PHP_EOL;
        echo "Full Name: " . $driver->full_name . PHP_EOL;
        echo "Status: " . $driver->status . PHP_EOL;
        echo "Verification: " . $driver->verification_status . PHP_EOL;
        echo "Email: " . $driver->email . PHP_EOL;
        echo "Phone: " . $driver->phone . PHP_EOL;
        echo "Created At: " . $driver->created_at . PHP_EOL;
    } else {
        echo "No drivers found in database\n";
    }

    echo "\n=== Driver Count ===\n";
    echo "Total Drivers: " . Drivers::count() . PHP_EOL;

    echo "\n=== Driver Stats ===\n";
    echo "Active: " . Drivers::where('status', 'active')->count() . PHP_EOL;
    echo "Verified: " . Drivers::where('verification_status', 'verified')->count() . PHP_EOL;
    echo "With Driver IDs: " . Drivers::whereNotNull('driver_id')->count() . PHP_EOL;

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== Test Complete ===\n";
