<?php

echo "🔐 Password Hash Generator for DriveLink\n\n";

$password = 'admin123';
$hash = password_hash($password, PASSWORD_DEFAULT);

echo "Password: $password\n";
echo "Hash: $hash\n\n";

// SQL to insert admin user
echo "📋 SQL Query to create admin user:\n\n";
echo "DELETE FROM admin_users WHERE email = 'admin@drivelink.com';\n\n";

echo "INSERT INTO admin_users (\n";
echo "    name, email, password, phone, role, status, created_at, updated_at\n";
echo ") VALUES (\n";
echo "    'System Administrator',\n";
echo "    'admin@drivelink.com',\n";
echo "    '$hash',\n";
echo "    '+234-800-000-0000',\n";
echo "    'Super Admin',\n";
echo "    'Active',\n";
echo "    NOW(),\n";
echo "    NOW()\n";
echo ");\n\n";

// Test verification
echo "🧪 Testing hash verification:\n";
$test = password_verify($password, $hash);
echo "Verification: " . ($test ? "✅ PASS" : "❌ FAIL") . "\n\n";

echo "🚀 Copy the SQL above and run it in phpMyAdmin!\n";
echo "✨ Then login with:\n";
echo "   Email: admin@drivelink.com\n";
echo "   Password: admin123\n";

?>