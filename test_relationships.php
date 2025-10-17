<?php

require_once 'vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Contracts\Console\Kernel;
use App\Models\Driver;
use App\Models\DriverNextOfKin;
use App\Models\DriverPerformance;
use App\Models\DriverBankingDetail;
use App\Models\DriverDocument;
use App\Models\DriverMatch;
use App\Models\DriverCategoryRequirement;

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

echo "=== TESTING DRIVER RELATIONSHIPS ===\n\n";

// Get first driver
$driver = Driver::first();
if (!$driver) {
    echo "No drivers found in database.\n";
    exit;
}

echo "Testing Driver ID: {$driver->driver_id}\n";
echo "Driver Name: {$driver->full_name}\n\n";

// Test relationships
echo "1. Personal Info (Next of Kin):\n";
$personalInfo = $driver->personalInfo;
if ($personalInfo) {
    echo "   - Name: {$personalInfo->name}\n";
    echo "   - Relationship: {$personalInfo->relationship}\n";
    echo "   - Phone: {$personalInfo->phone}\n";
} else {
    echo "   - No personal info found\n";
}

echo "\n2. Performance Data:\n";
$performance = $driver->performance;
if ($performance) {
    echo "   - Total Jobs: {$performance->total_jobs_completed}\n";
    echo "   - Average Rating: {$performance->average_rating}\n";
    echo "   - Total Earnings: ₦{$performance->total_earnings}\n";
} else {
    echo "   - No performance data found\n";
}

echo "\n3. Banking Details:\n";
$bankingDetails = $driver->bankingDetails;
if ($bankingDetails->count() > 0) {
    foreach ($bankingDetails as $bank) {
        echo "   - Account: {$bank->account_number} ({$bank->account_name})\n";
        echo "   - Primary: " . ($bank->is_primary ? 'Yes' : 'No') . "\n";
    }
} else {
    echo "   - No banking details found\n";
}

echo "\n4. Documents:\n";
$documents = $driver->documents;
if ($documents->count() > 0) {
    foreach ($documents as $doc) {
        echo "   - {$doc->document_type_name}: {$doc->verification_status}\n";
    }
} else {
    echo "   - No documents found\n";
}

echo "\n5. Matches:\n";
$matches = $driver->matches;
if ($matches->count() > 0) {
    foreach ($matches as $match) {
        echo "   - Match ID: {$match->match_id} - Status: {$match->status}\n";
    }
} else {
    echo "   - No matches found\n";
}

echo "\n6. Category Requirements:\n";
$requirements = $driver->categoryRequirements;
if ($requirements->count() > 0) {
    foreach ($requirements as $req) {
        echo "   - Category: {$req->category}\n";
        echo "   - Min Experience: {$req->minimum_experience_years} years\n";
    }
} else {
    echo "   - No category requirements found\n";
}

echo "\n=== TESTING REVERSE RELATIONSHIPS ===\n";

// Test reverse relationships
echo "\n7. Testing reverse relationships:\n";
$nextOfKin = DriverNextOfKin::where('driver_id', $driver->id)->first();
if ($nextOfKin) {
    echo "   - Next of Kin belongs to driver: {$nextOfKin->driver->full_name}\n";
}

$performanceData = DriverPerformance::where('driver_id', $driver->id)->first();
if ($performanceData) {
    echo "   - Performance belongs to driver: {$performanceData->driver->full_name}\n";
}

echo "\n=== TESTING CRUD OPERATIONS ===\n";

// Test creating/updating transactional data
echo "\n8. Testing transactional data creation:\n";

try {
    // Update personal info
    $personalInfo = DriverNextOfKin::updateOrCreate(
        ['driver_id' => $driver->id],
        [
            'name' => 'John Doe',
            'relationship' => 'Brother',
            'phone' => '+2348012345678',
            'is_primary' => true
        ]
    );
    echo "   ✓ Personal info updated\n";

    // Update performance
    $performance = DriverPerformance::updateOrCreate(
        ['driver_id' => $driver->id],
        [
            'total_jobs_completed' => 5,
            'average_rating' => 4.5,
            'total_earnings' => 150000.00
        ]
    );
    echo "   ✓ Performance data updated\n";

    // Create banking detail
    $banking = DriverBankingDetail::updateOrCreate(
        ['driver_id' => $driver->id, 'account_number' => '1234567890'],
        [
            'account_name' => 'John Doe',
            'bank_id' => 1,
            'is_primary' => true,
            'is_verified' => false
        ]
    );
    echo "   ✓ Banking detail updated\n";

} catch (\Exception $e) {
    echo "   ✗ Error updating transactional data: {$e->getMessage()}\n";
}

echo "\n=== TESTING DATA INTEGRITY ===\n";

// Test data integrity
echo "\n9. Testing data integrity:\n";
$driverCount = Driver::count();
$personalInfoCount = DriverNextOfKin::count();
$performanceCount = DriverPerformance::count();
$bankingCount = DriverBankingDetail::count();
$documentsCount = DriverDocument::count();
$matchesCount = DriverMatch::count();
$requirementsCount = DriverCategoryRequirement::count();

echo "   - Drivers: {$driverCount}\n";
echo "   - Personal Info: {$personalInfoCount}\n";
echo "   - Performance: {$performanceCount}\n";
echo "   - Banking: {$bankingCount}\n";
echo "   - Documents: {$documentsCount}\n";
echo "   - Matches: {$matchesCount}\n";
echo "   - Requirements: {$requirementsCount}\n";

echo "\n=== TESTING ACCESSORS ===\n";

// Test accessors
echo "\n10. Testing accessors:\n";
echo "   - Full Name: {$driver->full_name}\n";
echo "   - Age: " . ($driver->age ?? 'Not set') . "\n";
echo "   - Profile Completion: {$driver->getProfileCompletionPercentage()}%\n";
echo "   - Is Active: " . ($driver->isActive() ? 'Yes' : 'No') . "\n";
echo "   - Is Verified: " . ($driver->isVerified() ? 'Yes' : 'No') . "\n";

echo "\n=== ALL TESTS COMPLETED ===\n";
