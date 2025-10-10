-- Manual Admin User Creation for DriveLink
-- Run this in phpMyAdmin or MySQL command line

-- First, delete any existing admin user
DELETE FROM admin_users WHERE email = 'admin@drivelink.com';

-- Create new admin user with properly hashed password
-- Password: admin123
-- Hash: $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi (this is 'password')
-- Let's use a fresh hash for 'admin123'

INSERT INTO admin_users (
    name, 
    email, 
    password, 
    phone, 
    role, 
    status, 
    created_at, 
    updated_at
) VALUES (
    'System Administrator',
    'admin@drivelink.com',
    '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyuDZYRlLzM8H1X1Y1Y8H1Y8H1Y8H1',
    '+234-800-000-0000',
    'Super Admin',
    'Active',
    NOW(),
    NOW()
);

-- Verify the user was created
SELECT id, name, email, role, status, created_at FROM admin_users WHERE email = 'admin@drivelink.com';

-- Note: The password hash above is a placeholder. 
-- You should generate a proper hash for 'admin123' using PHP password_hash() function