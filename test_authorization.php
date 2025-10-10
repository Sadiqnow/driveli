<?php

require 'vendor/autoload.php';

try {
    $app = require 'bootstrap/app.php';
    $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
    
    echo "🧪 Testing admin authorization...\n\n";
    
    // Get the first admin user
    $admin = \App\Models\AdminUser::first();
    
    if (!$admin) {
        echo "❌ No admin users found. Please create an admin user first.\n";
        exit(1);
    }
    
    echo "👤 Testing with admin: {$admin->name} ({$admin->role})\n";
    echo "📧 Email: {$admin->email}\n";
    
    // Check permissions
    $permissions = $admin->getAllPermissions();
    echo "🔑 Permissions: " . (empty($permissions) ? 'None' : implode(', ', $permissions)) . "\n\n";
    
    // Test permission methods
    $hasManageUsers = $admin->hasPermission('manage_users');
    echo "✅ hasPermission('manage_users'): " . ($hasManageUsers ? 'YES' : 'NO') . "\n";
    
    // Simulate authentication
    auth('admin')->login($admin);
    echo "🔐 Authenticated as admin\n";
    
    // Test Gates
    $canManageUsers = auth('admin')->user()->can('manage_users');
    echo "✅ Can manage users (Gate): " . ($canManageUsers ? 'YES' : 'NO') . "\n";
    
    // Test specific route authorization
    $user = auth('admin')->user();
    $authorized = $user->can('manage_users') || $user->role === 'Super Admin';
    echo "✅ Route authorization: " . ($authorized ? 'AUTHORIZED' : 'DENIED') . "\n";
    
    echo "\n";
    
    if ($authorized) {
        echo "🎉 SUCCESS! The user should now be able to access /admin/users/create\n";
        echo "✅ The 403 error should be resolved!\n";
    } else {
        echo "❌ FAILED! The user still cannot access the admin users section.\n";
        echo "🔧 Consider running: php artisan admin:fix-permissions\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}