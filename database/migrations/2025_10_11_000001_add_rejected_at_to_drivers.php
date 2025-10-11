<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('drivers') && !Schema::hasColumn('drivers', 'rejected_at')) {
            Schema::table('drivers', function (Blueprint $table) {
                $table->timestamp('rejected_at')->nullable()->after('verified_at');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('drivers') && Schema::hasColumn('drivers', 'rejected_at')) {
            Schema::table('drivers', function (Blueprint $table) {
                $table->dropColumn('rejected_at');
            });
        }
    }
};