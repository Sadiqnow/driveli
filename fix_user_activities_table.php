<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Fixing User Activities Table ===\n\n";

try {
    // Check if table exists
    $exists = \Schema::hasTable('user_activities');
    
    if ($exists) {
        echo "✓ user_activities table already exists\n";
    } else {
        echo "Creating user_activities table...\n";
        
        // Create the table directly
        \Schema::create('user_activities', function (\Illuminate\Database\Schema\Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('action');
            $table->string('description');
            $table->string('model_type')->nullable();
            $table->unsignedBigInteger('model_id')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();
            
            $table->foreign('user_id')->references('id')->on('admin_users')->onDelete('cascade');
            $table->index(['user_id', 'created_at']);
            $table->index(['model_type', 'model_id']);
            $table->index('action');
        });
        
        echo "✓ user_activities table created successfully\n";
    }
    
    // Test the table
    $count = DB::table('user_activities')->count();
    echo "✓ Table is accessible - {$count} records found\n";
    
    // Test UserActivity model
    $userActivityModel = new \App\Models\UserActivity();
    echo "✓ UserActivity model is working\n";
    
    // Test logging a sample activity if there are admin users
    $adminUser = \App\Models\AdminUser::first();
    if ($adminUser) {
        \App\Models\UserActivity::log('test', 'Testing user activity logging system', $adminUser);
        echo "✓ Sample activity logged successfully\n";
        
        $newCount = DB::table('user_activities')->count();
        echo "✓ Activity count after logging: {$newCount}\n";
    }
    
    echo "\n🎉 User Activities table is now ready!\n";
    echo "Dashboard should work without the table error now.\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}