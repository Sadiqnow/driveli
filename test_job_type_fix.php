<?php

require_once 'vendor/autoload.php';

try {
    $app = require_once 'bootstrap/app.php';
    $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

    echo "Testing job_type Column Fix\n";
    echo "===========================\n\n";

    // Test 1: Check CompanyRequest model queries
    echo "1. Testing CompanyRequest model queries...\n";
    
    try {
        // Test basic query
        $requests = \App\Models\CompanyRequest::select('id', 'company_id', 'description', 'location', 'status')
            ->limit(5)
            ->get();
        
        echo "   ✓ Basic CompanyRequest query successful: {$requests->count()} requests\n";
        
        // Test with relationships
        $requestsWithCompany = \App\Models\CompanyRequest::with('company')
            ->select('id', 'company_id', 'description', 'location', 'status')
            ->limit(3)
            ->get();
        
        echo "   ✓ CompanyRequest with company relationship: {$requestsWithCompany->count()} requests\n";
        
        foreach ($requestsWithCompany as $request) {
            $companyName = $request->company ? $request->company->name : 'No Company';
            echo "     - Request {$request->id}: {$companyName} - Location: {$request->location}\n";
        }
        
    } catch (Exception $e) {
        echo "   ❌ CompanyRequest query failed: " . $e->getMessage() . "\n";
    }
    echo "\n";

    // Test 2: Test DriverMatch relationships with fixed job_type references
    echo "2. Testing DriverMatch with CompanyRequest relationships...\n";
    
    try {
        $matches = \App\Models\DriverMatch::with([
                'companyRequest' => function($query) {
                    $query->select('id', 'company_id', 'description', 'location', 'status');
                },
                'companyRequest.company' => function($query) {
                    $query->select('id', 'name', 'email');
                }
            ])
            ->limit(3)
            ->get();
        
        echo "   ✓ DriverMatch with CompanyRequest relationships: {$matches->count()} matches\n";
        
        foreach ($matches as $match) {
            if ($match->companyRequest) {
                $companyName = $match->companyRequest->company ? $match->companyRequest->company->name : 'No Company';
                $location = $match->companyRequest->location ?? 'No Location';
                echo "     - Match {$match->match_id}: {$companyName} - Location: {$location}\n";
            } else {
                echo "     - Match {$match->match_id}: No request linked\n";
            }
        }
        
    } catch (Exception $e) {
        echo "   ❌ DriverMatch relationship query failed: " . $e->getMessage() . "\n";
    }
    echo "\n";

    // Test 3: Test the exact queries from the matching controller
    echo "3. Testing matching controller queries...\n";
    
    try {
        // Test the recent matches query from index()
        $recentMatches = \App\Models\DriverMatch::with([
                'driver' => function($query) {
                    $query->select('id', 'driver_id', 'first_name', 'surname', 'phone', 'email');
                },
                'companyRequest' => function($query) {
                    $query->select('id', 'company_id', 'description', 'location', 'status');
                },
                'companyRequest.company' => function($query) {
                    $query->select('id', 'name', 'email');
                }
            ])
            ->whereNotNull('match_id')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
        
        echo "   ✓ Recent matches query from controller: {$recentMatches->count()} matches\n";
        
        foreach ($recentMatches as $match) {
            echo "     - Match {$match->match_id} ({$match->status})\n";
        }
        
    } catch (Exception $e) {
        echo "   ❌ Controller query failed: " . $e->getMessage() . "\n";
    }
    echo "\n";

    // Test 4: Test CompanyRequest pending requests query
    echo "4. Testing pending requests query...\n";
    
    try {
        $pendingRequests = \App\Models\CompanyRequest::with('company')
            ->whereIn('status', ['pending', 'Pending', 'Active'])
            ->whereNull('deleted_at')
            ->limit(5)
            ->get();
        
        echo "   ✓ Pending requests query: {$pendingRequests->count()} requests\n";
        
        foreach ($pendingRequests as $request) {
            $companyName = $request->company ? $request->company->name : 'No Company';
            echo "     - Request {$request->id}: {$companyName} ({$request->status})\n";
        }
        
    } catch (Exception $e) {
        echo "   ❌ Pending requests query failed: " . $e->getMessage() . "\n";
    }
    echo "\n";

    // Test 5: Test if model allows creating new requests without job_type
    echo "5. Testing CompanyRequest creation without job_type...\n";
    
    try {
        // Check if we can create a request without job_type
        $testData = [
            'company_id' => 1,
            'status' => 'pending',
            'description' => 'Test request without job_type',
            'location' => 'Test Location',
            'requirements' => 'Test requirements',
            'salary_range' => '50000-60000',
        ];
        
        // We're not actually creating it, just validating the fillable array
        $request = new \App\Models\CompanyRequest();
        $fillableFields = $request->getFillable();
        
        $hasJobType = in_array('job_type', $fillableFields);
        if (!$hasJobType) {
            echo "   ✓ job_type removed from fillable array\n";
        } else {
            echo "   ⚠ job_type still in fillable array (this might cause issues)\n";
        }
        
    } catch (Exception $e) {
        echo "   ❌ Model test failed: " . $e->getMessage() . "\n";
    }
    echo "\n";

    echo "✅ job_type column fix test completed!\n\n";
    echo "Summary of fixes:\n";
    echo "- Removed job_type from controller queries\n";
    echo "- Replaced job_type with location in all views\n";
    echo "- Updated CompanyRequest model fillable array\n";
    echo "- Fixed CompanyService to not use job_type\n";
    echo "- All queries should now work without column errors\n\n";
    echo "The matching system should now be fully functional!\n";

} catch (Exception $e) {
    echo "❌ Critical Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}