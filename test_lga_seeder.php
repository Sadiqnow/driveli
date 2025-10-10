<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing LGA Seeder\n";
echo "==================\n\n";

try {
    // Test database connection
    echo "1. Testing database connection...\n";
    $pdo = \DB::connection()->getPdo();
    echo "✓ Database connected successfully\n\n";

    // Check current state
    echo "2. Current database state:\n";
    $states = \App\Models\State::count();
    $lgas = \App\Models\LocalGovernment::count();
    echo "   States: {$states}\n";
    echo "   LGAs: {$lgas}\n\n";

    // Check if tables exist
    echo "3. Checking table structure...\n";
    $statesTable = \Schema::hasTable('states');
    $lgasTable = \Schema::hasTable('local_governments');
    echo "   States table exists: " . ($statesTable ? "Yes" : "No") . "\n";
    echo "   Local governments table exists: " . ($lgasTable ? "Yes" : "No") . "\n\n";

    if ($statesTable && $lgasTable) {
        echo "4. Testing seeder...\n";
        
        // Run the seeder
        \Artisan::call('db:seed', [
            '--class' => 'NigerianStatesLGASeeder',
            '--verbose' => true
        ]);
        
        $output = \Artisan::output();
        echo "Seeder output:\n" . $output . "\n";

        // Check results
        $newStates = \App\Models\State::count();
        $newLgas = \App\Models\LocalGovernment::count();
        echo "After seeding:\n";
        echo "   States: {$newStates}\n";
        echo "   LGAs: {$newLgas}\n\n";

        if ($newStates > 0 && $newLgas > 0) {
            echo "✓ Seeding appears successful!\n\n";
            
            // Test a specific state
            $lagos = \App\Models\State::where('name', 'Lagos')->first();
            if ($lagos) {
                $lagosLgas = \App\Models\LocalGovernment::where('state_id', $lagos->id)->get();
                echo "Lagos State (ID: {$lagos->id}) has " . $lagosLgas->count() . " LGAs:\n";
                foreach ($lagosLgas->take(5) as $lga) {
                    echo "   - {$lga->name}\n";
                }
            }
        } else {
            echo "✗ Seeding failed - no data inserted\n";
        }
    } else {
        echo "✗ Required tables don't exist. Run migrations first.\n";
    }

} catch (\Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    if ($e->getPrevious()) {
        echo "Previous: " . $e->getPrevious()->getMessage() . "\n";
    }
}