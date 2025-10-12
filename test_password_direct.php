<?php

require __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Hash;
use App\Models\AdminUser;

// Bootstrap Laravel
$app = new Application(__DIR__);
$app->singleton(
    Illuminate\Contracts\Http\Kernel::class,
    App\Http\Kernel::class
);
$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);
$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $email = 'superadmin@drivelink.com';
    $password = 'Blackzonex@1986..';

    $admin = AdminUser::where('email', $email)->first();

    if (!$admin) {
        echo "❌ Admin not found.\n";
        exit;
    }

    echo "Admin found: {$admin->name}\n";
    echo "Status: {$admin->status}\n";
    echo "Is Active: " . ($admin->isActive() ? 'Yes' : 'No') . "\n";

    $passwordMatches = Hash::check($password, $admin->password);
    echo "Password matches: " . ($passwordMatches ? 'Yes' : 'No') . "\n";

    if ($passwordMatches && $admin->isActive()) {
        echo "✅ Should be able to login.\n";
    } else {
        echo "❌ Cannot login.\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
