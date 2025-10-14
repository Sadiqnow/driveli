<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\CompanyRequest;
use App\Models\Company;
use App\Models\Drivers;

echo "Testing Manual Matching Data\n";
echo "===========================\n\n";

try {
    // Check if we have any company requests
    $totalRequests = CompanyRequest::count();
    echo "Total company requests: $totalRequests\n";
    
    // Check pending requests specifically
    $pendingRequests = CompanyRequest::with('company')
        ->whereIn('status', ['pending', 'Active'])
        ->whereNull('deleted_at')
        ->get();
    
    echo "Pending requests found: " . $pendingRequests->count() . "\n\n";
    
    if ($pendingRequests->count() > 0) {
        echo "Pending requests:\n";
        foreach ($pendingRequests as $request) {
            echo "  - ID: {$request->id}, Company: " . ($request->company->name ?? 'N/A') . ", Status: {$request->status}\n";
            echo "    Description: " . substr($request->description ?? 'No description', 0, 50) . "...\n";
        }
    } else {
        echo "❌ No pending requests found!\n\n";
        
        // Check what requests exist with their statuses
        $allRequests = CompanyRequest::all();
        echo "All requests with their statuses:\n";
        foreach ($allRequests as $request) {
            echo "  - ID: {$request->id}, Status: '{$request->status}', Deleted: " . ($request->deleted_at ? 'YES' : 'NO') . "\n";
        }
        
        // Check if we have companies
        $companyCount = Company::count();
        echo "\nTotal companies: $companyCount\n";
        
        if ($companyCount == 0) {
            echo "Creating test companies...\n";
            Company::create([
                'name' => 'ABC Logistics',
                'email' => 'admin@abclogistics.com',
                'phone' => '08012345678',
                'address' => '123 Business Street, Lagos',
                'status' => 'active'
            ]);
            
            Company::create([
                'name' => 'XYZ Transport',
                'email' => 'info@xyztransport.com', 
                'phone' => '08087654321',
                'address' => '456 Commerce Ave, Abuja',
                'status' => 'active'
            ]);
            
            echo "✅ Created test companies\n";
        }
        
        // Create test requests if none exist
        if ($totalRequests == 0) {
            echo "Creating test company requests...\n";
            
            $companies = Company::take(2)->get();
            
            if ($companies->count() > 0) {
                CompanyRequest::create([
                    'request_id' => 'REQ_' . time() . '_1',
                    'company_id' => $companies->first()->id,
                    'status' => 'pending',
                    'description' => 'Need experienced driver for logistics operations',
                    'location' => 'Lagos',
                    'job_type' => 'Delivery',
                    'position_title' => 'Logistics Driver',
                    'requirements' => 'Valid license, 2+ years experience',
                    'salary_range' => '150000-200000',
                    'created_by' => 1
                ]);
                
                CompanyRequest::create([
                    'request_id' => 'REQ_' . time() . '_2',
                    'company_id' => $companies->first()->id,
                    'status' => 'Active',
                    'description' => 'Urgent: Driver needed for executive transport',
                    'location' => 'Abuja',
                    'job_type' => 'Executive',
                    'position_title' => 'Executive Driver',
                    'requirements' => 'Professional driver, clean record',
                    'salary_range' => '250000-300000',
                    'created_by' => 1
                ]);
                
                if ($companies->count() > 1) {
                    CompanyRequest::create([
                        'request_id' => 'REQ_' . time() . '_3',
                        'company_id' => $companies->get(1)->id,
                        'status' => 'pending',
                        'description' => 'Part-time driver for weekend deliveries',
                        'location' => 'Port Harcourt',
                        'job_type' => 'Part-time',
                        'position_title' => 'Part-time Delivery Driver',
                        'requirements' => 'Weekend availability',
                        'salary_range' => '80000-120000',
                        'created_by' => 1
                    ]);
                }
                
                echo "✅ Created test company requests\n";
                
                // Re-check pending requests
                $newPendingRequests = CompanyRequest::with('company')
                    ->whereIn('status', ['pending', 'Active'])
                    ->whereNull('deleted_at')
                    ->get();
                
                echo "\nNew pending requests count: " . $newPendingRequests->count() . "\n";
            }
        }
    }
    
    // Check available drivers
    $availableDrivers = Drivers::whereNull('deleted_at')->count();
    echo "\nAvailable drivers: $availableDrivers\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}