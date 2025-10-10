<?php

require_once 'vendor/autoload.php';

// Initialize Laravel app
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    // Test the specific query that was failing
    $matches = App\Models\DriverMatch::all();
    echo "SUCCESS: DriverMatch query works! Found " . $matches->count() . " records.\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    
    // Let's check if the table exists with a direct query
    try {
        $result = DB::select("SHOW TABLES LIKE 'driver_matches'");
        if (empty($result)) {
            echo "Table 'driver_matches' does not exist in database.\n";
        } else {
            echo "Table 'driver_matches' exists but there's an access issue.\n";
        }
    } catch (Exception $e2) {
        echo "Cannot check table existence: " . $e2->getMessage() . "\n";
    }
}