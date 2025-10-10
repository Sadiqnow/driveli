<?php

echo "Verifying Auth Configuration...\n\n";

// Manually check the auth config file
$authConfig = include 'config/auth.php';

echo "1. Guards defined:\n";
foreach ($authConfig['guards'] as $guard => $config) {
    echo "   ✓ {$guard} -> {$config['provider']}\n";
}

echo "\n2. Providers defined:\n";
foreach ($authConfig['providers'] as $provider => $config) {
    echo "   ✓ {$provider} -> {$config['model']}\n";
}

echo "\n3. Admin Guard Check:\n";
if (isset($authConfig['guards']['admin'])) {
    echo "   ✅ Admin guard EXISTS\n";
    echo "   ✅ Admin provider: {$authConfig['guards']['admin']['provider']}\n";
    
    if (isset($authConfig['providers']['admin_users'])) {
        echo "   ✅ Admin provider EXISTS\n";
        echo "   ✅ Admin model: {$authConfig['providers']['admin_users']['model']}\n";
    } else {
        echo "   ❌ Admin provider MISSING\n";
    }
} else {
    echo "   ❌ Admin guard MISSING\n";
}

echo "\n4. Model Files Check:\n";
$adminUserPath = 'app/Models/AdminUser.php';
$driverPath = 'app/Models/Driver.php';

if (file_exists($adminUserPath)) {
    echo "   ✅ AdminUser model file exists\n";
} else {
    echo "   ❌ AdminUser model file missing\n";
}

if (file_exists($driverPath)) {
    echo "   ✅ Driver model file exists\n";
} else {
    echo "   ❌ Driver model file missing\n";
}

echo "\n✅ Verification complete!\n";