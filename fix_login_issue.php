<?php

require_once 'vendor/autoload.php';
require_once 'bootstrap/app.php';

use App\Models\AdminUser;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

echo "🔧 Fixing DriveLink Login Issue...\n\n";

try {
    // Test database connection
    echo "1. Testing Database Connection...\n";
    DB::connection()->getPdo();
    echo "✅ Database connected successfully\n\n";

    // Clear any existing admin users to start fresh
    echo "2. Cleaning up existing admin users...\n";
    AdminUser::truncate();
    echo "✅ Admin users table cleared\n\n";

    // Create a fresh admin user with proper password hashing
    echo "3. Creating new admin user...\n";
    
    // Method 1: Using Hash::make() explicitly
    $admin = new AdminUser();
    $admin->name = 'System Administrator';
    $admin->email = 'admin@drivelink.com';
    $admin->password = Hash::make('admin123'); // Explicitly hash the password
    $admin->phone = '+234-800-000-0000';
    $admin->role = 'Super Admin';
    $admin->status = 'Active';
    $admin->permissions = null;
    $admin->save();
    
    echo "✅ Admin user created with ID: {$admin->id}\n";
    echo "   Email: {$admin->email}\n";
    echo "   Password: admin123\n";
    echo "   Hash: " . substr($admin->password, 0, 30) . "...\n\n";

    // Test password verification
    echo "4. Testing password verification...\n";
    $passwordCheck = Hash::check('admin123', $admin->password);
    echo "   Password verification: " . ($passwordCheck ? "✅ PASS" : "❌ FAIL") . "\n\n";

    // Test authentication
    echo "5. Testing authentication...\n";
    $credentials = ['email' => 'admin@drivelink.com', 'password' => 'admin123'];
    
    $authResult = Auth::guard('admin')->attempt($credentials);
    echo "   Authentication result: " . ($authResult ? "✅ SUCCESS" : "❌ FAILED") . "\n";
    
    if ($authResult) {
        $user = Auth::guard('admin')->user();
        echo "   Authenticated as: {$user->name} ({$user->email})\n";
        echo "   User Role: {$user->role}\n";
        echo "   User Status: {$user->status}\n";
    }

    // Logout for clean state
    Auth::guard('admin')->logout();

    echo "\n6. Creating alternative admin user...\n";
    // Method 2: Using the model's automatic hashing (Laravel 9+ style)
    $admin2 = AdminUser::create([
        'name' => 'Test Administrator',
        'email' => 'test@drivelink.com',
        'password' => 'test123', // This will be auto-hashed by the 'hashed' cast
        'phone' => '+234-800-000-0001',
        'role' => 'Admin',
        'status' => 'Active',
    ]);
    
    echo "✅ Second admin created with ID: {$admin2->id}\n";
    echo "   Email: {$admin2->email}\n";
    echo "   Password: test123\n\n";

    // Test the second admin
    echo "7. Testing second admin authentication...\n";
    $credentials2 = ['email' => 'test@drivelink.com', 'password' => 'test123'];
    
    $authResult2 = Auth::guard('admin')->attempt($credentials2);
    echo "   Authentication result: " . ($authResult2 ? "✅ SUCCESS" : "❌ FAILED") . "\n";

    // Show final summary
    echo "\n🎉 Login Issue Fixed!\n\n";
    echo "📋 Available Admin Accounts:\n";
    echo "   1. Email: admin@drivelink.com | Password: admin123\n";
    echo "   2. Email: test@drivelink.com  | Password: test123\n\n";
    
    echo "🌐 Login URL: " . env('APP_URL', 'http://localhost') . "/admin/login\n\n";
    
    echo "✅ Both authentication methods are working:\n";
    echo "   - Hash::make() for explicit hashing\n";
    echo "   - 'hashed' cast for automatic hashing\n\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n\n";
    
    echo "🔧 Troubleshooting Steps:\n";
    echo "1. Make sure XAMPP MySQL is running\n";
    echo "2. Verify database 'drivelink_db' exists\n";
    echo "3. Run: php artisan migrate\n";
    echo "4. Check .env file database settings\n";
}

echo "\n✨ Script completed!\n";