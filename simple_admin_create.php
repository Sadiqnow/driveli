<?php

// Simple admin creation without Laravel bootstrap
// This bypasses potential autoloading issues

echo "Creating admin user directly...\n";

$servername = "127.0.0.1";
$username = "root";
$password = "";
$dbname = "drivelink_db";

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Database connected\n";
    
    // Create password hash
    $plainPassword = 'admin123';
    $hashedPassword = password_hash($plainPassword, PASSWORD_DEFAULT);
    
    // Delete existing admin
    $deleteStmt = $pdo->prepare("DELETE FROM admin_users WHERE email = ?");
    $deleteStmt->execute(['admin@drivelink.com']);
    
    // Insert new admin
    $insertStmt = $pdo->prepare("
        INSERT INTO admin_users (name, email, password, phone, role, status, created_at, updated_at) 
        VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())
    ");
    
    $insertStmt->execute([
        'System Administrator',
        'admin@drivelink.com',
        $hashedPassword,
        '+234-800-000-0000',
        'Super Admin',
        'Active'
    ]);
    
    echo "✅ Admin user created successfully!\n";
    echo "   Email: admin@drivelink.com\n";
    echo "   Password: admin123\n";
    echo "   Hash: " . substr($hashedPassword, 0, 30) . "...\n";
    
    // Verify the user
    $selectStmt = $pdo->prepare("SELECT * FROM admin_users WHERE email = ?");
    $selectStmt->execute(['admin@drivelink.com']);
    $admin = $selectStmt->fetch(PDO::FETCH_ASSOC);
    
    if ($admin) {
        echo "\n✅ Verification:\n";
        echo "   ID: " . $admin['id'] . "\n";
        echo "   Name: " . $admin['name'] . "\n";
        echo "   Status: " . $admin['status'] . "\n";
        echo "   Created: " . $admin['created_at'] . "\n";
        
        // Test password verification
        $passwordCheck = password_verify($plainPassword, $admin['password']);
        echo "   Password Check: " . ($passwordCheck ? "✅ PASS" : "❌ FAIL") . "\n";
    }
    
    echo "\n🚀 You can now login with:\n";
    echo "   URL: http://localhost/drivelink/public/admin/login\n";
    echo "   Email: admin@drivelink.com\n";
    echo "   Password: admin123\n";
    
} catch(PDOException $e) {
    echo "❌ Database Error: " . $e->getMessage() . "\n";
    echo "\n🔧 Check:\n";
    echo "1. XAMPP MySQL is running\n";
    echo "2. Database 'drivelink_db' exists\n";
    echo "3. Table 'admin_users' exists\n";
}

echo "\n✨ Done!\n";