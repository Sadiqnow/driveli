<?php

require __DIR__ . '/vendor/autoload.php';

try {
    $app = require_once __DIR__ . '/bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();

    echo "Checking company_requests table structure...\n";
    
    // Check if table exists
    if (!Schema::hasTable('company_requests')) {
        echo "❌ Table 'company_requests' does not exist!\n";
        exit(1);
    }
    
    // List all columns
    $columns = Schema::getColumnListing('company_requests');
    echo "Current columns in company_requests table:\n";
    foreach ($columns as $column) {
        echo "  - $column\n";
    }
    
    // Check specifically for position_title
    if (Schema::hasColumn('company_requests', 'position_title')) {
        echo "✅ Column 'position_title' exists!\n";
    } else {
        echo "❌ Column 'position_title' does NOT exist!\n";
    }
    
    // Test the problematic query
    echo "\nTesting the query that was failing...\n";
    try {
        $result = App\Models\CompanyRequest::select(['id', 'position_title', 'created_at'])
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get();
        echo "✅ Query executed successfully! Found " . $result->count() . " records.\n";
    } catch (Exception $e) {
        echo "❌ Query still failing: " . $e->getMessage() . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
}