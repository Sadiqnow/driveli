<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    echo "=== Fixing Duplicate Admin User Issue ===\n\n";
    
    // Check if admin user exists
    $admin = \App\Models\AdminUser::where('email', 'admin@drivelink.com')->first();
    
    if ($admin) {
        echo "✓ Admin user 'admin@drivelink.com' already exists\n";
        echo "  - ID: {$admin->id}\n";
        echo "  - Name: {$admin->name}\n";
        echo "  - Role: {$admin->role}\n";
        echo "  - Status: {$admin->status}\n";
        echo "  - Created: {$admin->created_at}\n\n";
        
        echo "No action needed - admin user already exists.\n";
        echo "You can log in with:\n";
        echo "  Email: admin@drivelink.com\n";
        echo "  Password: password123\n\n";
    } else {
        echo "Admin user 'admin@drivelink.com' does not exist.\n";
        echo "Creating admin user...\n";
        
        $admin = \App\Models\AdminUser::create([
            'name' => 'Super Admin',
            'email' => 'admin@drivelink.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password123'),
            'phone' => '+2348012345678',
            'role' => 'Super Admin',
            'status' => 'Active',
            'email_verified_at' => now(),
        ]);
        
        echo "✓ Admin user created successfully\n";
        echo "  - ID: {$admin->id}\n";
        echo "  - Email: {$admin->email}\n";
    }
    
    echo "\n=== Admin User Check Complete ===\n";
    echo "You can now run 'php artisan migrate' or 'php artisan db:seed' safely.\n";
    
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}