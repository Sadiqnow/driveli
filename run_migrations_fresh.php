<?php

echo "🔄 Running Fresh Migrations for DriveLink...\n\n";

try {
    // Change to the correct directory
    chdir(__DIR__);
    
    echo "1. Clearing config cache...\n";
    exec('php artisan config:clear 2>&1', $output1, $return1);
    echo implode("\n", $output1) . "\n\n";
    
    echo "2. Running fresh migrations...\n";
    exec('php artisan migrate:fresh 2>&1', $output2, $return2);
    echo implode("\n", $output2) . "\n\n";
    
    if ($return2 === 0) {
        echo "✅ Migrations completed successfully!\n\n";
        
        echo "3. Creating admin user via Artisan Tinker...\n";
        
        // Create a temporary file with the tinker commands
        $tinkerCommands = "
App\Models\AdminUser::create([
    'name' => 'System Administrator',
    'email' => 'admin@drivelink.com', 
    'password' => 'admin123',
    'phone' => '+234-800-000-0000',
    'role' => 'Super Admin',
    'status' => 'Active'
]);
echo 'Admin user created successfully!';
exit;
";
        
        file_put_contents('temp_tinker.php', $tinkerCommands);
        
        exec('php artisan tinker < temp_tinker.php 2>&1', $output3, $return3);
        echo implode("\n", $output3) . "\n\n";
        
        // Clean up
        unlink('temp_tinker.php');
        
        echo "🎉 Setup completed successfully!\n\n";
        echo "🔑 Login Credentials:\n";
        echo "   URL: http://localhost/drivelink/public/admin/login\n";
        echo "   Email: admin@drivelink.com\n";
        echo "   Password: admin123\n\n";
        
    } else {
        echo "❌ Migration failed with return code: $return2\n";
        echo "Error output:\n" . implode("\n", $output2) . "\n\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n\n";
}

echo "✨ Script completed!\n";