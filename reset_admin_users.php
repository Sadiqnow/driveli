<?php

// Reset admin users - delete all and create fresh superadmin
$host = '127.0.0.1';
$db = 'drivelink_db';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== RESETTING ADMIN USERS ===\n\n";
    
    // Step 1: Check current admin users
    $stmt = $pdo->query("SELECT id, name, email, role, status FROM admin_users");
    $existingAdmins = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Current admin users (" . count($existingAdmins) . "):\n";
    foreach ($existingAdmins as $admin) {
        echo "- ID: {$admin['id']} | Email: {$admin['email']} | Name: {$admin['name']} | Role: {$admin['role']} | Status: {$admin['status']}\n";
    }
    echo "\n";
    
    // Step 2: Delete all existing admin users
    echo "Deleting all existing admin users...\n";
    $stmt = $pdo->exec("DELETE FROM admin_users");
    echo "✅ Deleted {$stmt} admin user records\n\n";
    
    // Step 3: Reset auto increment
    echo "Resetting auto increment...\n";
    $pdo->exec("ALTER TABLE admin_users AUTO_INCREMENT = 1");
    echo "✅ Auto increment reset\n\n";
    
    // Step 4: Create fresh superadmin user
    echo "Creating fresh superadmin user...\n";
    
    $stmt = $pdo->prepare("
        INSERT INTO admin_users (
            name, 
            email, 
            password, 
            phone, 
            role, 
            status, 
            email_verified_at, 
            created_at, 
            updated_at,
            permissions
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $permissions = json_encode([
        'manage_users',
        'manage_drivers', 
        'manage_companies',
        'manage_requests',
        'manage_matches',
        'manage_commissions',
        'view_reports',
        'manage_notifications',
        'manage_settings',
        'delete_records'
    ]);
    
    $stmt->execute([
        'Super Admin',
        'admin@drivelink.com',
        password_hash('password123', PASSWORD_DEFAULT),
        '+2348012345678',
        'Super Admin',
        'Active',
        date('Y-m-d H:i:s'),
        date('Y-m-d H:i:s'),
        date('Y-m-d H:i:s'),
        $permissions
    ]);
    
    $adminId = $pdo->lastInsertId();
    echo "✅ Created superadmin with ID: {$adminId}\n\n";
    
    // Step 5: Verify the new admin user
    echo "Verifying new admin user...\n";
    $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE email = ?");
    $stmt->execute(['admin@drivelink.com']);
    $newAdmin = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($newAdmin) {
        echo "✅ Admin user found:\n";
        echo "   - ID: {$newAdmin['id']}\n";
        echo "   - Name: {$newAdmin['name']}\n";
        echo "   - Email: {$newAdmin['email']}\n";
        echo "   - Role: {$newAdmin['role']}\n";
        echo "   - Status: {$newAdmin['status']}\n";
        echo "   - Email Verified: {$newAdmin['email_verified_at']}\n";
        
        // Test password verification
        if (password_verify('password123', $newAdmin['password'])) {
            echo "✅ Password verification: SUCCESS\n";
        } else {
            echo "❌ Password verification: FAILED\n";
        }
    } else {
        echo "❌ Could not find newly created admin user\n";
    }
    
    echo "\n=== LOGIN INSTRUCTIONS ===\n";
    echo "URL: http://localhost/drivelink/public/admin/login\n";
    echo "Email: admin@drivelink.com\n";
    echo "Password: password123\n\n";
    echo "✅ Admin user reset completed successfully!\n";
    
} catch (PDOException $e) {
    echo "❌ Database error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}