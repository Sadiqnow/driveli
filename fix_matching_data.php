<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\CompanyRequest;
use App\Models\Company;
use App\Models\DriverNormalized;

echo "🔧 Fixing Manual Matching Data\n";
echo "==============================\n\n";

try {
    // Check current state
    $totalCompanies = Company::count();
    $totalRequests = CompanyRequest::count();
    $totalDrivers = DriverNormalized::count();
    
    echo "Current data:\n";
    echo "  - Companies: $totalCompanies\n";
    echo "  - Company Requests: $totalRequests\n";
    echo "  - Drivers: $totalDrivers\n\n";
    
    // Create companies if none exist
    if ($totalCompanies == 0) {
        echo "Creating test companies...\n";
        
        Company::create([
            'name' => 'ABC Logistics Ltd',
            'email' => 'admin@abclogistics.com',
            'phone' => '08012345678',
            'address' => '123 Business Street, Victoria Island, Lagos',
            'status' => 'active'
        ]);
        
        Company::create([
            'name' => 'XYZ Transport Services',
            'email' => 'info@xyztransport.com',
            'phone' => '08087654321',
            'address' => '456 Commerce Avenue, Wuse II, Abuja',
            'status' => 'active'
        ]);
        
        echo "✅ Created companies\n\n";
    }
    
    // Create requests if none exist
    if ($totalRequests == 0) {
        echo "Creating test company requests...\n";
        
        $companies = Company::all();
        
        $requests = [
            [
                'company_id' => $companies->first()->id,
                'status' => 'pending',
                'description' => 'Need experienced driver for daily logistics operations',
                'location' => 'Lagos State',
                'job_type' => 'Full-time Delivery',
                'requirements' => 'Valid license, 2+ years experience',
                'salary_range' => '150,000 - 200,000'
            ],
            [
                'company_id' => $companies->first()->id,
                'status' => 'Active',
                'description' => 'Executive driver needed for CEO transport',
                'location' => 'Lagos State',
                'job_type' => 'Executive Transport',
                'requirements' => 'Professional appearance, excellent driving skills',
                'salary_range' => '250,000 - 300,000'
            ]
        ];
        
        if ($companies->count() > 1) {
            $requests[] = [
                'company_id' => $companies->get(1)->id,
                'status' => 'pending',
                'description' => 'Part-time driver for weekend deliveries',
                'location' => 'Abuja',
                'job_type' => 'Part-time',
                'requirements' => 'Flexible schedule',
                'salary_range' => '80,000 - 120,000'
            ];
        }
        
        foreach ($requests as $requestData) {
            CompanyRequest::create($requestData);
        }
        
        echo "✅ Created company requests\n\n";
    }
    
    // Test the exact queries from MatchingController
    echo "Testing MatchingController queries...\n";
    
    $availableDrivers = DriverNormalized::select('id', 'driver_id', 'first_name', 'surname', 'phone', 'email')
        ->whereNull('deleted_at')
        ->orderBy('first_name')
        ->get();
    
    $pendingRequests = CompanyRequest::with('company')
        ->whereIn('status', ['pending', 'Active'])
        ->whereNull('deleted_at')
        ->orderBy('created_at', 'desc')
        ->get();
    
    echo "Query results:\n";
    echo "  - Available drivers: " . $availableDrivers->count() . "\n";
    echo "  - Pending requests: " . $pendingRequests->count() . "\n\n";
    
    if ($pendingRequests->count() > 0) {
        echo "Pending requests found:\n";
        foreach ($pendingRequests as $request) {
            $companyName = $request->company ? $request->company->name : 'Unknown Company';
            echo "  - ID: {$request->id}, Company: $companyName, Status: {$request->status}\n";
            echo "    Description: " . substr($request->description, 0, 60) . "...\n";
        }
    } else {
        echo "❌ No pending requests found!\n";
        echo "Check the status values and soft delete handling.\n";
    }
    
    echo "\n🎉 Manual matching data check completed!\n";
    echo "The manual matching page should now show pending requests.\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}