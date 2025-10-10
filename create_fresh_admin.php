<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\AdminUser;
use Illuminate\Support\Facades\Hash;

try {
    echo "Creating fresh admin user...\n";
    
    // Delete all existing admin users
    $deleted = AdminUser::query()->forceDelete();
    echo "Deleted existing admin users.\n";
    
    // Create new superadmin
    $admin = new AdminUser();
    $admin->name = 'Super Admin';
    $admin->email = 'admin@drivelink.com';
    $admin->password = Hash::make('password123');
    $admin->phone = '+2348012345678';
    $admin->role = 'Super Admin';
    $admin->status = 'Active';
    $admin->email_verified_at = now();
    $admin->permissions = [
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
    $admin->save();
    
    echo "✅ Created superadmin: {$admin->email}\n";
    echo "Login at: http://localhost/drivelink/public/admin/login\n";
    echo "Email: admin@drivelink.com\n";
    echo "Password: password123\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}