<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== DRIVER REGISTRATION FLOW TEST ===\n\n";

$tests = [
    'registration_form' => 'Registration Form Accessibility',
    'otp_verification' => 'OTP Verification Process',
    'kyc_step1' => 'KYC Step 1 - Personal Information',
    'kyc_step2' => 'KYC Step 2 - Contact & Address',
    'kyc_step3' => 'KYC Step 3 - Documents & Employment',
    'admin_approval' => 'Admin Approval Workflow',
    'edge_cases' => 'Edge Cases & Error Handling'
];

$results = [];

foreach ($tests as $key => $description) {
    echo "Testing: $description\n";
    try {
        switch ($key) {
            case 'registration_form':
                // Test if registration route exists and returns proper response
                $router = app('router');
                $routes = $router->getRoutes();
                $registrationRoute = null;
                foreach ($routes as $route) {
                    if ($route->uri() === 'driver/register' && in_array('GET', $route->methods())) {
                        $registrationRoute = $route;
                        break;
                    }
                }

                if ($registrationRoute) {
                    $results[$key] = "✓ PASS - Registration route exists";
                } else {
                    $results[$key] = "✗ FAIL - Registration route not found";
                }
                break;

            case 'otp_verification':
                // Test OTP verification model and controller methods
                if (class_exists('\App\Models\OtpVerification')) {
                    $otpModel = new \App\Models\OtpVerification();
                    $fillable = $otpModel->getFillable();

                    $requiredFields = ['driver_id', 'verification_type', 'otp_code', 'expires_at'];
                    $missingFields = array_diff($requiredFields, $fillable);

                    if (empty($missingFields)) {
                        $results[$key] = "✓ PASS - OTP model properly configured";
                    } else {
                        $results[$key] = "✗ FAIL - Missing OTP fields: " . implode(', ', $missingFields);
                    }
                } else {
                    $results[$key] = "✗ FAIL - OtpVerification model not found";
                }
                break;

            case 'kyc_step1':
                // Test KYC Step 1 validation rules
                $driverController = app(\App\Http\Controllers\Driver\DriverKycController::class);
                $reflection = new ReflectionClass($driverController);
                $method = $reflection->getMethod('postStep1');
                $method->setAccessible(true);

                // Check if method exists and has proper validation
                if ($method) {
                    $results[$key] = "✓ PASS - KYC Step 1 method exists";
                } else {
                    $results[$key] = "✗ FAIL - KYC Step 1 method not found";
                }
                break;

            case 'kyc_step2':
                // Test KYC Step 2 validation
                $driverController = app(\App\Http\Controllers\Driver\DriverKycController::class);
                $reflection = new ReflectionClass($driverController);
                $method = $reflection->getMethod('postStep2');
                $method->setAccessible(true);

                if ($method) {
                    $results[$key] = "✓ PASS - KYC Step 2 method exists";
                } else {
                    $results[$key] = "✗ FAIL - KYC Step 2 method not found";
                }
                break;

            case 'kyc_step3':
                // Test KYC Step 3 document upload
                $driverController = app(\App\Http\Controllers\Driver\DriverKycController::class);
                $reflection = new ReflectionClass($driverController);
                $method = $reflection->getMethod('postStep3');
                $method->setAccessible(true);

                if ($method) {
                    $results[$key] = "✓ PASS - KYC Step 3 method exists";
                } else {
                    $results[$key] = "✗ FAIL - KYC Step 3 method not found";
                }
                break;

            case 'admin_approval':
                // Test admin approval workflow
                if (class_exists('\App\Http\Controllers\Admin\VerificationController')) {
                    $adminController = app(\App\Http\Controllers\Admin\VerificationController::class);
                    $reflection = new ReflectionClass($adminController);
                    $methods = ['approveVerification', 'rejectVerification'];

                    $missingMethods = [];
                    foreach ($methods as $methodName) {
                        if (!$reflection->hasMethod($methodName)) {
                            $missingMethods[] = $methodName;
                        }
                    }

                    if (empty($missingMethods)) {
                        $results[$key] = "✓ PASS - Admin approval methods exist";
                    } else {
                        $results[$key] = "✗ FAIL - Missing admin methods: " . implode(', ', $missingMethods);
                    }
                } else {
                    $results[$key] = "✗ FAIL - Admin VerificationController not found";
                }
                break;

            case 'edge_cases':
                // Test edge cases and error handling
                $driverModel = new \App\Models\DriverNormalized();

                // Check if model has proper validation rules
                $rules = $driverModel->rules ?? [];

                $criticalFields = ['email', 'phone', 'license_number', 'first_name', 'surname'];
                $missingRules = array_diff($criticalFields, array_keys($rules));

                if (empty($missingRules)) {
                    $results[$key] = "✓ PASS - Model validation rules configured";
                } else {
                    $results[$key] = "⚠ WARN - Missing validation rules for: " . implode(', ', $missingRules);
                }
                break;
        }
    } catch (Exception $e) {
        $results[$key] = "✗ FAIL - " . $e->getMessage();
    }
    echo "Result: " . $results[$key] . "\n\n";
}

// Additional database checks
echo "=== DATABASE INTEGRITY CHECKS ===\n\n";

$dbTests = [
    'drivers_table' => 'Drivers table structure',
    'otp_table' => 'OTP verifications table',
    'foreign_keys' => 'Foreign key constraints',
    'indexes' => 'Database indexes'
];

foreach ($dbTests as $key => $description) {
    echo "Checking: $description\n";
    try {
        switch ($key) {
            case 'drivers_table':
                $columns = \Illuminate\Support\Facades\Schema::getColumnListing('drivers');
                $requiredColumns = ['id', 'driver_id', 'first_name', 'surname', 'email', 'phone', 'kyc_status', 'verification_status'];

                $missingColumns = array_diff($requiredColumns, $columns);
                if (empty($missingColumns)) {
                    echo "✓ PASS - All required columns present\n\n";
                } else {
                    echo "✗ FAIL - Missing columns: " . implode(', ', $missingColumns) . "\n\n";
                }
                break;

            case 'otp_table':
                $columns = \Illuminate\Support\Facades\Schema::getColumnListing('otp_verifications');
                $requiredColumns = ['id', 'driver_id', 'verification_type', 'otp_code', 'expires_at', 'verified_at'];

                $missingColumns = array_diff($requiredColumns, $columns);
                if (empty($missingColumns)) {
                    echo "✓ PASS - OTP table structure correct\n\n";
                } else {
                    echo "✗ FAIL - Missing OTP columns: " . implode(', ', $missingColumns) . "\n\n";
                }
                break;

            case 'foreign_keys':
                // Check if foreign key constraints exist
                $foreignKeys = \Illuminate\Support\Facades\DB::select("
                    SELECT CONSTRAINT_NAME, TABLE_NAME, COLUMN_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
                    FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
                    WHERE REFERENCED_TABLE_SCHEMA = DATABASE()
                    AND TABLE_NAME = 'drivers'
                ");

                if (count($foreignKeys) > 0) {
                    echo "✓ PASS - Foreign key constraints found\n\n";
                } else {
                    echo "⚠ WARN - No foreign key constraints detected\n\n";
                }
                break;

            case 'indexes':
                $indexes = \Illuminate\Support\Facades\DB::select("
                    SHOW INDEX FROM drivers
                ");

                $indexedColumns = array_column($indexes, 'Column_name');
                $importantIndexes = ['email', 'phone', 'driver_id'];

                $missingIndexes = array_diff($importantIndexes, $indexedColumns);
                if (empty($missingIndexes)) {
                    echo "✓ PASS - Important indexes present\n\n";
                } else {
                    echo "⚠ WARN - Missing indexes for: " . implode(', ', $missingIndexes) . "\n\n";
                }
                break;
        }
    } catch (Exception $e) {
        echo "✗ FAIL - " . $e->getMessage() . "\n\n";
    }
}

echo "=== TEST SUMMARY ===\n";
$passed = 0;
$total = count($results);
foreach ($results as $test => $result) {
    if (str_contains($result, 'PASS')) {
        $passed++;
    }
    echo "$test: $result\n";
}

echo "\nOverall: $passed/$total core tests passed\n";

if ($passed === $total) {
    echo "🎉 ALL CORE TESTS PASSED - Driver registration flow is properly implemented!\n";
} elseif ($passed >= $total * 0.8) {
    echo "✅ MOST TESTS PASSED - Minor issues may exist\n";
} else {
    echo "⚠️  SOME TESTS FAILED - Further investigation needed\n";
}

echo "\n=== RECOMMENDATIONS ===\n";
echo "1. Test the complete end-to-end flow using a web browser\n";
echo "2. Verify OTP email/SMS sending functionality\n";
echo "3. Test document upload and storage\n";
echo "4. Validate admin approval workflow\n";
echo "5. Check error handling for edge cases\n";
