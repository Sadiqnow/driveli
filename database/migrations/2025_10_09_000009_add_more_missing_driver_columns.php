<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMoreMissingDriverColumns extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('drivers')) {
            return;
        }

        Schema::table('drivers', function (Blueprint $table) {
            if (!Schema::hasColumn('drivers', 'registered_at')) {
                $table->dateTime('registered_at')->nullable()->after('is_active');
            }

            if (!Schema::hasColumn('drivers', 'verification_notes')) {
                $table->text('verification_notes')->nullable()->after('verification_status');
            }

            if (!Schema::hasColumn('drivers', 'kyc_status')) {
                $table->string('kyc_status', 50)->nullable()->after('verification_notes');
            }

            if (!Schema::hasColumn('drivers', 'kyc_step')) {
                $table->integer('kyc_step')->nullable()->after('kyc_status');
            }

            if (!Schema::hasColumn('drivers', 'kyc_retry_count')) {
                $table->integer('kyc_retry_count')->default(0)->after('kyc_step');
            }

            if (!Schema::hasColumn('drivers', 'kyc_rejection_reason')) {
                $table->string('kyc_rejection_reason')->nullable()->after('kyc_retry_count');
            }
        });
    }

    public function down()
    {
        if (!Schema::hasTable('drivers')) {
            return;
        }

        Schema::table('drivers', function (Blueprint $table) {
            $cols = ['kyc_rejection_reason','kyc_retry_count','kyc_step','kyc_status','verification_notes','registered_at'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('drivers', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
}
