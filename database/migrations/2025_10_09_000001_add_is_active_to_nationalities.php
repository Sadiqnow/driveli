<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('nationalities')) {
            return;
        }

        if (!Schema::hasColumn('nationalities', 'is_active')) {
            Schema::table('nationalities', function (Blueprint $table) {
                $table->boolean('is_active')->default(true)->after('name');
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('nationalities') && Schema::hasColumn('nationalities', 'is_active')) {
            Schema::table('nationalities', function (Blueprint $table) {
                $table->dropColumn('is_active');
            });
        }
    }
};
