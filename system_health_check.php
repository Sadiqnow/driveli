<?php

echo "=== DriveLink System Health Check ===\n\n";

// Check basic PHP
echo "1. PHP Status:\n";
echo "   Version: " . phpversion() . "\n";
echo "   Memory Limit: " . ini_get('memory_limit') . "\n";
echo "   Max Execution Time: " . ini_get('max_execution_time') . "\n";

// Check file structure
echo "\n2. File Structure:\n";
$requiredFiles = [
    'vendor/autoload.php',
    'bootstrap/app.php',
    '.env',
    'app/Http/Kernel.php',
    'app/Http/Controllers/Admin/AdminAuthController.php',
    'app/Http/Controllers/Driver/DriverJobController.php',
    'app/Http/Controllers/Drivers/DriverFileController.php',
    'app/Http/Middleware/TrustProxies.php',
    'app/Http/Middleware/VerifyCsrfToken.php',
    'public/build/manifest.json'
];

foreach ($requiredFiles as $file) {
    echo "   " . $file . ": " . (file_exists($file) ? "✓ EXISTS" : "✗ MISSING") . "\n";
}

// Check if Laravel can bootstrap
echo "\n3. Laravel Bootstrap:\n";
try {
    require_once 'vendor/autoload.php';
    echo "   ✓ Autoloader loaded\n";
    
    $app = require_once 'bootstrap/app.php';
    echo "   ✓ Laravel app bootstrapped\n";
    
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    echo "   ✓ HTTP Kernel loaded\n";
    
} catch (Exception $e) {
    echo "   ✗ Bootstrap failed: " . $e->getMessage() . "\n";
}

// Check database connection
echo "\n4. Database Connection:\n";
try {
    $pdo = new PDO(
        'mysql:host=localhost;dbname=drivelink',
        'root',
        '',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "   ✓ Database connection successful\n";
    
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "   ✓ Tables found: " . count($tables) . "\n";
    
    if (in_array('admin_users', $tables)) {
        $stmt = $pdo->query("SELECT COUNT(*) FROM admin_users");
        $adminCount = $stmt->fetchColumn();
        echo "   ✓ Admin users: " . $adminCount . "\n";
    }
    
} catch (Exception $e) {
    echo "   ✗ Database error: " . $e->getMessage() . "\n";
}

// Check critical services
echo "\n5. Laravel Services:\n";
if (isset($app)) {
    try {
        $authService = $app->make(\App\Services\AuthenticationService::class);
        echo "   ✓ AuthenticationService\n";
    } catch (Exception $e) {
        echo "   ✗ AuthenticationService: " . $e->getMessage() . "\n";
    }
    
    try {
        $validationService = $app->make(\App\Services\ValidationService::class);
        echo "   ✓ ValidationService\n";
    } catch (Exception $e) {
        echo "   ✗ ValidationService: " . $e->getMessage() . "\n";
    }
    
    try {
        $errorService = $app->make(\App\Services\ErrorHandlingService::class);
        echo "   ✓ ErrorHandlingService\n";
    } catch (Exception $e) {
        echo "   ✗ ErrorHandlingService: " . $e->getMessage() . "\n";
    }
}

echo "\n6. Recommendations:\n";
if (!file_exists('public/build/manifest.json')) {
    echo "   - Run 'npm run build' to compile frontend assets\n";
}
echo "   - Visit http://localhost/drivelink/admin/register to test registration\n";
echo "   - Check storage/logs/ for any runtime errors\n";

echo "\n=== Health Check Complete ===\n";