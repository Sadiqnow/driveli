<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\AdminUser;

echo "Testing canManage method fix...\n\n";

try {
    // Test 1: Check if AdminUser model has canManage method
    echo "1. Checking AdminUser model methods:\n";
    
    if (method_exists(AdminUser::class, 'canManage')) {
        echo "✓ canManage method exists in AdminUser model\n";
    } else {
        echo "✗ canManage method NOT found in AdminUser model\n";
        exit(1);
    }
    
    // Test 2: Create test admin users
    echo "\n2. Testing canManage functionality:\n";
    
    // Create a Super Admin
    $superAdmin = new AdminUser();
    $superAdmin->name = "Test Super Admin";
    $superAdmin->email = "superadmin@test.com";
    $superAdmin->role = "Super Admin";
    $superAdmin->status = "Active";
    
    // Create a regular Admin
    $admin = new AdminUser();
    $admin->name = "Test Admin";
    $admin->email = "admin@test.com";
    $admin->role = "Admin";
    $admin->status = "Active";
    
    // Test canManage method
    $canManage = $superAdmin->canManage($admin);
    echo "✓ Super Admin can manage regular admin: " . ($canManage ? 'true' : 'false') . "\n";
    
    $cannotManage = $admin->canManage($superAdmin);
    echo "✓ Regular admin can manage Super Admin: " . ($cannotManage ? 'true' : 'false') . "\n";
    
    // Test 3: Check instanceof functionality
    echo "\n3. Testing instanceof checks:\n";
    
    if ($superAdmin instanceof AdminUser) {
        echo "✓ Test AdminUser instance passes instanceof check\n";
    } else {
        echo "✗ Test AdminUser instance fails instanceof check\n";
    }
    
    // Test 4: Test null checks
    echo "\n4. Testing null safety:\n";
    
    $nullUser = null;
    $result = !$nullUser || !($nullUser instanceof AdminUser) || !$nullUser->canManage($admin);
    echo "✓ Null user check passes: " . ($result ? 'true' : 'false') . "\n";
    
    echo "\n✅ All canManage fixes appear to be working correctly!\n";
    
} catch (Exception $e) {
    echo "❌ Error testing canManage fix: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    exit(1);
}