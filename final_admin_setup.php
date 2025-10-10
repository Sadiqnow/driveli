<?php

echo "Setting up fresh superadmin user...\n\n";

// Database connection
$host = '127.0.0.1';
$dbname = 'drivelink_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Step 1: Clean slate - delete existing admin users
    echo "1. Deleting existing admin users...\n";
    $deleteStmt = $pdo->prepare("DELETE FROM admin_users");
    $deleteStmt->execute();
    echo "   ✅ Cleared admin_users table\n\n";
    
    // Step 2: Create new superadmin with proper password hash
    echo "2. Creating new superadmin...\n";
    
    $adminPassword = password_hash('password123', PASSWORD_DEFAULT);
    $now = date('Y-m-d H:i:s');
    
    $insertStmt = $pdo->prepare("
        INSERT INTO admin_users (
            name, 
            email, 
            password, 
            phone, 
            role, 
            status, 
            email_verified_at, 
            created_at, 
            updated_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $result = $insertStmt->execute([
        'Super Admin',
        'admin@drivelink.com',
        $adminPassword,
        '+2348012345678',
        'Super Admin',
        'Active',
        $now,
        $now,
        $now
    ]);
    
    if ($result) {
        echo "   ✅ Superadmin created successfully\n\n";
        
        // Step 3: Verify the user
        echo "3. Verifying superadmin...\n";
        
        $verifyStmt = $pdo->prepare("SELECT * FROM admin_users WHERE email = ?");
        $verifyStmt->execute(['admin@drivelink.com']);
        $admin = $verifyStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($admin) {
            echo "   ✅ Admin found in database\n";
            echo "   - ID: {$admin['id']}\n";
            echo "   - Name: {$admin['name']}\n";
            echo "   - Email: {$admin['email']}\n";
            echo "   - Role: {$admin['role']}\n";
            echo "   - Status: {$admin['status']}\n";
            
            // Test password
            if (password_verify('password123', $admin['password'])) {
                echo "   ✅ Password verification: PASSED\n\n";
            } else {
                echo "   ❌ Password verification: FAILED\n\n";
            }
        } else {
            echo "   ❌ Admin not found after creation\n\n";
        }
        
        echo "=====================================\n";
        echo "🎉 SETUP COMPLETE!\n";
        echo "=====================================\n";
        echo "Login URL: http://localhost/drivelink/public/admin/login\n";
        echo "Email: admin@drivelink.com\n";
        echo "Password: password123\n";
        echo "=====================================\n";
        
    } else {
        echo "   ❌ Failed to create superadmin\n";
    }
    
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}