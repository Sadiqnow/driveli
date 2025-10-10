<?php

require __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use App\Models\AdminUser;
use Illuminate\Support\Facades\Hash;

// Bootstrap Laravel
$app = new Application(__DIR__);
$app->singleton(
    Illuminate\Contracts\Http\Kernel::class,
    App\Http\Kernel::class
);
$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);
$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    echo "Testing Admin Login System...\n\n";
    
    // Check if admin users table exists
    $adminCount = AdminUser::count();
    echo "Total Admin Users in database: " . $adminCount . "\n";
    
    if ($adminCount > 0) {
        echo "\nExisting Admin Users:\n";
        $admins = AdminUser::all();
        foreach ($admins as $admin) {
            echo "- Email: " . $admin->email . " | Name: " . $admin->name . " | Role: " . $admin->role . " | Status: " . $admin->status . "\n";
        }
    } else {
        echo "No admin users found. Creating default superadmin...\n";
        
        $admin = AdminUser::create([
            'name' => 'Super Admin',
            'email' => 'admin@drivelink.com',
            'password' => 'password123',
            'phone' => '+2348012345678',
            'role' => 'Super Admin',
            'status' => 'Active',
            'email_verified_at' => now(),
        ]);
        
        echo "Created admin: " . $admin->email . "\n";
    }
    
    // Test login credentials
    echo "\nTesting login credentials:\n";
    $testEmail = 'admin@drivelink.com';
    $testPassword = 'password123';
    
    $admin = AdminUser::where('email', $testEmail)->first();
    
    if ($admin) {
        echo "Admin found: " . $admin->email . "\n";
        echo "Admin status: " . $admin->status . "\n";
        echo "Admin role: " . $admin->role . "\n";
        
        // Check password
        if (Hash::check($testPassword, $admin->password)) {
            echo "✅ Password verification: SUCCESS\n";
        } else {
            echo "❌ Password verification: FAILED\n";
            
            // Try to fix password
            echo "Updating password...\n";
            $admin->update(['password' => Hash::make($testPassword)]);
            echo "Password updated. Please try logging in again.\n";
        }
    } else {
        echo "❌ Admin with email {$testEmail} not found\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}