<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== DriveLink Database Reset Tool ===\n\n";

echo "This will:\n";
echo "1. Drop all existing tables\n";
echo "2. Run fresh migrations\n";
echo "3. Seed the database with sample data\n\n";

echo "⚠️  WARNING: This will delete all existing data!\n";
echo "Are you sure you want to continue? (y/N): ";

$handle = fopen("php://stdin", "r");
$response = trim(fgets($handle));
fclose($handle);

if (strtolower($response) !== 'y' && strtolower($response) !== 'yes') {
    echo "Operation cancelled.\n";
    exit(0);
}

try {
    echo "\n1. Dropping all tables...\n";
    
    // Disable foreign key checks
    \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS = 0');
    
    // Get all table names
    $tables = \Illuminate\Support\Facades\DB::select('SHOW TABLES');
    $dbName = \Illuminate\Support\Facades\DB::connection()->getDatabaseName();
    $tableColumn = "Tables_in_{$dbName}";
    
    foreach ($tables as $table) {
        $tableName = $table->$tableColumn;
        if ($tableName !== 'migrations') { // Keep migrations table
            \Illuminate\Support\Facades\DB::statement("DROP TABLE IF EXISTS `{$tableName}`");
            echo "  ✓ Dropped {$tableName}\n";
        }
    }
    
    // Re-enable foreign key checks
    \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    
    echo "\n2. Running fresh migrations...\n";
    
    // Run migrations
    \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
    echo "  ✓ Migrations completed\n";
    
    echo "\n3. Seeding database...\n";
    
    // Run seeders
    \Illuminate\Support\Facades\Artisan::call('db:seed', ['--force' => true]);
    echo "  ✓ Database seeded\n";
    
    echo "\n✅ Database reset completed successfully!\n\n";
    
    // Show login credentials
    echo "🔐 Admin Login Credentials:\n";
    echo "  URL: http://localhost/drivelink/admin/login\n";
    echo "  Email: admin@drivelink.com\n";
    echo "  Password: password123\n\n";
    
    echo "📊 Sample data created:\n";
    echo "  - 3 Admin users\n";
    echo "  - 3 Sample companies\n";
    echo "  - Sample drivers (if applicable)\n\n";
    
} catch (\Exception $e) {
    echo "\n❌ Error occurred: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n\n";
    
    // Try to re-enable foreign key checks
    try {
        \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    } catch (\Exception $fkError) {
        // Ignore
    }
}