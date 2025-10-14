<?php

require_once 'vendor/autoload.php';

use App\Models\DriverNormalized;
use App\Models\Company;
use App\Models\AdminUser;
use App\Models\DriverCompanyRelation;
use App\Models\ActivityLog;
use App\Services\EmploymentFeedbackService;
use App\Jobs\SendEmploymentFeedbackRequest;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

echo "=== EMPLOYMENT FEEDBACK SYSTEM COMPREHENSIVE TEST ===\n\n";

// Initialize Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$testResults = [
    'passed' => 0,
    'failed' => 0,
    'tests' => []
];

function test($name, $callback) {
    global $testResults;
    echo "Testing: $name... ";
    try {
        $result = $callback();
        if ($result === true || $result === null) {
            echo "✅ PASSED\n";
            $testResults['passed']++;
            $testResults['tests'][] = ['name' => $name, 'status' => 'passed'];
        } else {
            echo "❌ FAILED: $result\n";
            $testResults['failed']++;
            $testResults['tests'][] = ['name' => $name, 'status' => 'failed', 'error' => $result];
        }
    } catch (Exception $e) {
        echo "❌ FAILED: " . $e->getMessage() . "\n";
        $testResults['failed']++;
        $testResults['tests'][] = ['name' => $name, 'status' => 'failed', 'error' => $e->getMessage()];
    }
}

// Test 1: Database Migration Status
test("Database Migration Status", function() {
    $columns = DB::select("SHOW COLUMNS FROM driver_company_relations");
    $columnNames = array_column($columns, 'Field');

    $requiredColumns = [
        'reason_for_leaving',
        'performance_rating',
        'feedback_notes',
        'feedback_token',
        'feedback_requested_at',
        'feedback_submitted_at',
        'last_reminder_sent_at',
        'feedback_requested_by',
        'is_flagged',
        'flag_reason'
    ];

    foreach ($requiredColumns as $column) {
        if (!in_array($column, $columnNames)) {
            return "Missing column: $column";
        }
    }

    return true;
});

// Test 2: Model Relationships
test("DriverCompanyRelation Model Relationships", function() {
    $relation = new DriverCompanyRelation();

    if (!$relation->driver()) return "Missing driver relationship";
    if (!$relation->company()) return "Missing company relationship";
    if (!$relation->feedbackRequestedBy()) return "Missing feedbackRequestedBy relationship";

    return true;
});

// Test 3: Model Methods
test("DriverCompanyRelation Model Methods", function() {
    $relation = new DriverCompanyRelation();

    if (!method_exists($relation, 'generateFeedbackToken')) return "Missing generateFeedbackToken method";
    if (!method_exists($relation, 'requestFeedback')) return "Missing requestFeedback method";
    if (!method_exists($relation, 'submitFeedback')) return "Missing submitFeedback method";
    if (!method_exists($relation, 'checkAndFlagDriver')) return "Missing checkAndFlagDriver method";

    return true;
});

// Test 4: Model Scopes
test("DriverCompanyRelation Model Scopes", function() {
    if (!method_exists(DriverCompanyRelation::class, 'scopeFlagged')) return "Missing flagged scope";
    if (!method_exists(DriverCompanyRelation::class, 'scopeFeedbackRequested')) return "Missing feedbackRequested scope";
    if (!method_exists(DriverCompanyRelation::class, 'scopeFeedbackSubmitted')) return "Missing feedbackSubmitted scope";
    if (!method_exists(DriverCompanyRelation::class, 'scopePendingFeedback')) return "Missing pendingFeedback scope";

    return true;
});

// Test 5: Service Instantiation
test("EmploymentFeedbackService Instantiation", function() {
    $service = app(EmploymentFeedbackService::class);
    return $service instanceof EmploymentFeedbackService;
});

// Test 6: Service Methods
test("EmploymentFeedbackService Methods", function() {
    $service = app(EmploymentFeedbackService::class);

    $methods = [
        'requestFeedback',
        'submitFeedback',
        'validateToken',
        'sendFeedbackRequestNotification',
        'getFeedbackStats',
        'getFlaggedDrivers',
        'bulkRequestFeedback'
    ];

    foreach ($methods as $method) {
        if (!method_exists($service, $method)) {
            return "Missing method: $method";
        }
    }

    return true;
});

// Test 7: Create Test Data
$testData = null;
test("Create Test Data", function() use (&$testData) {
    try {
        // Create test driver manually
        $driver = DriverNormalized::create([
            'driver_id' => 'DRV-' . time(),
            'first_name' => 'Test',
            'last_name' => 'Driver',
            'email' => 'test.driver' . time() . '@example.com',
            'phone' => '08012345678',
            'status' => 'active',
            'verification_status' => 'verified',
            'password' => bcrypt('password123'),
            'registered_at' => now(),
        ]);

        // Create test company manually
        $company = Company::create([
            'company_id' => 'COMP-' . time(),
            'name' => 'Test Transport Company ' . time(),
            'email' => 'hr' . time() . '@testcompany.com',
            'contact_person' => 'HR Manager',
            'contact_person_name' => 'HR Manager',
            'contact_person_email' => 'hr' . time() . '@testcompany.com',
            'contact_person_phone' => '08098765432',
            'phone' => '08098765432',
            'address' => '123 Test Street, Lagos',
            'state' => 'Lagos',
            'status' => 'active',
        ]);

        // Create test admin manually
        $admin = AdminUser::create([
            'name' => 'Test Admin',
            'email' => 'admin' . time() . '@test.com',
            'password' => bcrypt('password123'),
            'status' => 'active',
        ]);

        // Create driver-company relation
        $relation = DriverCompanyRelation::create([
            'driver_id' => $driver->id,
            'company_id' => $company->id,
            'status' => 'active',
            'employment_start_date' => now()->subYears(2),
            'employment_end_date' => now()->subMonths(6),
        ]);

        $testData = [
            'driver' => $driver,
            'company' => $company,
            'admin' => $admin,
            'relation' => $relation
        ];

        return true;
    } catch (Exception $e) {
        return "Failed to create test data: " . $e->getMessage();
    }
});

// Test 8: Feedback Request Creation
test("Feedback Request Creation", function() use ($testData) {
    if (!$testData) return "Test data not available";

    $service = app(EmploymentFeedbackService::class);
    $result = $service->requestFeedback($testData['relation'], $testData['admin']);

    if (!$result) return "Feedback request failed";

    // Refresh relation
    $testData['relation']->refresh();

    if (!$testData['relation']->feedback_token) return "Token not generated";
    if (!$testData['relation']->feedback_requested_at) return "Request timestamp not set";
    if ($testData['relation']->feedback_requested_by !== $testData['admin']->id) return "Admin ID not set";

    return true;
});

// Test 9: Token Validation
test("Token Validation", function() use ($testData) {
    if (!$testData) return "Test data not available";

    $service = app(EmploymentFeedbackService::class);
    $validatedRelation = $service->validateToken($testData['relation']->feedback_token);

    if (!$validatedRelation) return "Token validation failed";
    if ($validatedRelation->id !== $testData['relation']->id) return "Wrong relation returned";

    return true;
});

// Test 10: Feedback Submission
test("Feedback Submission", function() use ($testData) {
    if (!$testData) return "Test data not available";

    $service = app(EmploymentFeedbackService::class);

    $feedbackData = [
        'employment_start_date' => '2022-01-15',
        'employment_end_date' => '2023-06-30',
        'performance_rating' => 'good',
        'reason_for_leaving' => 'Career advancement opportunity',
        'feedback_notes' => 'Reliable driver with good customer service skills.'
    ];

    $result = $service->submitFeedback($testData['relation']->feedback_token, $feedbackData);

    if (!$result) return "Feedback submission failed";

    // Refresh relation
    $testData['relation']->refresh();

    if (!$testData['relation']->feedback_submitted_at) return "Submission timestamp not set";
    if ($testData['relation']->performance_rating !== 'good') return "Performance rating not saved";
    if ($testData['relation']->reason_for_leaving !== 'Career advancement opportunity') return "Reason not saved";

    return true;
});

// Test 11: Auto-Flagging Logic
test("Auto-Flagging Logic", function() use ($testData) {
    if (!$testData) return "Test data not available";

    // Create another company for this test
    $company2 = Company::create([
        'company_id' => 'COMP2-' . time(),
        'name' => 'Test Transport Company 2 ' . time(),
        'email' => 'hr2' . time() . '@testcompany.com',
        'contact_person' => 'HR Manager',
        'contact_person_name' => 'HR Manager',
        'contact_person_email' => 'hr2' . time() . '@testcompany.com',
        'contact_person_phone' => '08098765433',
        'phone' => '08098765433',
        'address' => '124 Test Street, Lagos',
        'state' => 'Lagos',
        'status' => 'active',
    ]);

    // Test flagging for poor performance
    $relation2 = DriverCompanyRelation::create([
        'driver_id' => $testData['driver']->id,
        'company_id' => $company2->id,
        'status' => 'active',
        'employment_start_date' => now()->subYears(1),
        'employment_end_date' => now()->subMonths(3),
    ]);

    $service = app(EmploymentFeedbackService::class);

    // Request feedback
    $service->requestFeedback($relation2, $testData['admin']);

    // Submit poor feedback
    $poorFeedback = [
        'performance_rating' => 'very_poor',
        'reason_for_leaving' => 'Terminated for misconduct',
        'feedback_notes' => 'Driver had multiple accidents and safety violations.'
    ];

    $service->submitFeedback($relation2->feedback_token, $poorFeedback);

    $relation2->refresh();

    if (!$relation2->is_flagged) return "Driver not flagged for poor performance";
    if (strpos($relation2->flag_reason, 'Poor performance rating') === false) return "Flag reason incorrect";

    // Clean up
    $relation2->delete();
    $company2->delete();

    return true;
});

// Test 12: Statistics Generation
test("Statistics Generation", function() {
    $service = app(EmploymentFeedbackService::class);
    $stats = $service->getFeedbackStats();

    $requiredKeys = [
        'total_requested',
        'total_submitted',
        'total_pending',
        'total_flagged',
        'response_rate'
    ];

    foreach ($requiredKeys as $key) {
        if (!array_key_exists($key, $stats)) {
            return "Missing stats key: $key";
        }
    }

    return true;
});

// Test 13: Flagged Drivers Query
test("Flagged Drivers Query", function() {
    $service = app(EmploymentFeedbackService::class);
    $flaggedDrivers = $service->getFlaggedDrivers();

    if (!is_iterable($flaggedDrivers)) return "Flagged drivers query failed";

    return true;
});

// Test 14: Bulk Feedback Request
test("Bulk Feedback Request", function() use ($testData) {
    if (!$testData) return "Test data not available";

    // Create another company for this test
    $company3 = Company::create([
        'company_id' => 'COMP3-' . time(),
        'name' => 'Test Transport Company 3 ' . time(),
        'email' => 'hr3' . time() . '@testcompany.com',
        'contact_person' => 'HR Manager',
        'contact_person_name' => 'HR Manager',
        'contact_person_email' => 'hr3' . time() . '@testcompany.com',
        'contact_person_phone' => '08098765434',
        'phone' => '08098765434',
        'address' => '125 Test Street, Lagos',
        'state' => 'Lagos',
        'status' => 'active',
    ]);

    // Create another relation
    $relation3 = DriverCompanyRelation::create([
        'driver_id' => $testData['driver']->id,
        'company_id' => $company3->id,
        'status' => 'active',
        'employment_start_date' => now()->subYears(3),
        'employment_end_date' => now()->subMonths(12),
    ]);

    $service = app(EmploymentFeedbackService::class);
    $result = $service->bulkRequestFeedback([$testData['relation']->id, $relation3->id], $testData['admin']);

    if (!isset($result['successful'])) return "Bulk request failed";
    if ($result['successful'] < 1) return "No successful requests";

    // Clean up
    $relation3->delete();
    $company3->delete();

    return true;
});

// Test 15: Invalid Token Handling
test("Invalid Token Handling", function() {
    $service = app(EmploymentFeedbackService::class);
    $result = $service->submitFeedback('invalid-token-123', []);

    if ($result !== null) return "Invalid token should return null";

    return true;
});

// Test 16: Activity Logging
test("Activity Logging", function() use ($testData) {
    if (!$testData) return "Test data not available";

    $logs = ActivityLog::where('user_type', AdminUser::class)
                      ->where('user_id', $testData['admin']->id)
                      ->where('action', 'feedback_requested')
                      ->get();

    if ($logs->isEmpty()) return "No activity logs found for feedback requests";

    return true;
});

// Test 17: Email Template Existence
test("Email Template Existence", function() {
    $templatePath = resource_path('views/emails/employment-feedback-request.blade.php');
    if (!file_exists($templatePath)) return "Email template not found";

    return true;
});

// Test 18: Form View Existence
test("Form View Existence", function() {
    $formPath = resource_path('views/employment-feedback/form.blade.php');
    if (!file_exists($formPath)) return "Form view not found";

    return true;
});

// Test 19: Success View Existence
test("Success View Existence", function() {
    $successPath = resource_path('views/employment-feedback/success.blade.php');
    if (!file_exists($successPath)) return "Success view not found";

    return true;
});

// Test 20: Job Class Existence
test("Job Class Existence", function() {
    if (!class_exists(SendEmploymentFeedbackRequest::class)) return "Job class not found";

    $job = new SendEmploymentFeedbackRequest(1, 1);
    if (!$job instanceof SendEmploymentFeedbackRequest) return "Job instantiation failed";

    return true;
});

// Test 21: Controller Existence
test("Controller Existence", function() {
    $controllerClass = 'App\Http\Controllers\EmploymentFeedbackController';
    if (!class_exists($controllerClass)) return "Controller class not found";

    return true;
});

// Test 22: Request Class Existence
test("Request Class Existence", function() {
    $requestClass = 'App\Http\Requests\StoreEmploymentFeedbackRequest';
    if (!class_exists($requestClass)) return "Request class not found";

    return true;
});

// Test 23: Reminder Command Existence
test("Reminder Command Existence", function() {
    $commandClass = 'App\Console\Commands\SendFeedbackReminders';
    if (!class_exists($commandClass)) return "Command class not found";

    return true;
});

// Test 24: Feedback Analytics Controller Existence
test("Feedback Analytics Controller Existence", function() {
    $controllerClass = 'App\Http\Controllers\FeedbackAnalyticsController';
    if (!class_exists($controllerClass)) return "Analytics controller class not found";

    return true;
});

// Test 25: Reminder Job Existence
test("Reminder Job Existence", function() {
    $jobClass = 'App\Jobs\SendFeedbackReminder';
    if (!class_exists($jobClass)) return "Reminder job class not found";

    return true;
});

// Clean up test data
test("Clean Up Test Data", function() use ($testData) {
    if ($testData) {
        try {
            DriverCompanyRelation::where('driver_id', $testData['driver']->id)->delete();
            $testData['driver']->delete();
            $testData['company']->delete();
            $testData['admin']->delete();
        } catch (Exception $e) {
            return "Cleanup failed: " . $e->getMessage();
        }
    }

    return true;
});

// Summary
echo "\n=== TEST SUMMARY ===\n";
echo "Total Tests: " . ($testResults['passed'] + $testResults['failed']) . "\n";
echo "Passed: {$testResults['passed']}\n";
echo "Failed: {$testResults['failed']}\n";

if ($testResults['failed'] > 0) {
    echo "\nFailed Tests:\n";
    foreach ($testResults['tests'] as $test) {
        if ($test['status'] === 'failed') {
            echo "- {$test['name']}: {$test['error']}\n";
        }
    }
}

$successRate = ($testResults['passed'] / ($testResults['passed'] + $testResults['failed'])) * 100;
echo "\nSuccess Rate: " . round($successRate, 2) . "%\n";

if ($successRate >= 95) {
    echo "🎉 SYSTEM READY FOR PRODUCTION!\n";
} elseif ($successRate >= 80) {
    echo "⚠️  MINOR ISSUES DETECTED - REVIEW FAILED TESTS\n";
} else {
    echo "❌ CRITICAL ISSUES DETECTED - REQUIRES FIXES\n";
}
