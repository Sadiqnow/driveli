<?php
// Direct database connection for table dump
$host = '127.0.0.1';
$dbname = 'drivelink_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== DATABASE CONNECTION SUCCESSFUL ===" . PHP_EOL . PHP_EOL;
    
    // Check drivers table
    echo "=== DRIVERS TABLE STRUCTURE ===" . PHP_EOL;
    $stmt = $pdo->query("DESCRIBE drivers");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    printf("%-25s %-20s %-8s %-8s %-10s %-15s" . PHP_EOL, 'Field', 'Type', 'Null', 'Key', 'Default', 'Extra');
    echo str_repeat('-', 90) . PHP_EOL;
    
    foreach ($columns as $column) {
        printf("%-25s %-20s %-8s %-8s %-10s %-15s" . PHP_EOL, 
            $column['Field'], 
            $column['Type'], 
            $column['Null'],
            $column['Key'] ?: '',
            $column['Default'] ?: 'NULL',
            $column['Extra'] ?: ''
        );
    }
    
    // Count records
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM drivers");
    $count = $stmt->fetch(PDO::FETCH_ASSOC);
    echo PHP_EOL . "Drivers table record count: " . $count['count'] . PHP_EOL . PHP_EOL;
    
    // Check drivers table
    try {
        echo "=== DRIVERS_NORMALIZED TABLE STRUCTURE ===" . PHP_EOL;
        $stmt = $pdo->query("DESCRIBE drivers");
        $normalizedColumns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        printf("%-25s %-20s %-8s %-8s %-10s %-15s" . PHP_EOL, 'Field', 'Type', 'Null', 'Key', 'Default', 'Extra');
        echo str_repeat('-', 90) . PHP_EOL;
        
        foreach ($normalizedColumns as $column) {
            printf("%-25s %-20s %-8s %-8s %-10s %-15s" . PHP_EOL, 
                $column['Field'], 
                $column['Type'], 
                $column['Null'],
                $column['Key'] ?: '',
                $column['Default'] ?: 'NULL',
                $column['Extra'] ?: ''
            );
        }
        
        // Count records
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM drivers");
        $normalizedCount = $stmt->fetch(PDO::FETCH_ASSOC);
        echo PHP_EOL . "Drivers_normalized table record count: " . $normalizedCount['count'] . PHP_EOL . PHP_EOL;
        
    } catch (PDOException $e) {
        echo "Drivers_normalized table does not exist or error: " . $e->getMessage() . PHP_EOL . PHP_EOL;
    }
    
    // Check for OCR columns
    echo "=== OCR COLUMNS CHECK ===" . PHP_EOL;
    $stmt = $pdo->query("SELECT COLUMN_NAME, TABLE_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = 'drivelink_db' AND COLUMN_NAME LIKE '%ocr%'");
    $ocrColumns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($ocrColumns)) {
        echo "OCR-related columns found:" . PHP_EOL;
        foreach ($ocrColumns as $col) {
            echo "- Table: {$col['TABLE_NAME']}, Column: {$col['COLUMN_NAME']}" . PHP_EOL;
        }
    } else {
        echo "No OCR-related columns found." . PHP_EOL;
    }
    echo PHP_EOL;
    
    // Check migration status
    try {
        echo "=== MIGRATION STATUS ===" . PHP_EOL;
        $stmt = $pdo->query("SELECT migration, batch FROM migrations ORDER BY batch DESC, migration DESC LIMIT 10");
        $migrations = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "Recent migrations:" . PHP_EOL;
        foreach ($migrations as $migration) {
            echo "- {$migration['migration']} (Batch: {$migration['batch']})" . PHP_EOL;
        }
    } catch (PDOException $e) {
        echo "Error checking migrations: " . $e->getMessage() . PHP_EOL;
    }
    
} catch (PDOException $e) {
    echo "Database connection failed: " . $e->getMessage() . PHP_EOL;
}

echo PHP_EOL . "=== DUMP COMPLETE ===" . PHP_EOL;