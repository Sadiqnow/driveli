<?php

// Create superadmin user directly
$host = '127.0.0.1';
$db = 'drivelink_db';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Creating superadmin user...\n";
    
    // First, check if user already exists
    $stmt = $pdo->prepare("SELECT id FROM admin_users WHERE email = ?");
    $stmt->execute(['admin@drivelink.com']);
    $existing = $stmt->fetch();
    
    if ($existing) {
        echo "Superadmin already exists. Updating password...\n";
        
        $stmt = $pdo->prepare("UPDATE admin_users SET password = ? WHERE email = ?");
        $stmt->execute([
            password_hash('password123', PASSWORD_DEFAULT),
            'admin@drivelink.com'
        ]);
    } else {
        echo "Creating new superadmin...\n";
        
        $stmt = $pdo->prepare("
            INSERT INTO admin_users (name, email, password, phone, role, status, email_verified_at, created_at, updated_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
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
    }
    
    echo "✅ Superadmin ready!\n";
    echo "\nLogin credentials:\n";
    echo "URL: http://localhost/drivelink/public/admin/login\n";
    echo "Email: admin@drivelink.com\n";
    echo "Password: password123\n";
    
    // Verify the user can be found and password verified
    $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE email = ?");
    $stmt->execute(['admin@drivelink.com']);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($admin && password_verify('password123', $admin['password'])) {
        echo "✅ Password verification test: SUCCESS\n";
    } else {
        echo "❌ Password verification test: FAILED\n";
    }
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
}