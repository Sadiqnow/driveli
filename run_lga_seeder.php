<?php

/**
 * LGA Seeder Runner
 * 
 * Direct runner for the Nigerian States and LGA seeder
 */

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Nigerian States and LGA Seeder Runner\n";
echo "====================================\n\n";

try {
    // Test database connection first
    echo "Testing database connection...\n";
    $pdo = \DB::connection()->getPdo();
    echo "✓ Database connection successful\n\n";
    
    // Check if tables exist
    echo "Checking required tables...\n";
    if (!\Schema::hasTable('states')) {
        echo "✗ 'states' table not found. Running migrations...\n";
        \Artisan::call('migrate', ['--force' => true]);
        echo \Artisan::output();
    } else {
        echo "✓ 'states' table exists\n";
    }
    
    if (!\Schema::hasTable('local_governments')) {
        echo "✗ 'local_governments' table not found. Running migrations...\n";
        \Artisan::call('migrate', ['--force' => true]);
        echo \Artisan::output();
    } else {
        echo "✓ 'local_governments' table exists\n";
    }
    
    echo "\n";
    
    // Check current state
    $currentStates = \App\Models\State::count();
    $currentLgas = \App\Models\LocalGovernment::count();
    
    echo "Current database state:\n";
    echo "  States: {$currentStates}\n";
    echo "  LGAs: {$currentLgas}\n\n";
    
    // Run the seeder
    echo "Running Nigerian States and LGA Seeder...\n";
    echo "==========================================\n";
    
    $seeder = new \Database\Seeders\NigerianStatesLGASeeder();
    $seeder->run();
    
    echo "==========================================\n";
    echo "Seeder completed successfully!\n\n";
    
    // Final state check
    $finalStates = \App\Models\State::count();
    $finalLgas = \App\Models\LocalGovernment::count();
    
    echo "Final database state:\n";
    echo "  States: {$finalStates}\n";
    echo "  LGAs: {$finalLgas}\n\n";
    
    // Test some sample queries
    echo "Sample data verification:\n";
    echo "========================\n";
    
    $sampleStates = \App\Models\State::take(5)->get(['id', 'name', 'code']);
    foreach ($sampleStates as $state) {
        $lgaCount = \App\Models\LocalGovernment::where('state_id', $state->id)->count();
        echo "  {$state->name} ({$state->code}): {$lgaCount} LGAs\n";
    }
    
    echo "\n✅ LGA seeder completed successfully!\n";
    echo "You can now use the LGA dropdown functionality in your application.\n";

} catch (\Exception $e) {
    echo "\n❌ Error occurred:\n";
    echo "Message: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    
    if ($e->getPrevious()) {
        echo "Previous error: " . $e->getPrevious()->getMessage() . "\n";
    }
    
    echo "\nTroubleshooting steps:\n";
    echo "1. Ensure XAMPP MySQL service is running\n";
    echo "2. Check that database 'drivelink_db' exists\n";
    echo "3. Verify database credentials in .env file\n";
    echo "4. Run 'php artisan migrate' manually if needed\n";
    
    exit(1);
}