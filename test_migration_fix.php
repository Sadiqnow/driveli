<?php

require_once 'vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;

$capsule = new Capsule;

$capsule->addConnection([
    'driver' => 'mysql',
    'host' => 'localhost',
    'database' => 'drivelink_db', 
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
]);

$capsule->setAsGlobal();
$capsule->bootEloquent();

try {
    // Check if company_requests table exists and if it has deleted_at column
    echo "Checking company_requests table structure...\n";
    
    $columns = $capsule->getConnection()->select("SHOW COLUMNS FROM company_requests");
    
    $hasDeletedAt = false;
    echo "Columns in company_requests table:\n";
    foreach ($columns as $column) {
        echo "- {$column->Field} ({$column->Type})\n";
        if ($column->Field === 'deleted_at') {
            $hasDeletedAt = true;
        }
    }
    
    if ($hasDeletedAt) {
        echo "\n✅ deleted_at column already exists in company_requests table\n";
        echo "The duplicate migration error should now be resolved.\n";
    } else {
        echo "\n❌ deleted_at column does not exist in company_requests table\n";
    }
    
    // Check migrations table
    echo "\nChecking which migrations have been run:\n";
    $migrations = $capsule->getConnection()->select("SELECT migration FROM migrations WHERE migration LIKE '%company_request%' OR migration LIKE '%deleted_at%'");
    foreach ($migrations as $migration) {
        echo "✓ {$migration->migration}\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}