<?php

require_once 'vendor/autoload.php';

use App\Jobs\RunDriverVerificationJob;
use App\Models\Drivers;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Testing RunDriverVerificationJob dispatch...\n";

// Find a test driver
$driver = Drivers::where('verification_status', '!=', 'verified')->first();

if (!$driver) {
    echo "No unverified driver found for testing. Creating a test dispatch with null driver.\n";
    // For testing purposes, create job with null driver (will fail gracefully)
    $job = new RunDriverVerificationJob(null, false);
    echo "Job created successfully.\n";
    echo "Payload structure: " . json_encode($job, JSON_PRETTY_PRINT) . "\n";
} else {
    echo "Found driver ID: {$driver->id}\n";

    // Dispatch the job
    $pendingDispatch = RunDriverVerificationJob::dispatch($driver, false)->onQueue('verification');

    echo "Job dispatched successfully.\n";
    echo "Queue: verification\n";
    echo "Driver ID: {$driver->id}\n";
    echo "Is Reverify: false\n";
    echo "Job will be processed by queue worker.\n";
}

echo "Test completed.\n";
