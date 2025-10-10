<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    echo "Checking company_requests table structure...\n";
    
    // Check if table exists
    if (Schema::hasTable('company_requests')) {
        echo "✓ company_requests table exists\n";
        
        // Get columns
        $columns = Schema::getColumnListing('company_requests');
        echo "Current columns: " . implode(', ', $columns) . "\n\n";
        
        // Check missing columns from model
        $modelColumns = [
            'id', 'company_id', 'driver_id', 'request_id', 'position_title', 
            'request_type', 'description', 'location', 'requirements', 'salary_range',
            'status', 'priority', 'created_by', 'approved_by', 'approved_at', 
            'expires_at', 'created_at', 'updated_at', 'deleted_at'
        ];
        
        echo "Missing columns:\n";
        foreach ($modelColumns as $col) {
            if (!in_array($col, $columns)) {
                echo "✗ Missing: $col\n";
            }
        }
        
        // Test basic query
        $count = DB::table('company_requests')->count();
        echo "\nRecord count: $count\n";
        
    } else {
        echo "✗ company_requests table does not exist\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}