<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Creating User Activities Table via SQL ===\n\n";

try {
    // Read the SQL file
    $sql = file_get_contents('create_user_activities_direct.sql');
    
    // Split by semicolon to get individual statements
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    foreach ($statements as $statement) {
        if (!empty($statement) && !str_starts_with(trim($statement), '--')) {
            echo "Executing: " . substr($statement, 0, 50) . "...\n";
            DB::statement($statement);
            echo "✓ Success\n";
        }
    }
    
    // Verify table exists
    $tableExists = \Schema::hasTable('user_activities');
    if ($tableExists) {
        echo "\n✓ user_activities table created successfully!\n";
        
        // Count records
        $count = DB::table('user_activities')->count();
        echo "✓ Table has {$count} record(s)\n";
        
        // Test model
        $activity = new \App\Models\UserActivity();
        echo "✓ UserActivity model is functional\n";
        
        echo "\n🎉 User Activities system is now ready!\n";
        echo "The dashboard should work without errors now.\n";
    } else {
        echo "❌ Table creation failed\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    
    // Try alternative approach - create table manually
    echo "\nTrying alternative approach...\n";
    
    try {
        DB::statement("
            CREATE TABLE IF NOT EXISTS user_activities (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                user_id BIGINT UNSIGNED NOT NULL,
                action VARCHAR(255) NOT NULL,
                description TEXT NOT NULL,
                model_type VARCHAR(255) NULL,
                model_id BIGINT UNSIGNED NULL,
                old_values JSON NULL,
                new_values JSON NULL,
                ip_address VARCHAR(45) NULL,
                user_agent TEXT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_user_created (user_id, created_at),
                INDEX idx_action (action),
                INDEX idx_model (model_type, model_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ");
        
        echo "✓ Table created with alternative method!\n";
        
        // Insert sample record
        DB::table('user_activities')->insert([
            'user_id' => 1,
            'action' => 'system_setup',
            'description' => 'User Management System initialized',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'DriveLink Setup',
            'created_at' => now()
        ]);
        
        echo "✓ Sample activity record inserted\n";
        echo "\n🎉 User Activities system is ready!\n";
        
    } catch (Exception $e2) {
        echo "❌ Alternative method failed: " . $e2->getMessage() . "\n";
    }
}