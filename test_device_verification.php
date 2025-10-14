<?php

require_once 'vendor/autoload.php';

use App\Jobs\DeviceVerificationJob;
use App\Models\Drivers;
use Illuminate\Foundation\Application;
use Illuminate\Contracts\Console\Kernel;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

echo "Testing DeviceVerificationJob...\n";

try {
    // Create a test driver if not exists
    $driver = Drivers::first();
    if (!$driver) {
        echo "No drivers found in database. Please create a test driver first.\n";
        exit(1);
    }

    echo "Using driver ID: {$driver->id}\n";

    // Test device info
    $deviceInfo = [
        'ip_address' => '192.168.1.100',
        'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
        'device_fingerprint' => 'abc123def456',
        'browser_fingerprint' => 'chrome_win10_91',
        'ip_location' => ['lat' => 6.5244, 'lng' => 3.3792], // Lagos coordinates
        'claimed_location' => ['lat' => 6.5244, 'lng' => 3.3792], // Same location
        'timezone' => 'Africa/Lagos',
        'login_time' => now()->toISOString(),
    ];

    echo "Dispatching DeviceVerificationJob...\n";

    // Dispatch the job
    DeviceVerificationJob::dispatch($driver->id, $deviceInfo);

    echo "Job dispatched successfully!\n";
    echo "Check queue:failed commands to see if job executed\n";
    echo "Check logs for verification results\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
