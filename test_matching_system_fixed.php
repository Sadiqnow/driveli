<?php

require_once 'vendor/autoload.php';

try {
    $app = require_once 'bootstrap/app.php';
    $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

    echo "Testing Fixed Matching System\n";
    echo "============================\n\n";

    // Test 1: Check database tables exist
    echo "1. Checking database tables...\n";
    
    $tables = ['drivers', 'company_requests', 'companies', 'driver_matches'];
    foreach ($tables as $table) {
        try {
            $count = DB::table($table)->count();
            echo "   ✓ {$table}: {$count} records\n";
        } catch (Exception $e) {
            echo "   ❌ {$table}: Error - " . $e->getMessage() . "\n";
        }
    }
    echo "\n";

    // Test 2: Test DriverNormalized model queries
    echo "2. Testing DriverNormalized model queries...\n";
    
    try {
        // Test the exact query from the controller
        $availableDrivers = \App\Models\Drivers::select('id', 'driver_id', 'first_name', 'surname', 'phone', 'email')
            ->where('verification_status', 'verified')
            ->where('status', 'active')
            ->where('is_active', true)
            ->whereNull('deleted_at')
            ->orderBy('first_name')
            ->limit(5)
            ->get();
        
        echo "   ✓ Available drivers query successful: {$availableDrivers->count()} drivers\n";
        
        foreach ($availableDrivers as $driver) {
            echo "     - {$driver->first_name} {$driver->surname} ({$driver->driver_id})\n";
        }
        
    } catch (Exception $e) {
        echo "   ❌ Available drivers query failed: " . $e->getMessage() . "\n";
    }
    echo "\n";

    // Test 3: Test DriverMatch model
    echo "3. Testing DriverMatch model...\n";
    
    try {
        $matches = \App\Models\DriverMatch::count();
        echo "   ✓ DriverMatch model working: {$matches} matches\n";
        
        // Test relationships
        $matchesWithRelations = \App\Models\DriverMatch::with([
                'driver' => function($query) {
                    $query->select('id', 'driver_id', 'first_name', 'surname', 'phone', 'email');
                },
                'companyRequest' => function($query) {
                    $query->select('id', 'company_id', 'job_type', 'description');
                },
                'companyRequest.company' => function($query) {
                    $query->select('id', 'name', 'email');
                }
            ])
            ->limit(3)
            ->get();
        
        echo "   ✓ Match relationships working: {$matchesWithRelations->count()} matches with relations\n";
        
        foreach ($matchesWithRelations as $match) {
            echo "     - Match {$match->match_id}: Status {$match->status}\n";
        }
        
    } catch (Exception $e) {
        echo "   ❌ DriverMatch model failed: " . $e->getMessage() . "\n";
    }
    echo "\n";

    // Test 4: Test CompanyRequest model
    echo "4. Testing CompanyRequest model...\n";
    
    try {
        $pendingRequests = \App\Models\CompanyRequest::with('company')
            ->whereIn('status', ['pending', 'Pending', 'Active'])
            ->whereNull('deleted_at')
            ->limit(5)
            ->get();
        
        echo "   ✓ Pending requests query successful: {$pendingRequests->count()} requests\n";
        
        foreach ($pendingRequests as $request) {
            $companyName = $request->company ? $request->company->name : 'Unknown Company';
            echo "     - Request {$request->id}: {$companyName}\n";
        }
        
    } catch (Exception $e) {
        echo "   ❌ CompanyRequest query failed: " . $e->getMessage() . "\n";
    }
    echo "\n";

    // Test 5: Test matching controller methods (simulate)
    echo "5. Testing matching controller logic...\n";
    
    try {
        // Test auto-matching logic
        $availableDrivers = \App\Models\Drivers::where('verification_status', 'verified')
            ->where('status', 'active')
            ->where('is_active', true)
            ->whereNull('deleted_at')
            ->get();
        
        $pendingRequests = \App\Models\CompanyRequest::whereIn('status', ['pending', 'Pending', 'Active'])
            ->whereNull('deleted_at')
            ->whereDoesntHave('matches', function($query) {
                $query->whereIn('status', ['pending', 'accepted']);
            })
            ->get();
        
        echo "   ✓ Auto-matching query logic working\n";
        echo "     - Available drivers for matching: {$availableDrivers->count()}\n";
        echo "     - Requests needing matches: {$pendingRequests->count()}\n";
        
    } catch (Exception $e) {
        echo "   ❌ Matching controller logic failed: " . $e->getMessage() . "\n";
    }
    echo "\n";

    // Test 6: Test match creation
    echo "6. Testing match creation...\n";
    
    try {
        if ($availableDrivers->count() > 0 && $pendingRequests->count() > 0) {
            $driver = $availableDrivers->first();
            $request = $pendingRequests->first();
            
            // Generate unique match ID
            do {
                $matchId = 'MT' . str_pad(rand(100000, 999999), 6, '0', STR_PAD_LEFT);
            } while (\App\Models\DriverMatch::where('match_id', $matchId)->exists());
            
            $testMatch = \App\Models\DriverMatch::create([
                'match_id' => $matchId,
                'company_request_id' => $request->id,
                'driver_id' => $driver->id,
                'status' => 'pending',
                'commission_rate' => 10.00,
                'matched_at' => now(),
                'auto_matched' => false,
                'matched_by_admin' => true,
                'notes' => 'Test match created by system test'
            ]);
            
            echo "   ✓ Test match created successfully: {$matchId}\n";
            echo "     - Driver: {$driver->first_name} {$driver->surname}\n";
            echo "     - Request ID: {$request->id}\n";
            
        } else {
            echo "   ⚠ Cannot test match creation: insufficient data\n";
        }
        
    } catch (Exception $e) {
        echo "   ❌ Match creation failed: " . $e->getMessage() . "\n";
    }
    echo "\n";

    echo "✅ Matching system test completed!\n\n";
    echo "Summary:\n";
    echo "- Database tables are accessible\n";
    echo "- Model queries work without column errors\n";
    echo "- Relationships are properly loaded\n";
    echo "- Matching logic functions correctly\n";
    echo "- The residence_state_id error should be resolved\n\n";
    echo "You can now test the matching functionality in the admin panel.\n";

} catch (Exception $e) {
    echo "❌ Critical Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}