<?php

require __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Auth;

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
    $credentials = [
        'email' => 'superadmin@drivelink.com',
        'password' => 'Blackzonex@1986..'
    ];

    echo "Testing Auth::guard('admin')->attempt...\n";

    $result = Auth::guard('admin')->attempt($credentials);

    if ($result) {
        echo "✅ Login successful.\n";
        $user = Auth::guard('admin')->user();
        echo "Logged in user: " . $user->email . "\n";
    } else {
        echo "❌ Login failed.\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
