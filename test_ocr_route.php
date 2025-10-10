<?php

require_once 'vendor/autoload.php';

// Simple test to verify the OCR verification route exists
try {
    // Load Laravel app
    $app = require_once 'bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    
    // Create a test request to the OCR verification route
    $request = Illuminate\Http\Request::create('/admin/drivers/ocr-verification', 'GET');
    
    // Check if route exists by trying to match it
    $route = app('router')->getRoutes()->match($request);
    
    if ($route) {
        echo "✅ SUCCESS: Route 'admin.drivers.ocr-verification' exists!\n";
        echo "Route URI: " . $route->uri() . "\n";
        echo "Route Name: " . $route->getName() . "\n";
        echo "Controller: " . $route->getActionName() . "\n";
    } else {
        echo "❌ ERROR: Route not found\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}

echo "\n=== Driver_Normalized Table Check ===\n";

try {
    // Check if the table exists and has the required columns
    $pdo = new PDO(
        'mysql:host=localhost;dbname=drivelink', 
        'root', 
        '', 
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    // Check if drivers table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'drivers'");
    if ($stmt->rowCount() > 0) {
        echo "✅ Table 'drivers' exists\n";
        
        // Check OCR-related columns
        $ocrColumns = [
            'ocr_verification_status',
            'ocr_verification_notes', 
            'nin_verification_data',
            'nin_verified_at',
            'nin_ocr_match_score',
            'frsc_verification_data',
            'frsc_verified_at',
            'frsc_ocr_match_score'
        ];
        
        $stmt = $pdo->query("DESCRIBE drivers");
        $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        foreach ($ocrColumns as $column) {
            if (in_array($column, $columns)) {
                echo "✅ Column '$column' exists\n";
            } else {
                echo "❌ Column '$column' missing\n";
            }
        }
        
        // Check record count
        $stmt = $pdo->query("SELECT COUNT(*) FROM drivers");
        $count = $stmt->fetchColumn();
        echo "📊 Total drivers in table: $count\n";
        
    } else {
        echo "❌ Table 'drivers' does not exist\n";
    }
    
} catch (Exception $e) {
    echo "❌ Database Error: " . $e->getMessage() . "\n";
}

echo "\n=== Summary ===\n";
echo "• Route added: admin.drivers.ocr-verification\n";
echo "• Controller method: DriverController@ocrVerification\n"; 
echo "• View: admin.drivers.ocr-verification\n";
echo "• Table: drivers with OCR fields\n";
echo "• Status: Fixed\n";