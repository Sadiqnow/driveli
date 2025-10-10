@echo off
echo 🚛 Drivelink Complete Setup Script
echo ==================================
echo.

cd /d "C:\xampp\htdocs\drivelink"

echo Step 1: Running migrations...
"C:\xampp\php\php.exe" artisan migrate --force
if %errorlevel% neq 0 (
    echo ❌ Migration failed!
    pause
    exit /b 1
)
echo ✅ Migrations completed!
echo.

echo Step 2: Running seeders...
"C:\xampp\php\php.exe" artisan db:seed --force
if %errorlevel% neq 0 (
    echo ❌ Seeding failed!
    pause
    exit /b 1
)
echo ✅ Seeders completed!
echo.

echo Step 3: Clearing cache...
"C:\xampp\php\php.exe" artisan config:clear
"C:\xampp\php\php.exe" artisan route:clear
"C:\xampp\php\php.exe" artisan view:clear
echo ✅ Cache cleared!
echo.

echo Step 4: Testing authentication...
"C:\xampp\php\php.exe" quick_auth_test.php
echo.

echo 🎉 Setup completed successfully!
echo.
echo 📧 Default Admin Login Credentials:
echo    Email: admin@drivelink.com
echo    Password: password123
echo.
echo 🌐 Access your admin panel at: http://localhost/drivelink/public/admin/login
echo 🔧 Or register a new admin at: http://localhost/drivelink/public/admin/register
echo.
pause