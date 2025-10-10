<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('admin_users')) {
            return;
        }

        Schema::table('admin_users', function (Blueprint $table) {
            if (!Schema::hasColumn('admin_users', 'email_verified_at')) {
                $table->timestamp('email_verified_at')->nullable()->after('email');
            }

            if (!Schema::hasColumn('admin_users', 'permissions')) {
                // JSON permissions column for flexible permission storage
                $table->json('permissions')->nullable()->after('status');
            }

            if (!Schema::hasColumn('admin_users', 'avatar')) {
                $table->string('avatar')->nullable()->after('permissions');
            }

            if (!Schema::hasColumn('admin_users', 'last_login_at')) {
                $table->timestamp('last_login_at')->nullable()->after('avatar');
            }

            if (!Schema::hasColumn('admin_users', 'last_login_ip')) {
                $table->string('last_login_ip', 45)->nullable()->after('last_login_at');
            }
        });
    }

    public function down()
    {
        if (!Schema::hasTable('admin_users')) {
            return;
        }

        Schema::table('admin_users', function (Blueprint $table) {
            if (Schema::hasColumn('admin_users', 'email_verified_at')) {
                $table->dropColumn('email_verified_at');
            }
            if (Schema::hasColumn('admin_users', 'permissions')) {
                $table->dropColumn('permissions');
            }
            if (Schema::hasColumn('admin_users', 'avatar')) {
                $table->dropColumn('avatar');
            }
            if (Schema::hasColumn('admin_users', 'last_login_at')) {
                $table->dropColumn('last_login_at');
            }
            if (Schema::hasColumn('admin_users', 'last_login_ip')) {
                $table->dropColumn('last_login_ip');
            }
        });
    }
};
