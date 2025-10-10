<?php

$logFile = __DIR__ . '/setup_verification.log';
$output = "=== DriveLink Database Verification ===\n";
$output .= "Timestamp: " . date('Y-m-d H:i:s') . "\n\n";

try {
    // Test 1: Basic PHP functionality
    $output .= "1. PHP Test:\n";
    $output .= "   Version: " . PHP_VERSION . "\n";
    $output .= "   ✓ PHP working\n\n";
    
    // Test 2: Database connection
    $output .= "2. Database Connection:\n";
    try {
        $pdo = new PDO("mysql:host=127.0.0.1;dbname=drivelink_db", "root", "");
        $output .= "   ✓ Connected to drivelink_db\n";
        
        // Check tables
        $stmt = $pdo->query("SHOW TABLES LIKE 'drivers'");
        $tableExists = $stmt->rowCount() > 0;
        $output .= "   drivers exists: " . ($tableExists ? "YES" : "NO") . "\n";
        
        if ($tableExists) {
            $stmt = $pdo->query("SELECT COUNT(*) FROM drivers");
            $count = $stmt->fetchColumn();
            $output .= "   Records: {$count}\n";
        }
        
    } catch (PDOException $e) {
        $output .= "   ❌ Database Error: " . $e->getMessage() . "\n";
    }
    $output .= "\n";
    
    // Test 3: Laravel
    $output .= "3. Laravel Test:\n";
    try {
        require_once __DIR__ . '/vendor/autoload.php';
        $app = require_once __DIR__ . '/bootstrap/app.php';
        $kernel = $app->make('Illuminate\Contracts\Console\Kernel');
        $kernel->bootstrap();
        
        $output .= "   ✓ Laravel bootstrapped\n";
        $output .= "   Configured DB: " . config('database.connections.mysql.database') . "\n";
        
        // Test model
        try {
            $count = \App\Models\DriverNormalized::count();
            $output .= "   ✓ Model access: {$count} drivers\n";
        } catch (Exception $e) {
            $output .= "   ❌ Model error: " . $e->getMessage() . "\n";
        }
        
    } catch (Exception $e) {
        $output .= "   ❌ Laravel error: " . $e->getMessage() . "\n";
    }
    
    $output .= "\n4. Summary:\n";
    if (strpos($output, '❌') === false) {
        $output .= "   ✅ All tests passed - system ready!\n";
        $output .= "   You can now create drivers through the admin panel.\n";
    } else {
        $output .= "   ⚠️  Some issues found - check errors above.\n";
    }
    
} catch (Exception $e) {
    $output .= "Fatal error: " . $e->getMessage() . "\n";
}

// Write to log file
file_put_contents($logFile, $output);

// Also try to echo (might not work in current environment)
echo $output;
echo "\nLog written to: {$logFile}\n";