<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=drivelink_db', 'root', '');
    echo "Connected to drivelink_db successfully!\n";
    
    // Read SQL file
    $sql = file_get_contents('create_role_tables.sql');
    
    // Split by semicolon to execute individual statements
    $statements = explode(';', $sql);
    
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (!empty($statement)) {
            echo "Executing: " . substr($statement, 0, 50) . "...\n";
            $pdo->exec($statement);
        }
    }
    
    echo "All tables created successfully!\n";
    
    // Check tables
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    echo "\nFound " . count($tables) . " tables:\n";
    foreach ($tables as $table) {
        echo "- " . $table . "\n";
    }
    
    // Specifically check for our tables
    $required_tables = ['roles', 'permissions', 'role_user', 'permission_role'];
    foreach ($required_tables as $table) {
        if (in_array($table, $tables)) {
            echo "✓ $table table exists\n";
        } else {
            echo "✗ $table table missing\n";
        }
    }
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
}
?>