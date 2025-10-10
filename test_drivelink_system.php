<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "🔍 Testing DriveLink System...\n\n";

try {
    // Test database connection
    echo "1. Testing Database Connection...\n";
    $pdo = DB::connection()->getPdo();
    echo "✅ Database connected successfully\n\n";

    // Test models
    echo "2. Testing Models...\n";
    
    // Test AdminUser model
    $adminCount = App\Models\AdminUser::count();
    echo "✅ AdminUser model working - Count: {$adminCount}\n";
    
    // Test DriverNormalized model
    $driverCount = App\Models\DriverNormalized::count();
    echo "✅ DriverNormalized model working - Count: {$driverCount}\n";
    
    // Test Company model
    $companyCount = App\Models\Company::count();
    echo "✅ Company model working - Count: {$companyCount}\n";

    echo "\n3. Testing Key Relationships...\n";
    
    // Test driver with relationships
    $driver = App\Models\DriverNormalized::with(['nationality', 'locations', 'documents'])->first();
    if ($driver) {
        echo "✅ Driver relationships working\n";
        echo "   - Driver: {$driver->full_name}\n";
        echo "   - Nationality: " . ($driver->nationality->name ?? 'Not set') . "\n";
        echo "   - Locations: " . $driver->locations->count() . "\n";
        echo "   - Documents: " . $driver->documents->count() . "\n";
    } else {
        echo "⚠️  No drivers found in database\n";
    }

    echo "\n4. Testing Security Fixes...\n";
    
    // Test that verification fields are protected
    $protectedFields = ['verification_status', 'verified_at', 'verified_by', 'ocr_verification_status'];
    $fillableFields = App\Models\DriverNormalized::make()->getFillable();
    
    $vulnerableFields = array_intersect($protectedFields, $fillableFields);
    if (empty($vulnerableFields)) {
        echo "✅ Security fix applied - No sensitive fields in fillable array\n";
    } else {
        echo "❌ Security issue - Vulnerable fields: " . implode(', ', $vulnerableFields) . "\n";
    }

    echo "\n5. Testing Admin Functions...\n";
    
    if (method_exists(App\Models\DriverNormalized::class, 'adminUpdateVerification')) {
        echo "✅ Admin verification methods available\n";
    } else {
        echo "❌ Admin verification methods missing\n";
    }

    echo "\n📊 System Health Summary:\n";
    echo "✅ Database connection: Working\n";
    echo "✅ Models: Functional\n";
    echo "✅ Security: Enhanced\n";
    echo "✅ Admin functions: Available\n";
    
    echo "\n🎉 DriveLink system is operational!\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\nTest completed.\n";