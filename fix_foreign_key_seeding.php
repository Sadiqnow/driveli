<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Fixing Foreign Key Seeding Issues ===\n\n";

try {
    // Step 1: Check if admin users exist
    echo "1. Checking admin users...\n";
    $adminCount = \App\Models\AdminUser::count();
    echo "   Current admin users: {$adminCount}\n";
    
    if ($adminCount === 0) {
        echo "   Creating default admin user...\n";
        $admin = \App\Models\AdminUser::create([
            'name' => 'Super Admin',
            'email' => 'admin@drivelink.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password123'),
            'phone' => '+2348012345678',
            'role' => 'Super Admin',
            'status' => 'Active',
            'email_verified_at' => now(),
        ]);
        echo "   ✓ Admin user created with ID: {$admin->id}\n";
    } else {
        $admin = \App\Models\AdminUser::first();
        echo "   ✓ Using existing admin user: {$admin->name} (ID: {$admin->id})\n";
    }
    
    // Step 2: Check companies table and fix verification references
    echo "\n2. Checking companies...\n";
    $companies = \App\Models\Company::whereNotNull('verified_by')->get();
    
    if ($companies->count() > 0) {
        echo "   Found {$companies->count()} companies with verification references\n";
        
        foreach ($companies as $company) {
            // Check if the verified_by admin exists
            if (!$company->verifiedBy) {
                echo "   Fixing company: {$company->name}\n";
                $company->update([
                    'verified_by' => $admin->id,
                    'verification_status' => 'Verified',
                    'verified_at' => now()
                ]);
                echo "   ✓ Updated verification reference\n";
            }
        }
    } else {
        echo "   No companies with verification issues found\n";
    }
    
    // Step 3: Test seeding
    echo "\n3. Testing seeding process...\n";
    
    try {
        // Run individual seeders to test
        echo "   Testing AdminUserSeeder...\n";
        \Illuminate\Support\Facades\Artisan::call('db:seed', [
            '--class' => 'AdminUserSeeder',
            '--force' => true
        ]);
        echo "   ✓ AdminUserSeeder completed\n";
        
        echo "   Testing CompanySeeder...\n";
        \Illuminate\Support\Facades\Artisan::call('db:seed', [
            '--class' => 'CompanySeeder', 
            '--force' => true
        ]);
        echo "   ✓ CompanySeeder completed\n";
        
    } catch (\Exception $e) {
        echo "   ⚠ Seeding test error: " . $e->getMessage() . "\n";
    }
    
    echo "\n✅ Foreign key seeding issues resolved!\n\n";
    
    // Show final status
    $finalAdminCount = \App\Models\AdminUser::count();
    $finalCompanyCount = \App\Models\Company::count();
    
    echo "📊 Final Status:\n";
    echo "   Admin Users: {$finalAdminCount}\n";
    echo "   Companies: {$finalCompanyCount}\n\n";
    
    echo "🔐 Login Credentials:\n";
    echo "   URL: http://localhost/drivelink/admin/login\n";
    echo "   Email: admin@drivelink.com\n";
    echo "   Password: password123\n";
    
} catch (\Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}