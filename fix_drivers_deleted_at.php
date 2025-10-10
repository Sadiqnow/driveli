<?php
// Fix deleted_at column for drivers table
try {
    $pdo = new PDO(
        'mysql:host=127.0.0.1;dbname=drivelink_db',
        'root',
        ''
    );
    
    echo "Connected to database successfully!\n";
    
    // Check if deleted_at column exists
    $stmt = $pdo->prepare("SHOW COLUMNS FROM drivers LIKE 'deleted_at'");
    $stmt->execute();
    $columnExists = $stmt->fetch();
    
    if (!$columnExists) {
        echo "Adding deleted_at column to drivers table...\n";
        
        // Add deleted_at column
        $sql = "ALTER TABLE drivers ADD COLUMN deleted_at TIMESTAMP NULL DEFAULT NULL";
        $pdo->exec($sql);
        
        echo "✅ Successfully added deleted_at column to drivers table!\n";
    } else {
        echo "✅ deleted_at column already exists in drivers table.\n";
    }
    
    // Verify the column was added
    $stmt = $pdo->prepare("DESCRIBE drivers");
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $deletedAtFound = false;
    foreach ($columns as $column) {
        if ($column['Field'] === 'deleted_at') {
            $deletedAtFound = true;
            echo "✅ Confirmed: deleted_at column exists with type: " . $column['Type'] . "\n";
            break;
        }
    }
    
    if (!$deletedAtFound) {
        echo "❌ ERROR: deleted_at column still not found!\n";
    }
    
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
}