<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    echo "Testing CompanyRequest query fix...\n";
    
    // Test the exact query that was failing
    $result = App\Models\CompanyRequest::select('id', 'position_title', 'created_at')
        ->orderBy('created_at', 'desc')
        ->limit(3)
        ->get();
    
    echo "✅ Query executed successfully!\n";
    echo "Found " . $result->count() . " records.\n";
    
    // Test the dashboard controller method
    $controller = new App\Http\Controllers\Admin\AdminDashboardController();
    $recentActivity = $controller->getRecentActivity();
    
    echo "✅ Dashboard getRecentActivity() executed successfully!\n";
    echo "Found " . $recentActivity->count() . " recent activities.\n";
    
    // Show table structure
    echo "\nTable structure:\n";
    $columns = Schema::getColumnListing('company_requests');
    foreach ($columns as $column) {
        echo "- $column\n";
    }
    
    echo "\n✅ All tests passed! The position_title column issue has been fixed.\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "File: " . $e->getFile() . "\n";
}