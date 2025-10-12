<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Drivers;

echo "=== Driver Status Check ===\n\n";

$totalDrivers = Drivers::count();
$pendingDrivers = Drivers::where('verification_status', 'pending')->count();
$verifiedDrivers = Drivers::where('verification_status', 'verified')->count();
$rejectedDrivers = Drivers::where('verification_status', 'rejected')->count();

echo "Total drivers: $totalDrivers\n";
echo "Pending drivers: $pendingDrivers\n";
echo "Verified drivers: $verifiedDrivers\n";
echo "Rejected drivers: $rejectedDrivers\n\n";

echo "Latest 5 drivers:\n";
$latestDrivers = Drivers::latest()->take(5)->get(['id', 'driver_id', 'first_name', 'surname', 'verification_status', 'created_at']);

foreach ($latestDrivers as $driver) {
    echo "  ID: {$driver->id}, Name: " . ($driver->first_name ?? 'N/A') . " " . ($driver->surname ?? 'N/A') . ", Status: {$driver->verification_status}, Created: " . $driver->created_at->format('Y-m-d H:i:s') . "\n";
}

echo "\nDrivers created in last 24 hours:\n";
$recentDrivers = Drivers::where('created_at', '>=', now()->subDay())->get(['id', 'driver_id', 'first_name', 'surname', 'verification_status', 'created_at']);

foreach ($recentDrivers as $driver) {
    echo "  ID: {$driver->id}, Name: " . ($driver->first_name ?? 'N/A') . " " . ($driver->surname ?? 'N/A') . ", Status: {$driver->verification_status}, Created: " . $driver->created_at->format('Y-m-d H:i:s') . "\n";
}
