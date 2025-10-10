<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Bank;
use App\Models\State;
use App\Models\LocalGovernment; // Assuming this is the model for LGAs

echo "All Banks (alphabetically):\n";
echo str_repeat("-", 50) . "\n";
$banks = Bank::orderBy('name')->get();
if ($banks->isEmpty()) {
    echo "No banks found. Please run: php artisan db:seed --class=BanksSeeder\n";
} else {
    foreach ($banks as $bank) {
        echo "- " . $bank->name . "\n";
    }
}
echo "\n\n";

echo "All LGAs grouped by State (alphabetically):\n";
echo str_repeat("-", 50) . "\n";
$states = State::with('localGovernments')->orderBy('name')->get();
if ($states->isEmpty()) {
    echo "No states found. Please run: php artisan db:seed --class=NigerianStatesAndLGASeeder\n";
} else {
    foreach ($states as $state) {
        echo ucfirst($state->name) . ":\n";
        $lgas = $state->localGovernments->sortBy('name');
        if ($lgas->isEmpty()) {
            echo "  No LGAs for this state.\n";
        } else {
            foreach ($lgas as $lga) {
                echo "  - " . $lga->name . "\n";
            }
        }
        echo "\n";
    }
}
