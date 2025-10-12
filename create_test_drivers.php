<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Creating test drivers with different verification statuses...\n";

// Create test drivers
$drivers = [
    [
        'driver_id' => 'TEST001',
        'first_name' => 'John',
        'surname' => 'Doe',
        'email' => 'john@example.com',
        'phone' => '08012345678',
        'verification_status' => 'verified',
        'kyc_status' => 'completed',
        'password' => bcrypt('password'),
    ],
    [
        'driver_id' => 'TEST002',
        'first_name' => 'Jane',
        'surname' => 'Smith',
        'email' => 'jane@example.com',
        'phone' => '08012345679',
        'verification_status' => 'pending',
        'kyc_status' => 'in_progress',
        'password' => bcrypt('password'),
    ],
    [
        'driver_id' => 'TEST003',
        'first_name' => 'Bob',
        'surname' => 'Johnson',
        'email' => 'bob@example.com',
        'phone' => '08012345680',
        'verification_status' => 'rejected',
        'kyc_status' => 'rejected',
        'password' => bcrypt('password'),
    ],
];

foreach ($drivers as $driverData) {
    $driver = \App\Models\Drivers::create($driverData);
    echo "Created driver: {$driver->driver_id} with status {$driver->verification_status}\n";
}

echo "Test drivers created successfully!\n";
