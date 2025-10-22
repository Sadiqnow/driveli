<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Schema;

if (Schema::hasColumn('roles', 'parent_id')) {
    Schema::table('roles', function ($table) {
        $table->dropColumn('parent_id');
    });
    echo "Column 'parent_id' dropped from roles table.\n";
} else {
    echo "Column 'parent_id' does not exist in roles table.\n";
}
