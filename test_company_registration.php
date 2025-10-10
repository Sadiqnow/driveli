<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Test company creation directly
use App\Models\Company;
use Illuminate\Support\Facades\Log;

echo "Testing company registration...\n";

try {
    // Test data
    $testData = [
        'company_id' => 'TEST001',
        'name' => 'Test Logistics Company',
        'registration_number' => 'RC123456',
        'tax_id' => 'TIN123456',
        'address' => '123 Test Street, Test City',
        'state' => 'Lagos',
        'postal_code' => '100001',
        'contact_person_name' => 'John Doe',
        'contact_person_title' => 'CEO',
        'contact_person_phone' => '+2341234567890',
        'contact_person_email' => 'john@testlogistics.com',
        'email' => 'info@testlogistics.com',
        'phone' => '+2341234567890',
        'website' => 'https://testlogistics.com',
        'industry' => 'Logistics',
        'description' => 'Test logistics company',
        'status' => 'Active',
        'verification_status' => 'Pending',
    ];

    // Create company
    $company = Company::create($testData);
    
    echo "Company created successfully!\n";
    echo "Company ID: " . $company->company_id . "\n";
    echo "Name: " . $company->name . "\n";
    echo "Email: " . $company->email . "\n";
    
    // Clean up - delete the test company
    $company->delete();
    echo "Test company cleaned up.\n";
    
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "Test completed.\n";