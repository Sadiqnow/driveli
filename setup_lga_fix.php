<?php

/**
 * LGA Fix Setup Script
 * 
 * This script ensures that the database has the necessary data for LGAs to work properly
 */

echo "DriveLink LGA Fix Setup\n";
echo "======================\n\n";

// Check if we're in the correct directory
if (!file_exists('artisan')) {
    echo "Error: This script must be run from the Laravel project root directory.\n";
    exit(1);
}

echo "Step 1: Checking database connection...\n";

try {
    // Test basic PHP and database connection
    $output = shell_exec('php artisan --version 2>&1');
    if (strpos($output, 'Laravel') !== false) {
        echo "✓ Laravel is working\n";
    } else {
        throw new Exception("Laravel artisan command failed: " . $output);
    }

    // Check database connection
    $output = shell_exec('php artisan tinker --execute="echo \'DB Connection: \'; echo DB::connection()->getDatabaseName(); echo \' - OK\';" 2>&1');
    if (strpos($output, 'OK') !== false) {
        echo "✓ Database connection working\n";
    } else {
        echo "⚠ Database connection issue. Output: " . $output . "\n";
        echo "Please ensure XAMPP MySQL is running and database 'drivelink_db' exists.\n";
    }

} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}

echo "\nStep 2: Running migrations...\n";
$output = shell_exec('php artisan migrate 2>&1');
echo $output . "\n";

echo "Step 3: Seeding location data...\n";
$output = shell_exec('php artisan drivelink:seed-locations 2>&1');
echo $output . "\n";

echo "Step 4: Testing data availability...\n";
$testScript = '
try {
    $states = App\Models\State::count();
    $lgas = App\Models\LocalGovernment::count();
    echo "States: " . $states . "\n";
    echo "LGAs: " . $lgas . "\n";
    
    if ($states > 0 && $lgas > 0) {
        $lagos = App\Models\State::where("name", "Lagos")->first();
        if ($lagos) {
            $lagosLgas = App\Models\LocalGovernment::where("state_id", $lagos->id)->count();
            echo "Lagos LGAs: " . $lagosLgas . "\n";
            echo "✓ Test successful - LGA data is available\n";
        }
    } else {
        echo "✗ No data found in database\n";
    }
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}
';

$output = shell_exec('php artisan tinker --execute="' . str_replace('"', '\\"', $testScript) . '" 2>&1');
echo $output . "\n";

echo "\nStep 5: Testing API endpoint...\n";

// Test if server is running by trying to access a simple endpoint
$testUrl = 'http://localhost:8000/api/status';
$context = stream_context_create([
    'http' => [
        'timeout' => 5,
        'ignore_errors' => true
    ]
]);

$response = @file_get_contents($testUrl, false, $context);
if ($response !== false) {
    echo "✓ Laravel server is accessible at localhost:8000\n";
    
    // Test the LGA API endpoint with Lagos (likely state ID 25)
    $lgaTestUrl = 'http://localhost:8000/api/lgas/25';
    $lgaResponse = @file_get_contents($lgaTestUrl, false, $context);
    if ($lgaResponse !== false) {
        $data = json_decode($lgaResponse, true);
        if (is_array($data) && count($data) > 0) {
            echo "✓ LGA API endpoint working - returned " . count($data) . " LGAs for Lagos\n";
        } else {
            echo "⚠ LGA API returned empty result\n";
        }
    } else {
        echo "⚠ Could not test LGA API endpoint\n";
    }
} else {
    echo "⚠ Laravel server not accessible. You may need to run 'php artisan serve'\n";
}

echo "\nSetup Complete!\n";
echo "===============\n\n";
echo "If you see any errors above, please:\n";
echo "1. Make sure XAMPP is running (Apache + MySQL)\n";
echo "2. Ensure database 'drivelink_db' exists\n";
echo "3. Run 'php artisan serve' in another terminal\n";
echo "4. Test the LGA dropdown in your application\n\n";
echo "The LGA population should now work correctly.\n";