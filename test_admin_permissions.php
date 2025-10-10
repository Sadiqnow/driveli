<?php

require 'vendor/autoload.php';

try {
    $app = require 'bootstrap/app.php';
    $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
    
    echo "🔍 Testing admin permissions...\n\n";
    
    // Get all admin users
    $admins = \App\Models\AdminUser::all();
    
    echo "Found " . $admins->count() . " admin user(s):\n\n";
    
    foreach ($admins as $admin) {
        echo "👤 Admin: {$admin->name} ({$admin->email})\n";
        echo "   Role: {$admin->role}\n";
        echo "   Status: {$admin->status}\n";
        
        // Check current permissions
        $currentPermissions = $admin->getAllPermissions();
        echo "   Current Permissions: " . (empty($currentPermissions) ? 'None' : implode(', ', $currentPermissions)) . "\n";
        
        // Test specific permission
        $hasManageUsers = $admin->hasPermission('manage_users');
        echo "   Has 'manage_users' permission: " . ($hasManageUsers ? 'YES' : 'NO') . "\n";
        
        // If admin doesn't have proper permissions, fix them
        if (!$hasManageUsers && ($admin->role === 'Super Admin' || $admin->role === 'Admin')) {
            echo "   ⚠️  Fixing missing permissions...\n";
            
            $defaultPermissions = [];
            if ($admin->role === 'Super Admin') {
                $defaultPermissions = [
                    'manage_users', 'manage_drivers', 'manage_companies', 
                    'manage_requests', 'manage_matches', 'manage_commissions', 
                    'view_reports', 'manage_notifications', 'manage_settings', 'delete_records'
                ];
            } elseif ($admin->role === 'Admin') {
                $defaultPermissions = [
                    'manage_users', 'manage_drivers', 'manage_companies', 
                    'manage_requests', 'view_reports', 'manage_notifications'
                ];
            }
            
            $admin->update(['permissions' => $defaultPermissions]);
            echo "   ✅ Permissions updated: " . implode(', ', $defaultPermissions) . "\n";
        }
        
        echo "\n";
    }
    
    echo "🎉 Permission check complete!\n";
    
    // Test Gate functionality
    if ($admins->count() > 0) {
        $testAdmin = $admins->first();
        echo "🧪 Testing Gate functionality with {$testAdmin->name}...\n";
        
        // Authenticate the admin user for Gate testing
        auth('admin')->login($testAdmin);
        
        $canManageUsers = auth('admin')->user()->can('manage_users');
        echo "   Can manage users (via Gate): " . ($canManageUsers ? 'YES' : 'NO') . "\n";
        
        if ($canManageUsers) {
            echo "✅ Authorization should work now!\n";
        } else {
            echo "❌ Authorization still not working. Check permissions array.\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}