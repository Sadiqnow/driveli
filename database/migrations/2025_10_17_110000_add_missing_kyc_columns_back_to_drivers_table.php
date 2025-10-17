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
        Schema::table('drivers', function (Blueprint $table) {
            // Add back the KYC columns that were removed but are still needed
            if (!Schema::hasColumn('drivers', 'kyc_step')) {
                $table->enum('kyc_step', ['not_started', 'step_1', 'step_2', 'step_3', 'completed'])->default('not_started')->after('kyc_status');
            }

            if (!Schema::hasColumn('drivers', 'kyc_step_data')) {
                $table->json('kyc_step_data')->nullable()->after('kyc_step');
            }

            if (!Schema::hasColumn('drivers', 'kyc_step_1_completed_at')) {
                $table->timestamp('kyc_step_1_completed_at')->nullable()->after('kyc_step_data');
            }

            if (!Schema::hasColumn('drivers', 'kyc_step_2_completed_at')) {
                $table->timestamp('kyc_step_2_completed_at')->nullable()->after('kyc_step_1_completed_at');
            }

            if (!Schema::hasColumn('drivers', 'kyc_step_3_completed_at')) {
                $table->timestamp('kyc_step_3_completed_at')->nullable()->after('kyc_step_2_completed_at');
            }

            if (!Schema::hasColumn('drivers', 'kyc_completed_at')) {
                $table->timestamp('kyc_completed_at')->nullable()->after('kyc_step_3_completed_at');
            }

            if (!Schema::hasColumn('drivers', 'kyc_submitted_at')) {
                $table->timestamp('kyc_submitted_at')->nullable()->after('kyc_completed_at');
            }

            if (!Schema::hasColumn('drivers', 'kyc_reviewed_at')) {
                $table->timestamp('kyc_reviewed_at')->nullable()->after('kyc_submitted_at');
            }

            if (!Schema::hasColumn('drivers', 'kyc_reviewed_by')) {
                $table->unsignedBigInteger('kyc_reviewed_by')->nullable()->after('kyc_reviewed_at');
            }

            if (!Schema::hasColumn('drivers', 'kyc_rejection_reason')) {
                $table->text('kyc_rejection_reason')->nullable()->after('kyc_retry_count');
            }

            if (!Schema::hasColumn('drivers', 'kyc_submission_ip')) {
                $table->string('kyc_submission_ip', 45)->nullable()->after('kyc_rejection_reason');
            }

            if (!Schema::hasColumn('drivers', 'kyc_user_agent')) {
                $table->string('kyc_user_agent', 500)->nullable()->after('kyc_submission_ip');
            }

            if (!Schema::hasColumn('drivers', 'kyc_last_activity_at')) {
                $table->timestamp('kyc_last_activity_at')->nullable()->after('kyc_user_agent');
            }

            // Add foreign key for kyc_reviewed_by
            $table->foreign('kyc_reviewed_by')->references('id')->on('admin_users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('drivers', function (Blueprint $table) {
            // Drop foreign key first
            $table->dropForeign(['kyc_reviewed_by']);

            // Drop the columns
            $columnsToDrop = [
                'kyc_step', 'kyc_step_data', 'kyc_step_1_completed_at', 'kyc_step_2_completed_at',
                'kyc_step_3_completed_at', 'kyc_completed_at', 'kyc_submitted_at', 'kyc_reviewed_at',
                'kyc_reviewed_by', 'kyc_rejection_reason', 'kyc_submission_ip', 'kyc_user_agent',
                'kyc_last_activity_at'
            ];

            foreach ($columnsToDrop as $column) {
                if (Schema::hasColumn('drivers', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
