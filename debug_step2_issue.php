<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

echo "=== Debug KYC Step 2 Issue ===\n\n";

// Check if the table exists
if (!Schema::hasTable('drivers')) {
    echo "❌ drivers table does not exist!\n";
    exit(1);
}

echo "✅ drivers table exists\n\n";

// Get all columns in the table
$columns = Schema::getColumnListing('drivers');
echo "Total columns in drivers: " . count($columns) . "\n\n";

// Fields used in KYC Step 2 controller
$step2Fields = [
    'residential_address',
    'residence_state_id', 
    'residence_lga_id',
    'city',
    'postal_code',
    'license_class',
    'license_issue_date',
    'license_expiry_date', 
    'years_of_experience',
    'previous_company',
    'bank_id',
    'account_number',
    'account_name',
    'bvn',
    'kyc_step',
    'kyc_step_2_completed_at',
    'kyc_last_activity_at'
];

echo "=== Checking Step 2 Required Fields ===\n";
$missingFields = [];
foreach ($step2Fields as $field) {
    if (in_array($field, $columns)) {
        echo "✅ $field - EXISTS\n";
    } else {
        echo "❌ $field - MISSING\n";
        $missingFields[] = $field;
    }
}

if (!empty($missingFields)) {
    echo "\n🚨 MISSING FIELDS FOUND:\n";
    foreach ($missingFields as $field) {
        echo "  - $field\n";
    }
    
    echo "\n=== SQL to Add Missing Fields ===\n";
    echo "ALTER TABLE drivers\n";
    foreach ($missingFields as $field) {
        switch ($field) {
            case 'residential_address':
                echo "ADD COLUMN $field TEXT,\n";
                break;
            case 'residence_state_id':
            case 'residence_lga_id':
            case 'bank_id':
                echo "ADD COLUMN $field BIGINT UNSIGNED,\n";
                break;
            case 'city':
            case 'postal_code':
            case 'license_class':
            case 'previous_company':
            case 'account_name':
                echo "ADD COLUMN $field VARCHAR(255),\n";
                break;
            case 'license_issue_date':
            case 'license_expiry_date':
            case 'kyc_step_2_completed_at':
            case 'kyc_last_activity_at':
                echo "ADD COLUMN $field TIMESTAMP NULL,\n";
                break;
            case 'years_of_experience':
                echo "ADD COLUMN $field INTEGER,\n";
                break;
            case 'account_number':
                echo "ADD COLUMN $field VARCHAR(20),\n";
                break;
            case 'bvn':
                echo "ADD COLUMN $field VARCHAR(11),\n";
                break;
            case 'kyc_step':
                echo "ADD COLUMN $field INTEGER DEFAULT 0,\n";
                break;
            default:
                echo "ADD COLUMN $field VARCHAR(255),\n";
        }
    }
    echo ";\n";
} else {
    echo "\n✅ All required fields exist!\n";
}

// Check for any sample driver data
try {
    $driverCount = DB::table('drivers')->count();
    echo "\nDriver count in table: $driverCount\n";
    
    if ($driverCount > 0) {
        $sampleDriver = DB::table('drivers')->first();
        echo "Sample driver ID: " . ($sampleDriver->id ?? 'N/A') . "\n";
        echo "Sample driver email: " . ($sampleDriver->email ?? 'N/A') . "\n";
        echo "Sample driver kyc_status: " . ($sampleDriver->kyc_status ?? 'N/A') . "\n";
        echo "Sample driver kyc_step: " . ($sampleDriver->kyc_step ?? 'N/A') . "\n";
    }
} catch (Exception $e) {
    echo "\n❌ Error checking driver data: " . $e->getMessage() . "\n";
}

echo "\n=== Debug Complete ===\n";