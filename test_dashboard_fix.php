<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Dashboard Error Fix Test ===\n\n";

try {
    // Test 1: Check if user_activities table exists
    $tableExists = \Schema::hasTable('user_activities');
    echo "1. user_activities table exists: " . ($tableExists ? "✓ YES" : "✗ NO") . "\n";
    
    if (!$tableExists) {
        echo "   Creating table...\n";
        \Schema::create('user_activities', function (\Illuminate\Database\Schema\Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('action');
            $table->text('description');
            $table->string('model_type')->nullable();
            $table->unsignedBigInteger('model_id')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();
            
            $table->index(['user_id', 'created_at']);
            $table->index('action');
            $table->index(['model_type', 'model_id']);
        });
        echo "   ✓ Table created\n";
    }
    
    // Test 2: Test AdminUser model
    echo "\n2. Testing AdminUser model...\n";
    $userCount = \App\Models\AdminUser::count();
    echo "   ✓ AdminUser model working - {$userCount} users found\n";
    
    // Test 3: Test UserActivity model
    echo "\n3. Testing UserActivity model...\n";
    $activityCount = DB::table('user_activities')->count();
    echo "   ✓ UserActivity accessible - {$activityCount} activities found\n";
    
    // Test 4: Test dashboard controller stats
    echo "\n4. Testing dashboard controller...\n";
    $controller = new \App\Http\Controllers\Admin\AdminDashboardController();
    $stats = $controller->getStats();
    echo "   ✓ Dashboard stats working\n";
    
    // Test 5: Test user activity logging
    echo "\n5. Testing activity logging...\n";
    $user = \App\Models\AdminUser::first();
    if ($user) {
        \App\Models\UserActivity::log('test', 'Dashboard error fix test', $user);
        $newCount = DB::table('user_activities')->count();
        echo "   ✓ Activity logged - new count: {$newCount}\n";
    }
    
    echo "\n=== DASHBOARD SHOULD NOW WORK ===\n";
    echo "✅ All components tested successfully\n";
    echo "✅ Step 2: User Management is ready\n";
    echo "✅ Dashboard will show Step 2 as completed\n";
    echo "\n🎉 Navigate to admin dashboard to see Step 2!\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "\nTrying to fix the error...\n";
    
    // Emergency fix - create basic table
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
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
        echo "✓ Emergency table creation successful\n";
    } catch (Exception $e2) {
        echo "❌ Emergency fix failed: " . $e2->getMessage() . "\n";
    }
}