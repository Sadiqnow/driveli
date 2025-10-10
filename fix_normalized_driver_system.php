<?php

/**
 * DriveLink Normalized Driver System Fix
 * This script addresses common issues with the drivers table
 */

require_once 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== DriveLink Normalized Driver System Fix ===\n";
echo "Date: " . date('Y-m-d H:i:s') . "\n\n";

$issues = [];
$fixes = [];

try {
    // 1. Verify database connection
    echo "1. Testing database connection...\n";
    DB::connection()->getPdo();
    echo "   ✓ Connected to database: " . env('DB_DATABASE') . "\n";
    
    // 2. Check table existence
    echo "\n2. Checking required tables...\n";
    $tables = collect(DB::select('SHOW TABLES'))->pluck('Tables_in_' . env('DB_DATABASE', 'drivelink_db'));
    
    $requiredTables = [
        'drivers' => 'Main driver table',
        'admin_users' => 'Admin authentication',
        'states' => 'Location lookup',
        'nationalities' => 'Nationality lookup'
    ];
    
    foreach ($requiredTables as $table => $description) {
        if ($tables->contains($table)) {
            $count = DB::table($table)->count();
            echo "   ✓ $table exists ($count records)\n";
        } else {
            echo "   ✗ $table missing - $description\n";
            $issues[] = "Missing table: $table";
            $fixes[] = "Run migrations: php artisan migrate --force";
        }
    }
    
    // 3. Check drivers table structure
    if ($tables->contains('drivers')) {
        echo "\n3. Analyzing drivers table...\n";
        
        $columns = collect(DB::select('DESCRIBE drivers'))->pluck('Field');
        echo "   Table has " . $columns->count() . " columns\n";
        
        $requiredColumns = [
            'id' => 'Primary key',
            'driver_id' => 'Unique driver identifier',
            'first_name' => 'Required field',
            'surname' => 'Required field', 
            'phone' => 'Required field',
            'password' => 'Authentication',
            'status' => 'Driver status',
            'verification_status' => 'Verification state'
        ];
        
        foreach ($requiredColumns as $column => $description) {
            if ($columns->contains($column)) {
                echo "   ✓ $column column exists\n";
            } else {
                echo "   ✗ $column column missing - $description\n";
                $issues[] = "Missing column: drivers.$column";
            }
        }
        
        // Check for unique constraints
        echo "\n   Checking constraints...\n";
        $constraints = DB::select("SHOW INDEX FROM drivers WHERE Non_unique = 0");
        $uniqueColumns = collect($constraints)->pluck('Column_name')->unique();
        
        foreach (['phone', 'email', 'driver_id'] as $shouldBeUnique) {
            if ($uniqueColumns->contains($shouldBeUnique)) {
                echo "   ✓ $shouldBeUnique has unique constraint\n";
            } else {
                echo "   ⚠ $shouldBeUnique lacks unique constraint\n";
                $issues[] = "Missing unique constraint on $shouldBeUnique";
            }
        }
        
    } else {
        echo "\n3. ✗ drivers table does not exist!\n";
        $issues[] = "Primary table drivers is missing";
        $fixes[] = "Create table: php artisan migrate --force";
    }
    
    // 4. Test DriverNormalized model
    echo "\n4. Testing DriverNormalized model...\n";
    try {
        $model = new App\Models\DriverNormalized();
        echo "   ✓ Model instantiates successfully\n";
        echo "   Table: " . $model->getTable() . "\n";
        echo "   Primary key: " . $model->getKeyName() . "\n";
        echo "   Fillable count: " . count($model->getFillable()) . "\n";
        
        // Test model query
        if ($tables->contains('drivers')) {
            $count = App\Models\DriverNormalized::count();
            echo "   Model count: $count\n";
        }
        
    } catch (Exception $e) {
        echo "   ✗ Model error: " . $e->getMessage() . "\n";
        $issues[] = "DriverNormalized model has issues";
    }
    
    // 5. Test actual driver creation
    if ($tables->contains('drivers')) {
        echo "\n5. Testing driver creation...\n";
        try {
            DB::beginTransaction();
            
            $testData = [
                'driver_id' => 'DR' . time(),
                'first_name' => 'Test',
                'surname' => 'Driver',
                'phone' => '0812345' . rand(1000, 9999),
                'password' => 'testpass123'
            ];
            
            $driver = App\Models\DriverNormalized::create($testData);
            echo "   ✓ Driver creation successful\n";
            echo "   Created ID: " . $driver->id . "\n";
            echo "   Driver ID: " . $driver->driver_id . "\n";
            
            DB::rollback();
            echo "   ✓ Test data rolled back\n";
            
        } catch (Exception $e) {
            DB::rollback();
            echo "   ✗ Driver creation failed: " . $e->getMessage() . "\n";
            $issues[] = "Cannot create drivers: " . $e->getMessage();
            
            // Analyze the error
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                $fixes[] = "Check for duplicate phone/email before creation";
            } elseif (strpos($e->getMessage(), "doesn't exist") !== false) {
                $fixes[] = "Ensure drivers table exists with correct columns";
            } elseif (strpos($e->getMessage(), 'foreign key') !== false) {
                $fixes[] = "Check foreign key constraints in related tables";
            }
        }
    }
    
    // 6. Check admin user for testing
    if ($tables->contains('admin_users')) {
        echo "\n6. Checking admin users...\n";
        $adminCount = DB::table('admin_users')->count();
        echo "   Admin users: $adminCount\n";
        
        if ($adminCount === 0) {
            echo "   ⚠ No admin users found\n";
            $issues[] = "No admin users for testing";
            $fixes[] = "Create admin user for testing";
        }
    }
    
    // 7. Check file upload directory
    echo "\n7. Checking file upload setup...\n";
    $uploadPath = storage_path('app/public/driver_documents');
    if (is_dir($uploadPath)) {
        echo "   ✓ Upload directory exists: $uploadPath\n";
        if (is_writable($uploadPath)) {
            echo "   ✓ Upload directory is writable\n";
        } else {
            echo "   ✗ Upload directory not writable\n";
            $issues[] = "Upload directory not writable";
            $fixes[] = "Fix directory permissions";
        }
    } else {
        echo "   ✗ Upload directory missing: $uploadPath\n";
        $issues[] = "Upload directory missing";
        $fixes[] = "Create upload directory and run: php artisan storage:link";
    }
    
    // 8. Summary and recommendations
    echo "\n" . str_repeat("=", 50) . "\n";
    echo "ANALYSIS COMPLETE\n";
    echo str_repeat("=", 50) . "\n";
    
    if (empty($issues)) {
        echo "\n✓ ALL CHECKS PASSED!\n";
        echo "The drivers table and system appear to be working correctly.\n\n";
        echo "If you're still having issues with driver creation:\n";
        echo "1. Check the web browser console for JavaScript errors\n";
        echo "2. Check Laravel logs: storage/logs/laravel.log\n";
        echo "3. Test with minimal data (only required fields)\n";
        echo "4. Verify CSRF tokens are working\n";
        echo "5. Check form validation on the frontend\n";
    } else {
        echo "\n⚠ ISSUES FOUND:\n";
        foreach ($issues as $i => $issue) {
            echo "   " . ($i + 1) . ". $issue\n";
        }
        
        echo "\n🔧 RECOMMENDED FIXES:\n";
        $uniqueFixes = array_unique($fixes);
        foreach ($uniqueFixes as $i => $fix) {
            echo "   " . ($i + 1) . ". $fix\n";
        }
    }
    
    echo "\n📝 NEXT STEPS:\n";
    echo "1. Start XAMPP and ensure MySQL is running\n";
    echo "2. Run: php artisan migrate --force\n";
    echo "3. Run: php artisan storage:link\n";
    echo "4. Create an admin user if none exists\n";
    echo "5. Test driver creation via web interface\n";
    echo "6. Check Laravel logs for any errors\n";
    
    echo "\n🌐 WEB TESTING:\n";
    echo "URL: http://localhost/drivelink/public/admin/drivers/create\n";
    echo "Or: http://127.0.0.1/drivelink/public/admin/drivers/create\n";
    echo "Login first at: /admin/login\n";
    
} catch (Exception $e) {
    echo "\n❌ CRITICAL ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "\nThis indicates a fundamental connection or configuration issue.\n";
    echo "Check:\n";
    echo "1. XAMPP MySQL service is running\n";
    echo "2. Database exists: " . env('DB_DATABASE') . "\n";
    echo "3. .env file has correct database credentials\n";
    echo "4. Laravel configuration is valid\n";
}

echo "\n" . str_repeat("=", 50) . "\n";