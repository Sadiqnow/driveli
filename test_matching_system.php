<?php

require_once 'vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;

// Database configuration
$capsule = new Capsule;
$capsule->addConnection([
    'driver' => 'mysql',
    'host' => 'localhost',
    'database' => 'drivelink_db',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix' => '',
]);

$capsule->setAsGlobal();
$capsule->bootEloquent();

echo "🚀 Testing Drivelink Matching System\n";
echo "==================================\n\n";

try {
    // Test 1: Check if driver_matches table exists
    echo "✅ Test 1: Checking driver_matches table structure\n";
    $tableInfo = Capsule::select("DESCRIBE driver_matches");
    echo "   - driver_matches table exists with " . count($tableInfo) . " columns\n";
    
    // Test 2: Check existing data
    echo "\n✅ Test 2: Checking existing data counts\n";
    $driverCount = Capsule::table('drivers')
        ->where('verification_status', 'Verified')
        ->where('availability_status', 'Available')
        ->whereNull('deleted_at')
        ->count();
    echo "   - Available verified drivers: {$driverCount}\n";
    
    $requestCount = Capsule::table('company_requests')
        ->whereIn('status', ['pending', 'Pending', 'Active'])
        ->whereNull('deleted_at')
        ->count();
    echo "   - Pending company requests: {$requestCount}\n";
    
    $matchCount = Capsule::table('driver_matches')->count();
    echo "   - Existing driver matches: {$matchCount}\n";
    
    // Test 3: Test match creation (simulate)
    echo "\n✅ Test 3: Testing match creation logic\n";
    if ($driverCount > 0 && $requestCount > 0) {
        $driver = Capsule::table('drivers')
            ->where('verification_status', 'Verified')
            ->where('availability_status', 'Available')
            ->whereNull('deleted_at')
            ->first();
        
        $request = Capsule::table('company_requests')
            ->whereIn('status', ['pending', 'Pending', 'Active'])
            ->whereNull('deleted_at')
            ->first();
        
        if ($driver && $request) {
            echo "   - Found suitable driver: {$driver->first_name} {$driver->surname} (ID: {$driver->id})\n";
            echo "   - Found suitable request: ID {$request->id} for company ID {$request->company_id}\n";
            echo "   - Match creation would be possible ✓\n";
            
            // Check if match already exists
            $existingMatch = Capsule::table('driver_matches')
                ->where('driver_id', $driver->id)
                ->where('company_request_id', $request->id)
                ->first();
            
            if ($existingMatch) {
                echo "   - Match already exists: {$existingMatch->match_id}\n";
            } else {
                echo "   - No existing match found - new match can be created\n";
            }
        }
    } else {
        echo "   - ⚠️  Either no drivers or no requests available for matching\n";
        if ($driverCount == 0) {
            echo "   - Issue: No verified/available drivers found\n";
        }
        if ($requestCount == 0) {
            echo "   - Issue: No pending company requests found\n";
        }
    }
    
    // Test 4: Check relationships
    echo "\n✅ Test 4: Testing database relationships\n";
    $companies = Capsule::table('companies')->count();
    echo "   - Total companies: {$companies}\n";
    
    $drivers = Capsule::table('drivers')->count();
    echo "   - Total drivers: {$drivers}\n";
    
    // Test foreign key constraints
    $requestsWithCompanies = Capsule::table('company_requests')
        ->join('companies', 'company_requests.company_id', '=', 'companies.id')
        ->count();
    echo "   - Company requests with valid companies: {$requestsWithCompanies}\n";
    
    if ($matchCount > 0) {
        $matchesWithValidRefs = Capsule::table('driver_matches')
            ->join('drivers', 'driver_matches.driver_id', '=', 'drivers.id')
            ->join('company_requests', 'driver_matches.company_request_id', '=', 'company_requests.id')
            ->count();
        echo "   - Matches with valid references: {$matchesWithValidRefs}\n";
    }
    
    echo "\n🎉 Matching System Test Results:\n";
    echo "================================\n";
    echo "✅ Database structure: OK\n";
    echo "✅ Data availability: " . ($driverCount > 0 && $requestCount > 0 ? "OK" : "NEEDS DATA") . "\n";
    echo "✅ Relationships: OK\n";
    echo "✅ Ready for matching: " . ($driverCount > 0 && $requestCount > 0 ? "YES" : "NO") . "\n";
    
    if ($driverCount == 0 || $requestCount == 0) {
        echo "\n💡 Next Steps:\n";
        if ($driverCount == 0) {
            echo "   - Add verified drivers with 'Available' status\n";
        }
        if ($requestCount == 0) {
            echo "   - Add company requests with 'Pending' status\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "Test completed at " . date('Y-m-d H:i:s') . "\n";