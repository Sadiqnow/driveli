<?php

require 'vendor/autoload.php';

// Fix admin permissions for existing users
try {
    $app = require 'bootstrap/app.php';
    $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();
    
    echo "🔧 Fixing admin permissions...\n";
    
    $admins = \App\Models\AdminUser::all();
    
    foreach ($admins as $admin) {
        $permissions = [];
        
        switch ($admin->role) {
            case 'Super Admin':
                $permissions = [
                    'manage_users',
                    'manage_drivers',
                    'manage_companies',
                    'manage_requests',
                    'manage_matches',
                    'manage_commissions',
                    'view_reports',
                    'manage_notifications',
                    'manage_settings',
                    'delete_records'
                ];
                break;
                
            case 'Admin':
                $permissions = [
                    'manage_users',
                    'manage_drivers',
                    'manage_companies',
                    'manage_requests',
                    'view_reports',
                    'manage_notifications'
                ];
                break;
                
            case 'Moderator':
                $permissions = [
                    'manage_drivers',
                    'manage_requests',
                    'view_reports'
                ];
                break;
                
            default:
                $permissions = ['view_reports'];
                break;
        }
        
        $admin->update(['permissions' => $permissions]);
        
        echo "✅ Updated permissions for {$admin->name} ({$admin->role})\n";
        echo "   Permissions: " . implode(', ', $permissions) . "\n";
    }
    
    echo "\n🎉 Admin permissions fixed successfully!\n";
    echo "Total admins updated: " . $admins->count() . "\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}