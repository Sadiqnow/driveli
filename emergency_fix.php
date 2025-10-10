<?php
// Emergency fix for missing columns
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Emergency Column Fix</h1>";

// Database configuration
$host = 'localhost';
$dbname = 'drivelink';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<p style='color: green;'>✅ Connected to database</p>";
    
    // SQL statements to add missing columns
    $sqlStatements = [
        "ALTER TABLE drivers ADD COLUMN city VARCHAR(100) NULL",
        "ALTER TABLE drivers ADD COLUMN postal_code VARCHAR(10) NULL", 
        "ALTER TABLE drivers ADD COLUMN license_issue_date DATE NULL",
        "ALTER TABLE drivers ADD COLUMN years_of_experience INT NULL",
        "ALTER TABLE drivers ADD COLUMN previous_company VARCHAR(100) NULL",
        "ALTER TABLE drivers ADD COLUMN bank_id BIGINT UNSIGNED NULL",
        "ALTER TABLE drivers ADD COLUMN account_number VARCHAR(20) NULL",
        "ALTER TABLE drivers ADD COLUMN account_name VARCHAR(100) NULL",
        "ALTER TABLE drivers ADD COLUMN bvn VARCHAR(11) NULL", 
        "ALTER TABLE drivers ADD COLUMN residential_address TEXT NULL",
        "ALTER TABLE drivers ADD COLUMN has_vehicle BOOLEAN NULL",
        "ALTER TABLE drivers ADD COLUMN vehicle_type VARCHAR(100) NULL",
        "ALTER TABLE drivers ADD COLUMN vehicle_year INT NULL",
        "ALTER TABLE drivers ADD COLUMN preferred_work_location VARCHAR(255) NULL",
        "ALTER TABLE drivers ADD COLUMN available_for_night_shifts BOOLEAN NULL",
        "ALTER TABLE drivers ADD COLUMN available_for_weekend_work BOOLEAN NULL"
    ];
    
    echo "<h2>Adding Missing Columns</h2>";
    
    foreach ($sqlStatements as $sql) {
        try {
            $pdo->exec($sql);
            $columnName = preg_match('/ADD COLUMN (\w+)/', $sql, $matches) ? $matches[1] : 'unknown';
            echo "<p style='color: green;'>✅ Added column: $columnName</p>";
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate column') !== false) {
                $columnName = preg_match('/ADD COLUMN (\w+)/', $sql, $matches) ? $matches[1] : 'unknown';
                echo "<p style='color: orange;'>⏭️ Column already exists: $columnName</p>";
            } else {
                echo "<p style='color: red;'>❌ Error with SQL: $sql<br>Error: " . $e->getMessage() . "</p>";
            }
        }
    }
    
    // Verify columns exist
    echo "<h2>Verification</h2>";
    $stmt = $pdo->query("DESCRIBE drivers");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    $requiredColumns = ['city', 'postal_code', 'residential_address', 'bank_id', 'account_number', 'bvn', 'years_of_experience'];
    
    foreach ($requiredColumns as $col) {
        if (in_array($col, $columns)) {
            echo "<p style='color: green;'>✅ $col exists</p>";
        } else {
            echo "<p style='color: red;'>❌ $col missing</p>";
        }
    }
    
    echo "<p style='color: green;'><strong>✅ Column fix process completed!</strong></p>";
    echo "<p>Total columns in drivers: " . count($columns) . "</p>";
    
} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Database error: " . $e->getMessage() . "</p>";
}
?>

<script>
setTimeout(function() {
    alert('Column fix completed! Check the page for results.');
}, 1000);
</script>