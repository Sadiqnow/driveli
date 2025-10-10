<?php

require_once 'vendor/autoload.php';

// Load Laravel app
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

try {
    echo "Checking database structure...\n\n";
    
    // Check if new tables exist
    $tables = [
        'countries' => 'Global countries table',
        'global_states' => 'Global states table', 
        'global_cities' => 'Global cities table',
        'driver_category_requirements' => 'Driver category requirements table',
        'global_vehicle_types' => 'Global vehicle types table',
        'global_languages' => 'Global languages table'
    ];
    
    foreach ($tables as $table => $description) {
        if (Schema::hasTable($table)) {
            $count = DB::table($table)->count();
            echo "✅ {$description} - EXISTS with {$count} records\n";
        } else {
            echo "❌ {$description} - MISSING\n";
        }
    }
    
    echo "\nChecking drivers table for new columns...\n";
    
    // Check if new columns exist in drivers
    $newColumns = [
        'driver_category' => 'Driver category field',
        'employment_preference' => 'Employment preference field',
        'country_id' => 'Country ID field',
        'timezone' => 'Timezone field',
        'spoken_languages' => 'Spoken languages field',
        'currency_preference' => 'Currency preference field',
        'rate_per_hour' => 'Rate per hour field',
        'commercial_license_number' => 'Commercial license field',
        'security_clearance_level' => 'Security clearance field'
    ];
    
    foreach ($newColumns as $column => $description) {
        if (Schema::hasColumn('drivers', $column)) {
            echo "✅ {$description} - EXISTS\n";
        } else {
            echo "❌ {$description} - MISSING\n";
        }
    }
    
    echo "\nDatabase structure check completed!\n";
    
} catch (Exception $e) {
    echo "Error checking database: " . $e->getMessage() . "\n";
}

?>