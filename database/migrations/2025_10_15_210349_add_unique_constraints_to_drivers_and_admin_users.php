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
        // Add unique constraints to drivers table
        Schema::table('drivers', function (Blueprint $table) {
            if (!Schema::hasColumn('drivers', 'email') || !$this->hasIndex('drivers', 'drivers_email_unique')) {
                $table->unique('email');
            }
            if (!Schema::hasColumn('drivers', 'phone') || !$this->hasIndex('drivers', 'drivers_phone_unique')) {
                $table->unique('phone');
            }
        });

        // Add unique constraints to admin_users table
        Schema::table('admin_users', function (Blueprint $table) {
            if (!Schema::hasColumn('admin_users', 'email') || !$this->hasIndex('admin_users', 'admin_users_email_unique')) {
                $table->unique('email');
            }
            if (!Schema::hasColumn('admin_users', 'phone') || !$this->hasIndex('admin_users', 'admin_users_phone_unique')) {
                $table->unique('phone');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Remove unique constraints from drivers table
        Schema::table('drivers', function (Blueprint $table) {
            if ($this->hasIndex('drivers', 'drivers_email_unique')) {
                $table->dropUnique('drivers_email_unique');
            }
            if ($this->hasIndex('drivers', 'drivers_phone_unique')) {
                $table->dropUnique('drivers_phone_unique');
            }
        });

        // Remove unique constraints from admin_users table
        Schema::table('admin_users', function (Blueprint $table) {
            if ($this->hasIndex('admin_users', 'admin_users_email_unique')) {
                $table->dropUnique('admin_users_email_unique');
            }
            if ($this->hasIndex('admin_users', 'admin_users_phone_unique')) {
                $table->dropUnique('admin_users_phone_unique');
            }
        });
    }

    /**
     * Check if index exists
     */
    private function hasIndex($table, $indexName)
    {
        $indexes = Schema::getConnection()
            ->getDoctrineSchemaManager()
            ->listTableIndexes($table);

        return isset($indexes[$indexName]);
    }
};
