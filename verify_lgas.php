<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\State;
use App\Models\LocalGovernment;

echo "=== Nigerian States and LGAs Verification ===" . PHP_EOL;
echo "Total States: " . State::count() . PHP_EOL;
echo "Total LGAs: " . LocalGovernment::count() . PHP_EOL;
echo PHP_EOL;

echo "States with their LGA counts:" . PHP_EOL;
echo "=============================" . PHP_EOL;

$states = State::withCount('localGovernments')->orderBy('name')->get();

$totalLgas = 0;
foreach ($states as $state) {
    echo sprintf("%-25s: %d LGAs", $state->name, $state->local_governments_count) . PHP_EOL;
    $totalLgas += $state->local_governments_count;
}

echo PHP_EOL;
echo "Total LGAs calculated: " . $totalLgas . PHP_EOL;

// Sample of some LGAs
echo PHP_EOL;
echo "Sample LGAs from Lagos State:" . PHP_EOL;
echo "=============================" . PHP_EOL;
$lagosLgas = LocalGovernment::whereHas('state', function($query) {
    $query->where('name', 'Lagos');
})->limit(10)->get();

foreach ($lagosLgas as $lga) {
    echo "- " . $lga->name . PHP_EOL;
}

echo PHP_EOL;
echo "Database seeding completed successfully!" . PHP_EOL;