<?php
/**
 * Quick System Test for DriveLink
 */

echo "=== DriveLink Quick System Test ===\n";
echo "Started at: " . date('Y-m-d H:i:s') . "\n\n";

$results = [];
$errors = [];
$warnings = [];

// 1. PHP Version Test
echo "1. Testing PHP Version...\n";
$phpVersion = phpversion();
if (version_compare($phpVersion, '8.1', '>=')) {
    $results[] = "✅ PHP $phpVersion (Compatible)";
    echo "   ✅ PHP $phpVersion (Compatible)\n";
} else {
    $errors[] = "❌ PHP version $phpVersion is too old";
    echo "   ❌ PHP version $phpVersion is too old\n";
}

// 2. Environment File Test
echo "\n2. Testing Environment Configuration...\n";
if (file_exists('.env')) {
    echo "   ✅ .env file exists\n";
    $envContent = file_get_contents('.env');
    
    if (strpos($envContent, 'APP_KEY=base64:') !== false) {
        echo "   ✅ Application key is set\n";
        $results[] = "✅ Application key configured";
    } else {
        echo "   ❌ Application key not set\n";
        $errors[] = "❌ Application key not set";
    }
    
    if (strpos($envContent, 'DB_DATABASE=drivelink_db') !== false) {
        echo "   ✅ Database configuration found\n";
        $results[] = "✅ Database configuration found";
    } else {
        echo "   ⚠️  Database configuration incomplete\n";
        $warnings[] = "⚠️ Database configuration incomplete";
    }
} else {
    echo "   ❌ .env file missing\n";
    $errors[] = "❌ .env file missing";
}

// 3. Directory Permissions Test
echo "\n3. Testing Directory Permissions...\n";
$dirs = ['storage/app', 'storage/framework', 'storage/logs', 'bootstrap/cache'];
foreach ($dirs as $dir) {
    if (is_dir($dir) && is_writable($dir)) {
        echo "   ✅ $dir is writable\n";
        $results[] = "✅ $dir writable";
    } else {
        echo "   ❌ $dir is not writable\n";
        $errors[] = "❌ $dir not writable";
    }
}

// 4. Database Connection Test
echo "\n4. Testing Database Connection...\n";
try {
    $pdo = new PDO('mysql:host=127.0.0.1;port=3306;dbname=drivelink_db', 'root', '');
    echo "   ✅ Database connection successful\n";
    $results[] = "✅ Database connection successful";
    
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "   ✅ Found " . count($tables) . " tables\n";
    $results[] = "✅ Found " . count($tables) . " tables";
    
    $expectedTables = ['users', 'admin_users', 'drivers', 'companies'];
    $missing = [];
    foreach ($expectedTables as $table) {
        if (!in_array($table, $tables)) {
            $missing[] = $table;
        }
    }
    
    if (empty($missing)) {
        echo "   ✅ All critical tables present\n";
        $results[] = "✅ All critical tables present";
    } else {
        echo "   ❌ Missing tables: " . implode(', ', $missing) . "\n";
        $errors[] = "❌ Missing tables: " . implode(', ', $missing);
    }
    
} catch (Exception $e) {
    echo "   ❌ Database connection failed: " . $e->getMessage() . "\n";
    $errors[] = "❌ Database connection failed: " . $e->getMessage();
}

// 5. Laravel Files Test
echo "\n5. Testing Laravel Installation...\n";
if (file_exists('vendor/autoload.php')) {
    echo "   ✅ Composer autoloader found\n";
    $results[] = "✅ Composer dependencies installed";
} else {
    echo "   ❌ Composer autoloader missing\n";
    $errors[] = "❌ Composer dependencies missing";
}

if (file_exists('artisan')) {
    echo "   ✅ Artisan console available\n";
    $results[] = "✅ Artisan console available";
} else {
    echo "   ❌ Artisan console missing\n";
    $errors[] = "❌ Artisan console missing";
}

// 6. Route Files Test
echo "\n6. Testing Route Configuration...\n";
if (file_exists('routes/web.php')) {
    $webRoutes = file_get_contents('routes/web.php');
    if (strpos($webRoutes, 'admin') !== false) {
        echo "   ✅ Admin routes configured\n";
        $results[] = "✅ Admin routes configured";
    } else {
        echo "   ⚠️  Admin routes may be missing\n";
        $warnings[] = "⚠️ Admin routes incomplete";
    }
} else {
    echo "   ❌ Web routes file missing\n";
    $errors[] = "❌ Web routes file missing";
}

// 7. Controllers Test
echo "\n7. Testing Controllers...\n";
$controllers = ['AdminAuthController.php', 'DriverController.php', 'CompanyController.php'];
$foundControllers = 0;
foreach ($controllers as $controller) {
    if (file_exists("app/Http/Controllers/Admin/$controller") || 
        file_exists("app/Http/Controllers/$controller")) {
        $foundControllers++;
    }
}
echo "   ✅ Found $foundControllers critical controllers\n";
$results[] = "✅ Found $foundControllers critical controllers";

// 8. Views Test
echo "\n8. Testing Views...\n";
if (file_exists('resources/views/admin/login.blade.php')) {
    echo "   ✅ Admin login view found\n";
    $results[] = "✅ Admin login view found";
} else {
    echo "   ❌ Admin login view missing\n";
    $errors[] = "❌ Admin login view missing";
}

if (is_dir('resources/views/admin/drivers')) {
    echo "   ✅ Driver management views found\n";
    $results[] = "✅ Driver management views found";
} else {
    echo "   ❌ Driver management views missing\n";
    $errors[] = "❌ Driver management views missing";
}

// Generate Summary Report
echo "\n" . str_repeat("=", 50) . "\n";
echo "SYSTEM ASSESSMENT SUMMARY\n";
echo str_repeat("=", 50) . "\n";
echo "Total Tests: " . (count($results) + count($errors) + count($warnings)) . "\n";
echo "Passed: " . count($results) . "\n";
echo "Errors: " . count($errors) . "\n";
echo "Warnings: " . count($warnings) . "\n\n";

if (!empty($errors)) {
    echo "❌ CRITICAL ERRORS:\n";
    foreach ($errors as $error) {
        echo "  $error\n";
    }
    echo "\n";
}

if (!empty($warnings)) {
    echo "⚠️  WARNINGS:\n";
    foreach ($warnings as $warning) {
        echo "  $warning\n";
    }
    echo "\n";
}

// Overall Health Score
$totalTests = count($results) + count($errors) + count($warnings);
$successRate = ($totalTests > 0) ? (count($results) / $totalTests) * 100 : 0;

echo "OVERALL HEALTH SCORE: " . round($successRate, 1) . "%\n";

if ($successRate >= 90) {
    echo "🟢 EXCELLENT - System is fully functional\n";
} elseif ($successRate >= 70) {
    echo "🟡 GOOD - Minor issues need attention\n";
} elseif ($successRate >= 50) {
    echo "🟠 FAIR - Several issues require fixing\n";
} else {
    echo "🔴 POOR - Major issues need immediate attention\n";
}

echo "\nTest completed at: " . date('Y-m-d H:i:s') . "\n";
echo str_repeat("=", 50) . "\n";