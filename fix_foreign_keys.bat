@echo off
echo DriveLink Foreign Key Constraint Fixer
echo =====================================
echo.

echo Running PHP script to fix constraints...
php fix_constraints_simple.php

echo.
echo Press any key to continue...
pause > nul