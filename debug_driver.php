<?php

require_once 'vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Contracts\Console\Kernel;

$app = require_once 'bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$driver = \App\Models\Drivers::find(2);
echo "Driver ID: {$driver->id}\n";
echo "Status: {$driver->status}\n";
echo "Is Active: {$driver->is_active}\n";
echo "Verification Status: {$driver->verification_status}\n";

// Update driver to be available
$driver->update([
    'status' => 'active',
    'is_active' => true,
    'verification_status' => 'verified'
]);

echo "Updated driver to be available\n";
