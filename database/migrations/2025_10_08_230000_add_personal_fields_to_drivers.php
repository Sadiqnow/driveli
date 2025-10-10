<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('drivers')) {
            return;
        }

        Schema::table('drivers', function (Blueprint $table) {
            if (!Schema::hasColumn('drivers', 'first_name')) {
                $table->string('first_name')->nullable();
            }
            if (!Schema::hasColumn('drivers', 'surname')) {
                $table->string('surname')->nullable();
            }
            if (!Schema::hasColumn('drivers', 'last_name')) {
                $table->string('last_name')->nullable();
            }
            if (!Schema::hasColumn('drivers', 'password')) {
                $table->string('password')->nullable();
            }
            if (!Schema::hasColumn('drivers', 'date_of_birth')) {
                $table->date('date_of_birth')->nullable();
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('drivers')) {
            return;
        }

        Schema::table('drivers', function (Blueprint $table) {
            foreach (['first_name','surname','last_name','password','date_of_birth'] as $col) {
                if (Schema::hasColumn('drivers', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
