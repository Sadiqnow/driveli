<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Drivers;

echo "Checking drivers table...\n";

$driver = Drivers::first();
if ($driver) {
    echo "Driver found: ID {$driver->id}\n";
    echo "Status: {$driver->verification_status}\n";
    echo "Score: {$driver->overall_verification_score}\n";
    echo "Has verification_completed_at: " . ($driver->verification_completed_at ? 'Yes' : 'No') . "\n";
} else {
    echo "No drivers found\n";
}

echo "\nChecking driver_verification_logs table...\n";

$logs = DB::table('driver_verification_logs')->count();
echo "Total logs: {$logs}\n";

$latestLog = DB::table('driver_verification_logs')->latest()->first();
if ($latestLog) {
    echo "Latest log ID: {$latestLog->id}\n";
    echo "Action: {$latestLog->action}\n";
    echo "Status: {$latestLog->status}\n";
    echo "Confidence score: {$latestLog->confidence_score}\n";
}
