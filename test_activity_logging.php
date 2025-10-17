<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Drivers;
use App\Services\SuperadminActivityLogger;

echo "Testing activity logging...\n";

$driver = Drivers::first();
if($driver) {
    echo "Found driver: {$driver->first_name} {$driver->surname}\n";
    $activity = SuperadminActivityLogger::logDriverCreation($driver);
    echo "Activity logged successfully with ID: {$activity->id}\n";
} else {
    echo "No driver found\n";
}

echo "Total activities: " . \App\Models\SuperadminActivityLog::count() . "\n";
