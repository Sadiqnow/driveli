<?php

require_once 'vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Contracts\Console\Kernel;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

echo "Testing ScheduleReverification console command...\n";

try {
    echo "Running: php artisan verification:schedule-reverification\n";

    // Run the command
    $exitCode = $kernel->call('verification:schedule-reverification');

    echo "Command executed with exit code: {$exitCode}\n";

    // Get command output
    $output = $kernel->output();
    echo "Command output:\n{$output}\n";

    echo "Console command test completed!\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
