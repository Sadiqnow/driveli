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
        Schema::table('driver_documents', function (Blueprint $table) {
            // KYC timestamps and data moved from drivers table
            $table->timestamp('kyc_step_1_completed_at')->nullable()->after('verified_at');
            $table->timestamp('kyc_step_2_completed_at')->nullable()->after('kyc_step_1_completed_at');
            $table->timestamp('kyc_step_3_completed_at')->nullable()->after('kyc_step_2_completed_at');
            $table->timestamp('kyc_completed_at')->nullable()->after('kyc_step_3_completed_at');
            $table->timestamp('kyc_submitted_at')->nullable()->after('kyc_completed_at');
            $table->timestamp('kyc_reviewed_at')->nullable()->after('kyc_submitted_at');
            $table->unsignedBigInteger('kyc_reviewed_by')->nullable()->after('kyc_reviewed_at');
            $table->json('kyc_step_data')->nullable()->after('kyc_reviewed_by');

            // OCR verification data moved from drivers table
            $table->enum('ocr_verification_status', ['pending', 'passed', 'failed'])->default('pending')->after('kyc_step_data');
            $table->text('ocr_verification_notes')->nullable()->after('ocr_verification_status');
            $table->json('nin_verification_data')->nullable()->after('ocr_verification_notes');
            $table->timestamp('nin_verified_at')->nullable()->after('nin_verification_data');
            $table->decimal('nin_ocr_match_score', 5, 2)->nullable()->after('nin_verified_at');
            $table->json('frsc_verification_data')->nullable()->after('nin_ocr_match_score');
            $table->timestamp('frsc_verified_at')->nullable()->after('frsc_verification_data');
            $table->decimal('frsc_ocr_match_score', 5, 2)->nullable()->after('frsc_verified_at');

            // Profile data moved from drivers table
            $table->string('profile_picture')->nullable()->after('frsc_ocr_match_score');
            $table->string('passport_photograph')->nullable()->after('profile_picture');
            $table->timestamp('registered_at')->nullable()->after('passport_photograph');
            $table->timestamp('last_active_at')->nullable()->after('registered_at');

            // Foreign key for kyc_reviewed_by
            $table->foreign('kyc_reviewed_by')->references('id')->on('admin_users')->onDelete('set null');

            // Indexes
            $table->index(['ocr_verification_status']);
            $table->index(['kyc_reviewed_by']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('driver_documents', function (Blueprint $table) {
            $table->dropForeign(['kyc_reviewed_by']);
            $table->dropIndex(['ocr_verification_status']);
            $table->dropIndex(['kyc_reviewed_by']);
            $table->dropColumn([
                'kyc_step_1_completed_at',
                'kyc_step_2_completed_at',
                'kyc_step_3_completed_at',
                'kyc_completed_at',
                'kyc_submitted_at',
                'kyc_reviewed_at',
                'kyc_reviewed_by',
                'kyc_step_data',
                'ocr_verification_status',
                'ocr_verification_notes',
                'nin_verification_data',
                'nin_verified_at',
                'nin_ocr_match_score',
                'frsc_verification_data',
                'frsc_verified_at',
                'frsc_ocr_match_score',
                'profile_picture',
                'passport_photograph',
                'registered_at',
                'last_active_at'
            ]);
        });
    }
};
