<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRememberTokenToDrivers extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('drivers')) return;

        Schema::table('drivers', function (Blueprint $table) {
            if (!Schema::hasColumn('drivers', 'remember_token')) {
                $table->string('remember_token', 100)->nullable()->after('password');
            }
        });
    }

    public function down()
    {
        if (!Schema::hasTable('drivers')) return;

        Schema::table('drivers', function (Blueprint $table) {
            if (Schema::hasColumn('drivers', 'remember_token')) {
                $table->dropColumn('remember_token');
            }
        });
    }
}
