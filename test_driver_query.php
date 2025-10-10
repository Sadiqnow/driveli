<?php

require_once 'vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel');

try {
    // Test the Driver model query that's likely causing the error
    $drivers = App\Models\Driver::all();
    echo "✅ Successfully queried drivers table!\n";
    echo "Found " . count($drivers) . " drivers.\n";
    
} catch (Exception $e) {
    echo "❌ Error occurred:\n";
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    
    // If it's the deleted_at error, let's fix it
    if (strpos($e->getMessage(), "deleted_at") !== false) {
        echo "\n🔧 This is the deleted_at column error. Attempting to fix...\n";
        
        try {
            // Connect directly to database and add the column
            $pdo = new PDO(
                'mysql:host=' . $_ENV['DB_HOST'] . ';dbname=' . $_ENV['DB_DATABASE'],
                $_ENV['DB_USERNAME'],
                $_ENV['DB_PASSWORD']
            );
            
            // Add deleted_at column if it doesn't exist
            $sql = "ALTER TABLE drivers ADD COLUMN IF NOT EXISTS deleted_at TIMESTAMP NULL DEFAULT NULL";
            $pdo->exec($sql);
            
            echo "✅ Added deleted_at column to drivers table!\n";
            
            // Test query again
            $drivers = App\Models\Driver::all();
            echo "✅ Driver query now works! Found " . count($drivers) . " drivers.\n";
            
        } catch (Exception $fixError) {
            echo "❌ Could not fix the issue: " . $fixError->getMessage() . "\n";
        }
    }
}