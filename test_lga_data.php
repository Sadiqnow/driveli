<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    // Test database connection
    $states = \App\Models\State::count();
    $lgas = \App\Models\LocalGovernment::count();
    
    echo "Database Connection: OK\n";
    echo "States in database: {$states}\n";
    echo "LGAs in database: {$lgas}\n\n";
    
    if ($states == 0) {
        echo "No states found. Running seeder...\n";
        Artisan::call('db:seed', ['--class' => 'NigerianStatesLGASeeder']);
        echo Artisan::output();
        
        $states = \App\Models\State::count();
        $lgas = \App\Models\LocalGovernment::count();
        echo "After seeding - States: {$states}, LGAs: {$lgas}\n\n";
    }
    
    // Test a specific state (Lagos)
    $lagosState = \App\Models\State::where('name', 'Lagos')->first();
    if ($lagosState) {
        $lagosLgas = \App\Models\LocalGovernment::where('state_id', $lagosState->id)->count();
        echo "Lagos State ID: {$lagosState->id}\n";
        echo "Lagos LGAs: {$lagosLgas}\n";
        
        if ($lagosLgas > 0) {
            echo "Sample Lagos LGAs:\n";
            $sampleLgas = \App\Models\LocalGovernment::where('state_id', $lagosState->id)->limit(5)->get();
            foreach ($sampleLgas as $lga) {
                echo "- {$lga->name} (ID: {$lga->id})\n";
            }
        }
    } else {
        echo "Lagos state not found!\n";
    }
    
    // Test the API route
    echo "\nTesting API route...\n";
    if ($lagosState) {
        $response = file_get_contents("http://localhost:8000/api/lgas/{$lagosState->id}");
        if ($response !== false) {
            $data = json_decode($response, true);
            echo "API Response: " . (is_array($data) ? count($data) . " LGAs returned" : "Invalid response") . "\n";
        } else {
            echo "API call failed - server might not be running\n";
        }
    }
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}