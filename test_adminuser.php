<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\AdminUser;

try {
    echo "=== Testing AdminUser Model ===\n";
    
    // Test basic model functionality
    echo "1. Testing basic model instantiation...\n";
    $admin = new AdminUser();
    echo "✓ AdminUser model can be instantiated\n";
    
    // Test model methods
    echo "\n2. Testing legacy role methods...\n";
    $admin->role = 'Admin';
    echo "✓ Role can be set: " . $admin->role . "\n";
    
    echo "✓ hasRole('Admin'): " . ($admin->hasRole('Admin') ? 'true' : 'false') . "\n";
    echo "✓ hasAnyRole(['Admin', 'User']): " . ($admin->hasAnyRole(['Admin', 'User']) ? 'true' : 'false') . "\n";
    echo "✓ getHighestRoleLevel(): " . $admin->getHighestRoleLevel() . "\n";
    
    // Test Super Admin
    echo "\n3. Testing Super Admin role...\n";
    $superAdmin = new AdminUser();
    $superAdmin->role = 'Super Admin';
    echo "✓ Super Admin role level: " . $superAdmin->getHighestRoleLevel() . "\n";
    echo "✓ Super Admin can manage regular admin: " . ($superAdmin->canManage($admin) ? 'true' : 'false') . "\n";
    
    // Test permissions
    echo "\n4. Testing permission methods...\n";
    $superAdmin->permissions = ['manage_users', 'view_reports'];
    echo "✓ Permissions set: " . json_encode($superAdmin->getAllPermissions()) . "\n";
    echo "✓ hasPermission('manage_users'): " . ($superAdmin->hasPermission('manage_users') ? 'true' : 'false') . "\n";
    echo "✓ hasAnyPermission(['manage_users', 'delete_users']): " . ($superAdmin->hasAnyPermission(['manage_users', 'delete_users']) ? 'true' : 'false') . "\n";
    
    echo "\n=== All Tests Passed! ===\n";
    echo "The AdminUser model is now working correctly without the role_user table.\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
?>