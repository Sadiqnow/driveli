<?php

// Bootstrap Laravel
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Laravel Database Status Test ===\n\n";

try {
    // Test basic database connection
    echo "1. Testing database connection...\n";
    $pdo = DB::connection()->getPdo();
    echo "✓ Connected successfully\n";
    
    // Show configured database
    $dbName = config('database.connections.mysql.database');
    echo "✓ Configured database: {$dbName}\n\n";
    
    // Check if drivers table exists
    echo "2. Checking drivers table...\n";
    $hasTable = Schema::hasTable('drivers');
    echo "   Table exists: " . ($hasTable ? "YES" : "NO") . "\n";
    
    if ($hasTable) {
        // Test model access
        try {
            $count = \App\Models\DriverNormalized::count();
            echo "   ✓ Model access works: {$count} records\n";
        } catch (Exception $e) {
            echo "   ❌ Model error: " . $e->getMessage() . "\n";
        }
    } else {
        echo "   ❌ Table missing - need to create it\n";
    }
    
    // List all tables
    echo "\n3. Available tables:\n";
    $tables = DB::select('SHOW TABLES');
    $tableKey = 'Tables_in_' . $dbName;
    foreach ($tables as $table) {
        echo "   - " . $table->$tableKey . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Database Error: " . $e->getMessage() . "\n";
    echo "Error file: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\nTest completed.\n";