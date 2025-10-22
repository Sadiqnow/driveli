<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Schema;

if (Schema::hasColumn('user_roles', 'expires_at')) {
    Schema::table('user_roles', function ($table) {
        $table->dropColumn('expires_at');
    });
    echo "Column 'expires_at' dropped from user_roles table.\n";
} else {
    echo "Column 'expires_at' does not exist in user_roles table.\n";
}
