<?php
// Test API endpoints for states and LGAs

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\State;
use App\Models\LocalGovernment;

echo "Testing API data...\n\n";

// Test states
$states = State::orderBy('name')->take(5)->get(['id', 'name', 'code']);
echo "States (first 5):\n";
foreach ($states as $state) {
    echo "- ID: {$state->id}, Name: {$state->name}, Code: {$state->code}\n";
}

// Test Lagos LGAs (state ID 25)
$lagosState = State::where('name', 'Lagos')->first();
if ($lagosState) {
    $lgas = LocalGovernment::where('state_id', $lagosState->id)->take(10)->get(['id', 'name']);
    echo "\nLagos LGAs (first 10):\n";
    foreach ($lgas as $lga) {
        echo "- ID: {$lga->id}, Name: {$lga->name}\n";
    }
} else {
    echo "\nLagos state not found\n";
}

echo "\nAPI test complete.\n";