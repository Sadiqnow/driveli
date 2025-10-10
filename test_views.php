<?php

echo "Testing Admin Views...\n\n";

// Check if view files exist
$viewFiles = [
    'admin/login.blade.php' => 'resources/views/admin/login.blade.php',
    'admin/forgot-password.blade.php' => 'resources/views/admin/forgot-password.blade.php',
    'admin/reset-password.blade.php' => 'resources/views/admin/reset-password.blade.php',
    'admin/dashboard.blade.php' => 'resources/views/admin/dashboard.blade.php'
];

echo "1. Checking View Files:\n";
foreach ($viewFiles as $viewName => $filePath) {
    if (file_exists($filePath)) {
        echo "   ✅ {$viewName} exists\n";
    } else {
        echo "   ❌ {$viewName} missing\n";
    }
}

echo "\n2. Checking File Sizes:\n";
foreach ($viewFiles as $viewName => $filePath) {
    if (file_exists($filePath)) {
        $size = filesize($filePath);
        echo "   ✓ {$viewName} - {$size} bytes\n";
    }
}

echo "\n3. Checking View Structure:\n";
$loginContent = file_get_contents('resources/views/admin/login.blade.php');

if (strpos($loginContent, 'route(\'admin.login.submit\')') !== false) {
    echo "   ✅ Login form action is correctly set\n";
} else {
    echo "   ❌ Login form action issue\n";
}

if (strpos($loginContent, 'csrf') !== false) {
    echo "   ✅ CSRF token included\n";
} else {
    echo "   ❌ CSRF token missing\n";
}

if (strpos($loginContent, 'AdminLTE') !== false) {
    echo "   ✅ AdminLTE styling included\n";
} else {
    echo "   ❌ AdminLTE styling missing\n";
}

echo "\n✅ View test completed!\n";