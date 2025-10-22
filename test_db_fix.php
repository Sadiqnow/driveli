<?php

require_once 'vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Contracts\Console\Kernel;

$app = require_once 'bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

echo "Testing database operations...\n";

// Test creating company
$company = \App\Models\Company::first();
if (!$company) {
    $company = \App\Models\Company::create([
        'name' => 'Test Company',
        'email' => 'test@company.com',
        'phone' => '1234567890',
        'status' => 'active'
    ]);
}
echo "Company created with ID: {$company->id}\n";

// Test creating company request
$request = \App\Models\CompanyRequest::create([
    'company_id' => $company->id,
    'request_id' => 'TEST_REQ_' . rand(100000, 999999),
    'position_title' => 'Test Driver Position',
    'request_type' => 'driver_assignment',
    'description' => 'Test request for background processing',
    'location' => 'Test Location',
    'status' => 'pending',
    'priority' => 'medium',
    'created_by' => 1
]);
echo "Request created with ID: {$request->id}\n";

// Test creating driver
$driver = \App\Models\Drivers::where('status', 'active')->first();
if (!$driver) {
    $driver = \App\Models\Drivers::create([
        'first_name' => 'Test',
        'last_name' => 'Driver',
        'email' => 'test@driver.com',
        'phone' => '1234567890',
        'status' => 'active',
        'is_active' => true,
        'verification_status' => 'verified'
    ]);
}
echo "Driver available with ID: {$driver->id}\n";

// Test job dispatching
$matchData = [
    'match_id' => 'TEST' . rand(100000, 999999),
    'company_request_id' => $request->id,
    'driver_id' => $driver->id,
    'commission_rate' => 12.5,
    'matched_by_admin' => 1,
    'auto_matched' => false,
    'priority' => 'high'
];

echo "Testing job instantiation...\n";
$job = new \App\Jobs\ProcessDriverMatch($matchData);
echo "Job created successfully\n";

echo "Testing synchronous job execution...\n";
try {
    $job->handle();
    echo "Job executed successfully\n";
} catch (Exception $e) {
    echo "Job execution failed: " . $e->getMessage() . "\n";
}

// Clean up
$request->delete();
if ($company->name === 'Test Company') {
    $company->delete();
}
if ($driver->first_name === 'Test') {
    $driver->delete();
}

echo "Test completed successfully!\n";
