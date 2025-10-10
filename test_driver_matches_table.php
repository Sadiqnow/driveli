<?php

require_once 'vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Support\Facades\Schema;

// Initialize Laravel app
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    // Check if driver_matches table exists
    $exists = Schema::hasTable('driver_matches');
    echo "Driver matches table exists: " . ($exists ? 'YES' : 'NO') . "\n";
    
    if ($exists) {
        // Get table columns
        $columns = Schema::getColumnListing('driver_matches');
        echo "Table columns:\n";
        foreach ($columns as $column) {
            echo "  - $column\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}