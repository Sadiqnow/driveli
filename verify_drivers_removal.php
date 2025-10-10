<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== DRIVELINK DRIVERS TABLE REMOVAL VERIFICATION ===\n\n";

// 1. Check if drivers table exists in database
echo "1. CHECKING DRIVERS TABLE IN DATABASE:\n";
try {
    $tables = DB::select("SHOW TABLES LIKE 'drivers'");
    if (empty($tables)) {
        echo "✓ DRIVERS TABLE NOT FOUND - Successfully removed from database\n";
    } else {
        echo "✗ DRIVERS TABLE STILL EXISTS in database\n";
        foreach ($tables as $table) {
            print_r($table);
        }
    }
} catch (Exception $e) {
    echo "Database error: " . $e->getMessage() . "\n";
}

echo "\n";

// 2. Check all tables in database
echo "2. ALL TABLES IN DATABASE:\n";
try {
    $allTables = DB::select("SHOW TABLES");
    foreach ($allTables as $table) {
        $tableName = array_values((array)$table)[0];
        echo "- " . $tableName . "\n";
    }
} catch (Exception $e) {
    echo "Database error: " . $e->getMessage() . "\n";
}

echo "\n=== END VERIFICATION ===\n";