<?php
echo "<h2>Drivelink Database Test</h2>";
echo "<p>Testing deleted_at column in drivers table...</p>";

try {
    // Database connection
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=drivelink_db', 'root', '');
    echo "<p style='color: green;'>✅ Database connection successful!</p>";
    
    // Check if deleted_at column exists in drivers table
    $stmt = $pdo->prepare("SHOW COLUMNS FROM drivers LIKE 'deleted_at'");
    $stmt->execute();
    $result = $stmt->fetch();
    
    if ($result) {
        echo "<p style='color: green;'>✅ deleted_at column exists in drivers table</p>";
        echo "<pre>" . print_r($result, true) . "</pre>";
    } else {
        echo "<p style='color: red;'>❌ deleted_at column NOT found in drivers table</p>";
        echo "<p>Adding the column now...</p>";
        
        // Add the column
        $pdo->exec("ALTER TABLE drivers ADD COLUMN deleted_at TIMESTAMP NULL DEFAULT NULL");
        echo "<p style='color: green;'>✅ Successfully added deleted_at column!</p>";
    }
    
    // Test a simple query
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM drivers WHERE deleted_at IS NULL");
    $stmt->execute();
    $count = $stmt->fetch();
    echo "<p style='color: green;'>✅ Query test successful! Found {$count['count']} active drivers</p>";
    
    // Show all columns in drivers table
    echo "<h3>All columns in drivers table:</h3>";
    $stmt = $pdo->prepare("DESCRIBE drivers");
    $stmt->execute();
    $columns = $stmt->fetchAll();
    echo "<ul>";
    foreach ($columns as $col) {
        echo "<li><strong>{$col['Field']}</strong> ({$col['Type']})</li>";
    }
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>