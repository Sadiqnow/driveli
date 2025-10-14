<?php

require_once 'vendor/autoload.php';

use App\Jobs\ReverificationSchedulerJob;
use App\Models\Verification;
use Illuminate\Foundation\Application;
use Illuminate\Contracts\Console\Kernel;
use Carbon\Carbon;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

echo "Testing ReverificationSchedulerJob...\n";

try {
    // Create some test expired verifications
    echo "Creating test expired verifications...\n";

    // Find existing driver
    $driver = \App\Models\Drivers::first();
    if (!$driver) {
        echo "No drivers found. Please create test data first.\n";
        exit(1);
    }

    // Create expired verification
    $expiredVerification = Verification::create([
        'verifiable_id' => $driver->id,
        'verifiable_type' => 'App\Models\Drivers',
        'type' => 'nin_verification',
        'verification_source' => 'nimc_api',
        'status' => 'approved',
        'api_response' => json_encode(['test' => 'data']),
        'response_timestamp' => now(),
        'response_time_ms' => 1500,
        'expires_at' => Carbon::now()->subDays(5), // Expired 5 days ago
        'created_at' => Carbon::now()->subDays(10),
        'updated_at' => Carbon::now()->subDays(10),
    ]);

    // Create failed verification
    $failedVerification = Verification::create([
        'verifiable_id' => $driver->id,
        'verifiable_type' => 'App\Models\Drivers',
        'type' => 'license_verification',
        'verification_source' => 'frsc_api',
        'status' => 'rejected',
        'api_response' => json_encode(['error' => 'test failure']),
        'response_timestamp' => now(),
        'response_time_ms' => 2000,
        'created_at' => Carbon::now()->subDays(20),
        'updated_at' => Carbon::now()->subDays(20),
    ]);

    echo "Created test verifications:\n";
    echo "- Expired verification ID: {$expiredVerification->id}\n";
    echo "- Failed verification ID: {$failedVerification->id}\n";

    echo "Dispatching ReverificationSchedulerJob...\n";

    // Dispatch the job
    ReverificationSchedulerJob::dispatch();

    echo "Job dispatched successfully!\n";
    echo "Check queue:failed commands to see if job executed\n";
    echo "Check logs for scheduler results\n";
    echo "Check verifications table for updated requires_reverification flags\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
