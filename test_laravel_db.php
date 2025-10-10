<?php
require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

try {
    // Test database connection
    $pdo = new PDO('mysql:host=127.0.0.1;port=3306', 'root', '');
    echo "MySQL connection successful\n";
    
    // Create database if not exists
    $pdo->exec("CREATE DATABASE IF NOT EXISTS drivelink_db");
    echo "Database created or exists\n";
    
    // Now test Laravel's database connection
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    
    // Clear cache first
    $kernel->call('config:clear');
    $kernel->call('cache:clear');
    
    // Run migrations
    echo "Running migrations...\n";
    $kernel->call('migrate', ['--force' => true]);
    echo "Migrations completed\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>