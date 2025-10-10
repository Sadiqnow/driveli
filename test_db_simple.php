<?php
echo "=== Database Connection Test ===\n\n";

// Test basic PHP-MySQL connection
try {
    echo "1. Testing basic MySQL connection...\n";
    $pdo = new PDO('mysql:host=127.0.0.1;port=3306', 'root', '');
    echo "   ✓ MySQL connection successful\n";
    
    // Check if database exists
    echo "\n2. Checking if database exists...\n";
    $stmt = $pdo->query("SHOW DATABASES LIKE 'drivelink_db'");
    if ($stmt->rowCount() > 0) {
        echo "   ✓ Database 'drivelink_db' exists\n";
        
        // Connect to the specific database
        $pdo = new PDO('mysql:host=127.0.0.1;dbname=drivelink_db', 'root', '');
        echo "   ✓ Connected to drivelink_db\n";
        
        // Check if tables exist
        echo "\n3. Checking tables...\n";
        $tables = ['drivers', 'admin_users', 'companies'];
        foreach ($tables as $table) {
            $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
            if ($stmt->rowCount() > 0) {
                echo "   ✓ Table '$table' exists\n";
            } else {
                echo "   ✗ Table '$table' missing\n";
            }
        }
        
        // Check drivers structure
        echo "\n4. Checking drivers structure...\n";
        try {
            $stmt = $pdo->query("DESCRIBE drivers");
            $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
            echo "   ✓ drivers has " . count($columns) . " columns\n";
            echo "   Key columns: " . implode(', ', array_slice($columns, 0, 5)) . "...\n";
        } catch (Exception $e) {
            echo "   ✗ Cannot describe drivers: " . $e->getMessage() . "\n";
        }
        
    } else {
        echo "   ✗ Database 'drivelink_db' does not exist\n";
        echo "   Creating database...\n";
        $pdo->exec("CREATE DATABASE drivelink_db");
        echo "   ✓ Database created\n";
    }
    
} catch (PDOException $e) {
    echo "   ✗ Database connection failed: " . $e->getMessage() . "\n";
    
    // Common troubleshooting
    echo "\n=== Troubleshooting ===\n";
    echo "1. Is XAMPP running? Check XAMPP Control Panel\n";
    echo "2. Is MySQL service started?\n";
    echo "3. Try starting XAMPP manually\n";
    echo "4. Check if port 3306 is available\n";
}

// Test Laravel database connection
echo "\n5. Testing Laravel database connection...\n";
try {
    require_once 'vendor/autoload.php';
    $app = require_once 'bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
    
    $connection = DB::connection();
    $connection->getPdo();
    echo "   ✓ Laravel database connection successful\n";
    
} catch (Exception $e) {
    echo "   ✗ Laravel database connection failed: " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n";
?>