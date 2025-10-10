<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Contracts\Console\Kernel;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

echo "=== Drivelink System Integration Test ===\n\n";

// Test 1: Database Connection
echo "1. Testing Database Connection...\n";
try {
    $pdo = DB::connection()->getPdo();
    echo "   ✓ Database connection successful\n";
} catch (Exception $e) {
    echo "   ✗ Database connection failed: " . $e->getMessage() . "\n";
}

// Test 2: AdminUser Model
echo "\n2. Testing AdminUser Model...\n";
try {
    $adminCount = \App\Models\AdminUser::count();
    echo "   ✓ AdminUser model working. Total admins: {$adminCount}\n";
} catch (Exception $e) {
    echo "   ✗ AdminUser model error: " . $e->getMessage() . "\n";
}

// Test 3: DriverNormalized Model  
echo "\n3. Testing DriverNormalized Model...\n";
try {
    $driverCount = \App\Models\DriverNormalized::count();
    echo "   ✓ DriverNormalized model working. Total drivers: {$driverCount}\n";
} catch (Exception $e) {
    echo "   ✗ DriverNormalized model error: " . $e->getMessage() . "\n";
}

// Test 4: Authentication Guards
echo "\n4. Testing Authentication Guards...\n";
try {
    $adminGuard = Auth::guard('admin');
    $driverGuard = Auth::guard('driver');
    echo "   ✓ Admin guard configured\n";
    echo "   ✓ Driver guard configured\n";
} catch (Exception $e) {
    echo "   ✗ Authentication guard error: " . $e->getMessage() . "\n";
}

// Test 5: OCR Service
echo "\n5. Testing OCR Service...\n";
try {
    $ocrService = new \App\Services\OCRVerificationService();
    echo "   ✓ OCRVerificationService instantiated successfully\n";
} catch (Exception $e) {
    echo "   ✗ OCRVerificationService error: " . $e->getMessage() . "\n";
}

// Test 6: Route Registration
echo "\n6. Testing Route Registration...\n";
try {
    $routes = Route::getRoutes();
    $adminRoutes = 0;
    foreach ($routes as $route) {
        if (str_starts_with($route->uri(), 'admin/')) {
            $adminRoutes++;
        }
    }
    echo "   ✓ Routes registered successfully. Admin routes: {$adminRoutes}\n";
} catch (Exception $e) {
    echo "   ✗ Route registration error: " . $e->getMessage() . "\n";
}

// Test 7: Password Reset Table
echo "\n7. Testing Password Reset Table...\n";
try {
    $resetCount = DB::table('password_resets')->count();
    echo "   ✓ Password reset table accessible. Current tokens: {$resetCount}\n";
} catch (Exception $e) {
    echo "   ✗ Password reset table error: " . $e->getMessage() . "\n";
}

echo "\n=== Integration Test Complete ===\n";
echo "\nNext Steps:\n";
echo "1. Create an admin user via registration\n";
echo "2. Test admin login functionality\n";
echo "3. Test driver CRUD operations\n";
echo "4. Test OCR verification workflow\n";
echo "5. Test password reset functionality\n";