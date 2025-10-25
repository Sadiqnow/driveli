<?php

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\AdminUser;
use App\Models\Role;
use App\Models\Permission;

echo "=== FIXING SUPER ADMIN PERMISSIONS ===\n\n";

// Find the Super Admin user
$user = AdminUser::where('email', 'admin@drivelink.com')->first();
if (!$user) {
    echo "ERROR: Super Admin user not found!\n";
    exit(1);
}

echo "Found user: {$user->name} ({$user->email})\n";

// Check if Super Admin role exists
$superAdminRole = Role::where('name', 'super_admin')->first();
if (!$superAdminRole) {
    echo "ERROR: Super Admin role not found!\n";
    exit(1);
}

echo "Found Super Admin role: {$superAdminRole->name}\n";

// Assign the role to the user
try {
    if (!$user->roles()->where('role_id', $superAdminRole->id)->exists()) {
        $user->roles()->attach($superAdminRole->id);
        echo "✓ Assigned Super Admin role to user\n";
    } else {
        echo "✓ User already has Super Admin role\n";
    }
} catch (Exception $e) {
    echo "ERROR assigning role: " . $e->getMessage() . "\n";
}

// Check and assign permissions
$requiredPermissions = ['manage_superadmin', 'manage_drivers'];

foreach ($requiredPermissions as $permName) {
    $permission = Permission::where('name', $permName)->first();
    if (!$permission) {
        echo "ERROR: Permission '{$permName}' not found!\n";
        continue;
    }

    try {
        if (!$superAdminRole->permissions()->where('permission_id', $permission->id)->exists()) {
            $superAdminRole->permissions()->attach($permission->id);
            echo "✓ Assigned '{$permName}' permission to Super Admin role\n";
        } else {
            echo "✓ Super Admin role already has '{$permName}' permission\n";
        }
    } catch (Exception $e) {
        echo "ERROR assigning permission '{$permName}': " . $e->getMessage() . "\n";
    }
}

// Update user's permissions array as backup
$user->permissions = $requiredPermissions;
$user->save();

echo "\n=== VERIFICATION ===\n";

// Re-check permissions
$user->refresh();
echo 'Role: ' . $user->role . PHP_EOL;
echo 'Permissions: ' . json_encode($user->permissions) . PHP_EOL;
echo 'Has Super Admin role: ' . ($user->hasRole('super_admin') ? 'YES' : 'NO') . PHP_EOL;
echo 'Has manage_superadmin permission: ' . ($user->hasPermission('manage_superadmin') ? 'YES' : 'NO') . PHP_EOL;
echo 'Has manage_drivers permission: ' . ($user->hasPermission('manage_drivers') ? 'YES' : 'NO') . PHP_EOL;
echo 'Is Super Admin: ' . ($user->isSuperAdmin() ? 'YES' : 'NO') . PHP_EOL;

echo "\n=== FIX COMPLETE ===\n";
