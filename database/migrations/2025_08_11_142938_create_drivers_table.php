<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Add is_available column to existing drivers table
        if (Schema::hasTable('drivers')) {
            Schema::table('drivers', function (Blueprint $table) {
                if (!Schema::hasColumn('drivers', 'is_available')) {
                    $table->boolean('is_available')->default(true)->after('status');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('drivers')) {
            Schema::table('drivers', function (Blueprint $table) {
                if (Schema::hasColumn('drivers', 'is_available')) {
                    $table->dropColumn('is_available');
                }
            });
        }
    }
};
