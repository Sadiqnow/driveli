<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== VERIFYING SEEDING DATA ===\n\n";

echo "STATES:\n";
$states = \App\Models\State::take(5)->get();
foreach ($states as $state) {
    echo "- {$state->name} ({$state->code})\n";
}

echo "\nLGAs (Abia State):\n";
$lgas = \App\Models\LocalGovernment::where('state_id', 1)->take(5)->get();
foreach ($lgas as $lga) {
    echo "- {$lga->name}\n";
}

echo "\nNATIONALITIES:\n";
$nationalities = \App\Models\Nationality::all();
foreach ($nationalities as $nat) {
    echo "- {$nat->name} ({$nat->code}) - " . ($nat->is_active ? 'Active' : 'Inactive') . "\n";
}

echo "\nBANKS:\n";
$banks = \App\Models\Bank::take(5)->get();
foreach ($banks as $bank) {
    echo "- {$bank->name} ({$bank->code}) - " . ($bank->is_active ? 'Active' : 'Inactive') . "\n";
}

echo "\n=== COUNTS ===\n";
echo "States: " . \App\Models\State::count() . "\n";
echo "LGAs: " . \App\Models\LocalGovernment::count() . "\n";
echo "Nationalities: " . \App\Models\Nationality::count() . "\n";
echo "Banks: " . \App\Models\Bank::count() . "\n";

echo "\n=== VERIFICATION COMPLETE ===\n";
