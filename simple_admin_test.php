<?php

// Direct database test without full Laravel bootstrap

use App\Models\AdminUser;

$host = '127.0.0.1';
$db = 'drivelink_db';
$user = 'root';
$pass = '';

try {
    echo "Testing admin login issue...\n\n";
    
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "✅ Database connection successful\n";
    
    // Check if admin_users table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'admin_users'");
    if ($stmt->rowCount() > 0) {
        echo "✅ admin_users table exists\n";
        
        // Check admin users
        $stmt = $pdo->query("SELECT * FROM admin_users");
        $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "Found " . count($admins) . " admin users:\n";
        
        foreach ($admins as $admin) {
            echo "- Email: {$admin['email']} | Name: {$admin['name']} | Role: {$admin['role']} | Status: {$admin['status']}\n";
        }
        
        // Check for superadmin
        $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE email = ?");
        $stmt->execute(['adminII@drivelink.com']);
        $superAdmin = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($Admin_users) {
            echo "\n✅ admin exists\n";
            
            // Test password
            if (password_verify('', $Admin_users['password'])) {
                echo "✅ Password verification: SUCCESS\n";
            } else {
                echo "❌ Password verification: FAILED\n";
                echo "Fixing password...\n";
                
                $newHash = password_hash('password123', PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE admin_users SET password = ? WHERE email = ?");
                $stmt->execute([$newHash, 'admin@drivelink.com']);
                echo "✅ Password updated\n";
            }
        } else {
            echo "❌ Superadmin not found. Creating...\n";
            
            $stmt = $pdo->prepare("INSERT INTO admin_users (name, email, password, phone, role, status, email_verified_at, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                'Super Admin',
                'admin@drivelink.com',
                password_hash('password123', PASSWORD_DEFAULT),
                '+2348012345678',
                'Super Admin',
                'Active',
                date('Y-m-d H:i:s'),
                date('Y-m-d H:i:s'),
                date('Y-m-d H:i:s')
            ]);
            echo "✅ Superadmin created\n";
        }
    } else {
        echo "❌ admin_users table does not exist\n";
        echo "Please run: php artisan migrate\n";
    }
    
    echo "\n=== LOGIN CREDENTIALS ===\n";
    echo "URL: http://localhost/drivelink/public/admin/login\n";
    echo "Email: admin@drivelink.com\n";
    echo "Password: password123\n";
    
} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}