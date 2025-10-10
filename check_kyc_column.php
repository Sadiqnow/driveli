<?php

require 'vendor/autoload.php';

// Load Laravel app
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

try {
    // Check if column exists
    $hasColumn = Schema::hasColumn('drivers', 'kyc_rejection_reason');
    echo "Column kyc_rejection_reason exists: " . ($hasColumn ? 'YES' : 'NO') . "\n";
    
    // Get table structure
    $columns = DB::select("DESCRIBE drivers");
    echo "\nColumns in drivers table:\n";
    foreach ($columns as $column) {
        if (strpos($column->Field, 'kyc') !== false) {
            echo "- {$column->Field} ({$column->Type}) - Null: {$column->Null} - Default: {$column->Default}\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}