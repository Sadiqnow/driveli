<!DOCTYPE html>
<html>
<head>
    <title>Test CompanyRequest Model</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .success { color: green; }
        .error { color: red; }
        .info { color: blue; }
        pre { background: #f5f5f5; padding: 10px; border-radius: 4px; }
        .test { border: 1px solid #ddd; margin: 10px 0; padding: 10px; border-radius: 4px; }
    </style>
</head>
<body>
    <h1>Test CompanyRequest Model</h1>
    
    <?php
    // This script tests the CompanyRequest model functionality
    
    try {
        // Include Laravel bootstrap (if available)
        if (file_exists(__DIR__ . '/vendor/autoload.php')) {
            require_once __DIR__ . '/vendor/autoload.php';
            $laravel = require_once __DIR__ . '/bootstrap/app.php';
            echo "<p class='success'>✅ Laravel application loaded</p>";
            
            // Test the CompanyRequest model
            echo "<div class='test'>";
            echo "<h3>Testing CompanyRequest Model</h3>";
            
            $request = new \App\Models\CompanyRequest();
            echo "<p class='success'>✅ CompanyRequest model instantiated successfully</p>";
            echo "<p class='info'>Table name: " . $request->getTable() . "</p>";
            echo "<p class='info'>Fillable fields: " . implode(', ', $request->getFillable()) . "</p>";
            
            // Test query
            try {
                $count = \App\Models\CompanyRequest::count();
                echo "<p class='success'>✅ Model query successful! Record count: $count</p>";
            } catch (Exception $queryError) {
                echo "<p class='error'>❌ Model query failed: " . $queryError->getMessage() . "</p>";
            }
            
            echo "</div>";
            
        } else {
            echo "<p class='error'>❌ Laravel autoloader not found</p>";
            echo "<p class='info'>Testing database connection directly instead...</p>";
            
            // Direct database test
            echo "<div class='test'>";
            echo "<h3>Direct Database Test</h3>";
            
            $host = 'localhost';
            $dbname = 'drivelink_db';
            $username = 'root';
            $password = '';

            $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Test company_requests table
            $query = "SELECT COUNT(*) as count FROM company_requests";
            $stmt = $pdo->query($query);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo "<p class='success'>✅ Direct database query successful!</p>";
            echo "<p class='info'>company_requests table record count: " . $result['count'] . "</p>";
            
            // Test table structure matches model expectations
            $structQuery = "DESCRIBE company_requests";
            $structStmt = $pdo->query($structQuery);
            $fields = [];
            while ($row = $structStmt->fetch(PDO::FETCH_ASSOC)) {
                $fields[] = $row['Field'];
            }
            
            echo "<p class='info'>Available fields in table: " . implode(', ', $fields) . "</p>";
            
            // Check if all expected model fields exist
            $expectedFields = [
                'id', 'request_id', 'company_id', 'company_name', 'contact_person',
                'email', 'phone', 'request_type', 'vehicle_type', 'pickup_location',
                'delivery_location', 'pickup_date', 'delivery_date', 'cargo_weight',
                'cargo_description', 'budget', 'special_requirements', 'status',
                'priority', 'approved_at', 'rejected_at', 'cancelled_at',
                'completed_at', 'created_by_admin', 'notes', 'created_at',
                'updated_at', 'deleted_at'
            ];
            
            $missing = array_diff($expectedFields, $fields);
            if (empty($missing)) {
                echo "<p class='success'>✅ All expected fields are present in the table!</p>";
            } else {
                echo "<p class='error'>❌ Missing fields: " . implode(', ', $missing) . "</p>";
            }
            
            echo "</div>";
        }
        
    } catch (Exception $e) {
        echo "<p class='error'>❌ Error: " . $e->getMessage() . "</p>";
    }
    ?>
    
    <div class='test'>
        <h3>Testing Model Relationships</h3>
        <?php
        try {
            // Test if we can access related models
            if (class_exists('\App\Models\Company') && class_exists('\App\Models\DriverMatch')) {
                echo "<p class='success'>✅ Related models (Company, DriverMatch) are available</p>";
            } else {
                echo "<p class='info'>ℹ️ Related models not loaded (normal if testing outside Laravel)</p>";
            }
        } catch (Exception $e) {
            echo "<p class='error'>❌ Relationship test error: " . $e->getMessage() . "</p>";
        }
        ?>
    </div>
    
    <hr>
    <h3>Summary</h3>
    <p>This test verifies that:</p>
    <ul>
        <li>✅ The company_requests table exists and is accessible</li>
        <li>✅ All expected fields are present</li>
        <li>✅ The CompanyRequest model can query the table</li>
        <li>✅ The table structure matches the model expectations</li>
    </ul>
    
    <p><strong>If all tests pass, the "Table 'drivelink_db.company_requests' doesn't exist" error should be resolved!</strong></p>
    
    <p><a href="/drivelink/public/admin/login">← Back to Admin Login</a></p>
</body>
</html>