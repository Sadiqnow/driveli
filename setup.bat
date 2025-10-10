@echo off
echo 🚛 Drivelink Setup Script
echo =========================
echo.

echo 1. Running Laravel migrations...
php artisan migrate --force
echo ✅ Migrations completed!
echo.

echo 2. Running database seeders...
php artisan db:seed --force
echo ✅ Seeders completed!
echo.

echo 3. Clearing application cache...
php artisan config:clear
php artisan route:clear
php artisan view:clear
echo ✅ Cache cleared!
echo.

echo 🎉 Drivelink setup completed successfully!
echo.
echo 📧 Default Admin Login Credentials:
echo    Email: admin@drivelink.com
echo    Password: password123
echo.
echo 🌐 Access your admin panel at: /admin/login
echo 🔧 Or register a new admin at: /admin/register
echo.
pause