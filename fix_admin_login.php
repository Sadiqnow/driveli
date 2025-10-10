<?php

require __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    echo "=== ADMIN LOGIN TROUBLESHOOTING ===\n\n";
    
    // Test database connection
    try {
        \Illuminate\Support\Facades\DB::connection()->getPdo();
        echo "✅ Database connection: SUCCESS\n";
    } catch (Exception $e) {
        echo "❌ Database connection: FAILED\n";
        echo "Error: " . $e->getMessage() . "\n";
        exit(1);
    }
    
    // Check if admin_users table exists
    try {
        $tables = \Illuminate\Support\Facades\DB::select("SHOW TABLES LIKE 'admin_users'");
        if (count($tables) > 0) {
            echo "✅ admin_users table: EXISTS\n";
        } else {
            echo "❌ admin_users table: NOT EXISTS\n";
            echo "Running migration...\n";
            \Illuminate\Support\Facades\Artisan::call('migrate');
            echo "Migration completed.\n";
        }
    } catch (Exception $e) {
        echo "❌ Table check failed: " . $e->getMessage() . "\n";
    }
    
    // Check admin users
    try {
        $adminCount = \App\Models\AdminUser::count();
        echo "Total admin users: {$adminCount}\n";
        
        if ($adminCount == 0) {
            echo "Creating superadmin user...\n";
            
            $admin = \App\Models\AdminUser::create([
                'name' => 'Super Admin',
                'email' => 'admin@drivelink.com',
                'password' => 'password123', // Will be hashed by the model
                'phone' => '+2348012345678',
                'role' => 'Super Admin',
                'status' => 'Active',
                'email_verified_at' => now(),
                'permissions' => [
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
                ]
            ]);
            
            echo "✅ Superadmin created: {$admin->email}\n";
        } else {
            echo "Existing admin users:\n";
            $admins = \App\Models\AdminUser::all();
            foreach ($admins as $admin) {
                echo "- {$admin->email} | {$admin->name} | {$admin->role} | {$admin->status}\n";
            }
        }
        
        // Test superadmin login
        $superAdmin = \App\Models\AdminUser::where('email', 'admin@drivelink.com')->first();
        if ($superAdmin) {
            echo "\n=== TESTING SUPERADMIN LOGIN ===\n";
            echo "Email: admin@drivelink.com\n";
            echo "Password: password123\n";
            echo "Status: {$superAdmin->status}\n";
            echo "Role: {$superAdmin->role}\n";
            
            // Test password hash
            if (\Illuminate\Support\Facades\Hash::check('password123', $superAdmin->password)) {
                echo "✅ Password verification: SUCCESS\n";
            } else {
                echo "❌ Password verification: FAILED\n";
                echo "Fixing password hash...\n";
                $superAdmin->update([
                    'password' => \Illuminate\Support\Facades\Hash::make('password123')
                ]);
                echo "✅ Password fixed. Try logging in again.\n";
            }
            
            // Test auth guard
            try {
                $credentials = ['email' => 'admin@drivelink.com', 'password' => 'password123'];
                if (\Illuminate\Support\Facades\Auth::guard('admin')->attempt($credentials)) {
                    echo "✅ Auth guard test: SUCCESS\n";
                    \Illuminate\Support\Facades\Auth::guard('admin')->logout();
                } else {
                    echo "❌ Auth guard test: FAILED\n";
                }
            } catch (Exception $e) {
                echo "❌ Auth guard test error: " . $e->getMessage() . "\n";
            }
        } else {
            echo "❌ Superadmin not found\n";
        }
        
    } catch (Exception $e) {
        echo "❌ Admin user operations failed: " . $e->getMessage() . "\n";
    }
    
    echo "\n=== LOGIN INSTRUCTIONS ===\n";
    echo "1. Go to: http://localhost:8000/admin/login\n";
    echo "2. Email: admin@drivelink.com\n";
    echo "3. Password: password123\n";
    echo "\nIf login still fails, check Laravel logs in storage/logs/\n";
    
} catch (Exception $e) {
    echo "❌ Script failed: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}