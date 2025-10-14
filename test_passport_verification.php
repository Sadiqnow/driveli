<?php

require_once 'vendor/autoload.php';

use App\Jobs\PassportVerificationJob;
use App\Models\Drivers;
use Illuminate\Foundation\Application;
use Illuminate\Contracts\Console\Kernel;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

echo "Testing PassportVerificationJob...\n";

try {
    // Create a test driver if not exists
    $driver = Drivers::first();
    if (!$driver) {
        echo "No drivers found in database. Please create a test driver first.\n";
        exit(1);
    }

    echo "Using driver ID: {$driver->id}\n";

    // Test passport number
    $passportNumber = 'A12345678'; // Test passport number

    echo "Dispatching PassportVerificationJob...\n";

    // Dispatch the job
    PassportVerificationJob::dispatch($driver->id, $passportNumber);

    echo "Job dispatched successfully!\n";
    echo "Check queue:failed commands to see if job executed\n";
    echo "Check logs for verification results\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
