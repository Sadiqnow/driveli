<?php
/**
 * Migration Conflict Resolution Script
 * 
 * This script will:
 * 1. Check for existing tables and their structure
 * 2. Remove duplicate migration files
 * 3. Reset migrations if needed
 * 4. Run fresh migrations
 */

require_once 'vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;

// Database connection
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

function info($message) {
    echo "[INFO] " . $message . "\n";
}

function success($message) {
    echo "[SUCCESS] " . $message . "\n";
}

function error($message) {
    echo "[ERROR] " . $message . "\n";
}

try {
    info("Starting migration conflict resolution...");
    
    // Step 1: Check existing tables
    info("Checking existing table structures...");
    
    $tables = ['admin_users', 'drivers', 'companies', 'company_requests'];
    $existingTables = [];
    
    foreach ($tables as $table) {
        try {
            $columns = $capsule->getConnection()->select("SHOW COLUMNS FROM `$table`");
            $existingTables[$table] = array_column($columns, 'Field');
            info("Table '$table' exists with " . count($columns) . " columns");
            
            // Check if deleted_at exists
            $hasDeletedAt = in_array('deleted_at', $existingTables[$table]);
            if ($hasDeletedAt) {
                info("Table '$table' already has deleted_at column");
            }
        } catch (Exception $e) {
            info("Table '$table' does not exist yet");
        }
    }
    
    // Step 2: List all migration files
    info("Listing migration files...");
    $migrationFiles = glob('database/migrations/*.php');
    sort($migrationFiles);
    
    $duplicateFiles = [];
    $keepFiles = [];
    
    foreach ($migrationFiles as $file) {
        $filename = basename($file);
        
        // Identify duplicate deleted_at migrations
        if (strpos($filename, 'add_deleted_at_to_') !== false) {
            $duplicateFiles[] = $file;
        } else {
            $keepFiles[] = $file;
        }
    }
    
    info("Found " . count($duplicateFiles) . " duplicate deleted_at migration files");
    info("Found " . count($keepFiles) . " core migration files");
    
    // Step 3: Remove duplicate migrations
    if (!empty($duplicateFiles)) {
        info("Removing duplicate migration files...");
        foreach ($duplicateFiles as $file) {
            if (file_exists($file)) {
                unlink($file);
                success("Deleted: " . basename($file));
            }
        }
    }
    
    // Step 4: Check if we need to refresh migrations
    info("Checking migration status...");
    
    // Try to run migrations
    $output = shell_exec('"C:\xampp\php\php.exe" artisan migrate --pretend 2>&1');
    
    if (strpos($output, 'Duplicate column name') !== false || 
        strpos($output, 'already exists') !== false) {
        
        info("Migration conflicts detected. Refreshing migrations...");
        
        // Drop and recreate the problematic migrations table entry
        try {
            $capsule->getConnection()->delete(
                "DELETE FROM migrations WHERE migration LIKE '%add_deleted_at%'"
            );
            success("Cleaned up duplicate migration entries from database");
        } catch (Exception $e) {
            error("Could not clean migration entries: " . $e->getMessage());
        }
    }
    
    // Step 5: Run migrations
    info("Running migrations...");
    $migrateOutput = shell_exec('"C:\xampp\php\php.exe" artisan migrate --force 2>&1');
    
    if ($migrateOutput) {
        info("Migration output: " . trim($migrateOutput));
    } else {
        success("Migrations completed successfully!");
    }
    
    // Step 6: Verify final state
    info("Verifying final table structures...");
    
    foreach ($tables as $table) {
        try {
            $columns = $capsule->getConnection()->select("SHOW COLUMNS FROM `$table`");
            $columnNames = array_column($columns, 'Field');
            
            $hasDeletedAt = in_array('deleted_at', $columnNames);
            $status = $hasDeletedAt ? "✓ HAS deleted_at" : "✗ MISSING deleted_at";
            
            info("Table '$table': " . count($columns) . " columns, $status");
            
        } catch (Exception $e) {
            info("Table '$table' does not exist: " . $e->getMessage());
        }
    }
    
    success("Migration conflict resolution completed!");
    
} catch (Exception $e) {
    error("Script failed: " . $e->getMessage());
    error("Stack trace: " . $e->getTraceAsString());
}