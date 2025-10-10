<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('admin_users')) {
            Schema::create('admin_users', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('name')->nullable();
                $table->string('email')->unique()->nullable();
                $table->string('password')->nullable();
                $table->string('phone')->nullable();
                $table->string('role')->nullable()->default('Admin');
                $table->string('status')->nullable()->default('Active');
                $table->boolean('is_active')->default(true);
                $table->timestamp('last_login_at')->nullable();
                $table->string('last_login_ip')->nullable();
                $table->rememberToken();
                $table->timestamps();
                $table->softDeletes();
            });
        } else {
            // If table exists, ensure role column exists
            if (!Schema::hasColumn('admin_users', 'role')) {
                Schema::table('admin_users', function (Blueprint $table) {
                    $table->string('role')->nullable()->default('Admin')->after('phone');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable('admin_users')) {
            Schema::dropIfExists('admin_users');
        }
    }
};
