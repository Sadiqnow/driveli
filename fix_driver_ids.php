<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Drivers;

$drivers = Drivers::whereNull('driver_id')->get();

foreach ($drivers as $driver) {
    $driver->update([
        'driver_id' => 'DRV-' . strtoupper(uniqid())
    ]);
    echo "Updated driver ID for: {$driver->first_name} {$driver->surname}\n";
}

echo "Fixed " . $drivers->count() . " drivers with missing driver_id\n";
