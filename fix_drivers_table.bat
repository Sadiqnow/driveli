@echo off
echo DriveLink Drivers Table Fixer
echo =============================
echo.

echo Checking current directory...
cd /d "%~dp0"
echo Current directory: %CD%
echo.

echo Attempting to fix drivers_normalized table...
echo.

echo Method 1: Using Laravel Artisan Command
php artisan drivelink:fix-drivers-table --force
echo.

echo Method 2: Using PHP Script (if artisan fails)
php fix_drivers_normalized_table.php
echo.

echo Method 3: Running standard migrations
php artisan migrate --force
echo.

echo Checking if table was created...
php -r "
try {
    require 'vendor/autoload.php';
    \$app = require 'bootstrap/app.php';
    \$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
    \$exists = \Illuminate\Support\Facades\Schema::hasTable('drivers_normalized');
    echo 'drivers_normalized table exists: ' . (\$exists ? 'YES' : 'NO') . \"\n\";
    if (\$exists) {
        \$count = \App\Models\DriverNormalized::count();
        echo 'Current driver count: ' . \$count . \"\n\";
        echo 'Table is ready for use!' . \"\n\";
    }
} catch (Exception \$e) {
    echo 'Error checking table: ' . \$e->getMessage() . \"\n\";
}
"

echo.
echo Fix process complete!
echo Press any key to continue...
pause > nul