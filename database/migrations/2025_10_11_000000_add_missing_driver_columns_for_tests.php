<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('drivers')) {
            Schema::table('drivers', function (Blueprint $table) {
                if (!Schema::hasColumn('drivers', 'verified_at')) {
                    $table->timestamp('verified_at')->nullable()->after('verification_status');
                }
                if (!Schema::hasColumn('drivers', 'verification_started_at')) {
                    $table->timestamp('verification_started_at')->nullable()->after('verification_status');
                }
                if (!Schema::hasColumn('drivers', 'nin_document')) {
                    $table->string('nin_document')->nullable()->after('nin_number');
                }
                if (!Schema::hasColumn('drivers', 'license_front_image')) {
                    $table->string('license_front_image')->nullable()->after('license_number');
                }
                if (!Schema::hasColumn('drivers', 'license_back_image')) {
                    $table->string('license_back_image')->nullable()->after('license_front_image');
                }
                if (!Schema::hasColumn('drivers', 'passport_photograph')) {
                    $table->string('passport_photograph')->nullable()->after('license_back_image');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('drivers')) {
            Schema::table('drivers', function (Blueprint $table) {
                if (Schema::hasColumn('drivers', 'verified_at')) {
                    $table->dropColumn('verified_at');
                }
                if (Schema::hasColumn('drivers', 'verification_started_at')) {
                    $table->dropColumn('verification_started_at');
                }
                if (Schema::hasColumn('drivers', 'nin_document')) {
                    $table->dropColumn('nin_document');
                }
                if (Schema::hasColumn('drivers', 'license_front_image')) {
                    $table->dropColumn('license_front_image');
                }
                if (Schema::hasColumn('drivers', 'license_back_image')) {
                    $table->dropColumn('license_back_image');
                }
                if (Schema::hasColumn('drivers', 'passport_photograph')) {
                    $table->dropColumn('passport_photograph');
                }
            });
        }
    }
};