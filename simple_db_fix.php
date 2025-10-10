<?php

// Simple database connection and migration fix
$host = 'localhost';
$dbname = 'drivelink_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Database connection successful!\n\n";
    
    // Check current migrations
    echo "Checking migrations table...\n";
    $stmt = $pdo->query("SELECT migration FROM migrations WHERE migration LIKE '%deleted_at%' OR migration LIKE '%company_request%'");
    $migrations = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (!empty($migrations)) {
        echo "Found migrations:\n";
        foreach ($migrations as $migration) {
            echo "- $migration\n";
        }
    } else {
        echo "No relevant migrations found.\n";
    }
    
    // Clean up duplicate migration entries
    echo "\nCleaning up duplicate migration entries...\n";
    
    $duplicatePatterns = [
        '%add_deleted_at_to_admin_users_table%',
        '%add_deleted_at_to_drivers_table%',
        '%add_deleted_at_to_companies_table%',
        '%add_deleted_at_to_company_requests_table%'
    ];
    
    $totalDeleted = 0;
    foreach ($duplicatePatterns as $pattern) {
        $stmt = $pdo->prepare("DELETE FROM migrations WHERE migration LIKE ?");
        $stmt->execute([$pattern]);
        $deleted = $stmt->rowCount();
        if ($deleted > 0) {
            echo "Deleted $deleted entries matching: $pattern\n";
            $totalDeleted += $deleted;
        }
    }
    
    if ($totalDeleted > 0) {
        echo "✅ Cleaned up $totalDeleted duplicate migration entries\n";
    } else {
        echo "No duplicate entries found to clean up\n";
    }
    
    // Check table structures
    echo "\nChecking table structures...\n";
    
    $tables = ['admin_users', 'drivers', 'companies', 'company_requests'];
    
    foreach ($tables as $table) {
        try {
            $stmt = $pdo->query("SHOW COLUMNS FROM `$table` LIKE 'deleted_at'");
            $hasDeletedAt = $stmt->rowCount() > 0;
            
            $status = $hasDeletedAt ? "✅ HAS deleted_at" : "❌ MISSING deleted_at";
            echo "Table '$table': $status\n";
            
        } catch (PDOException $e) {
            echo "Table '$table': Does not exist or error - " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n✅ Database cleanup completed successfully!\n";
    echo "\nYou can now try running: php artisan migrate\n";
    
} catch (PDOException $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
}