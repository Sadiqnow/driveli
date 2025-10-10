<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    echo "=== Fixing Foreign Key Constraints ===\n\n";
    
    $db = \Illuminate\Support\Facades\DB::connection();
    $dbName = $db->getDatabaseName();
    
    // First, let's see what tables exist
    echo "1. Checking existing tables...\n";
    $tables = $db->select("SHOW TABLES");
    $tableColumn = "Tables_in_{$dbName}";
    
    $existingTables = [];
    foreach ($tables as $table) {
        $existingTables[] = $table->$tableColumn;
    }
    
    echo "Found " . count($existingTables) . " tables\n";
    
    // Check if drivers table exists
    $hasDriversTable = in_array('drivers', $existingTables);
    $hasDriversTable = in_array('drivers', $existingTables);
    
    echo "- drivers table: " . ($hasDriversTable ? "EXISTS" : "NOT FOUND") . "\n";
    echo "- drivers table: " . ($hasDriversTable ? "EXISTS" : "NOT FOUND") . "\n\n";
    
    if ($hasDriversTable) {
        echo "2. Checking foreign key constraints on 'drivers' table...\n";
        
        // Get foreign key constraints
        $constraints = $db->select("
            SELECT 
                CONSTRAINT_NAME,
                TABLE_NAME,
                COLUMN_NAME,
                REFERENCED_TABLE_NAME,
                REFERENCED_COLUMN_NAME
            FROM 
                INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
            WHERE 
                CONSTRAINT_SCHEMA = ?
                AND REFERENCED_TABLE_NAME = 'drivers'
        ", [$dbName]);
        
        if (count($constraints) > 0) {
            echo "Found " . count($constraints) . " foreign key constraints:\n";
            
            foreach ($constraints as $constraint) {
                echo "- {$constraint->TABLE_NAME}.{$constraint->COLUMN_NAME} -> {$constraint->REFERENCED_TABLE_NAME}.{$constraint->REFERENCED_COLUMN_NAME}\n";
                
                // Drop the foreign key constraint
                try {
                    echo "  Dropping constraint {$constraint->CONSTRAINT_NAME}...\n";
                    $db->statement("ALTER TABLE `{$constraint->TABLE_NAME}` DROP FOREIGN KEY `{$constraint->CONSTRAINT_NAME}`");
                    echo "  ✓ Constraint dropped successfully\n";
                } catch (\Exception $e) {
                    echo "  ✗ Failed to drop constraint: " . $e->getMessage() . "\n";
                }
            }
        } else {
            echo "No foreign key constraints found referencing 'drivers' table.\n";
        }
        
        echo "\n3. Attempting to drop 'drivers' table...\n";
        try {
            $db->statement("DROP TABLE IF EXISTS `drivers`");
            echo "✓ 'drivers' table dropped successfully\n";
        } catch (\Exception $e) {
            echo "✗ Failed to drop 'drivers' table: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n4. Checking for any remaining problematic tables...\n";
    
    // List of tables that might have issues
    $problematicTables = [
        'guarantors',
        'driver_matches', 
        'driver_performances',
        'commissions'
    ];
    
    foreach ($problematicTables as $table) {
        if (in_array($table, $existingTables)) {
            echo "- {$table}: EXISTS\n";
            
            // Check if this table has foreign keys to drivers
            $tableFks = $db->select("
                SELECT 
                    CONSTRAINT_NAME,
                    COLUMN_NAME,
                    REFERENCED_TABLE_NAME,
                    REFERENCED_COLUMN_NAME
                FROM 
                    INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                WHERE 
                    CONSTRAINT_SCHEMA = ?
                    AND TABLE_NAME = ?
                    AND REFERENCED_TABLE_NAME IN ('drivers', 'drivers')
            ", [$dbName, $table]);
            
            if (count($tableFks) > 0) {
                foreach ($tableFks as $fk) {
                    echo "  - Has FK: {$fk->COLUMN_NAME} -> {$fk->REFERENCED_TABLE_NAME}.{$fk->REFERENCED_COLUMN_NAME}\n";
                }
            }
        }
    }
    
    echo "\n=== Fix Complete ===\n";
    
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}