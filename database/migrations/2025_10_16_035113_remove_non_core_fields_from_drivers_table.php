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
            // Remove non-core fields (moved to transactional tables)
            // Use dropColumnIfExists for safety
            $columnsToDrop = [
                // Personal info moved to driver_next_of_kin
                'date_of_birth', 'gender', 'religion', 'blood_group', 'height_meters',
                'disability_status', 'nationality_id', 'nin_number',

                // Professional info moved to driver_performance
                'current_employer', 'years_of_experience', 'employment_start_date', 'is_working',
                'previous_company', 'reason_stopped_working', 'license_number', 'license_class',
                'license_issue_date', 'license_expiry_date', 'has_vehicle', 'vehicle_type', 'vehicle_year',

                // Banking moved to driver_banking_details
                'bank_id', 'account_number', 'account_name', 'bvn_number',

                // Work preferences moved to driver_performance
                'preferred_work_location', 'available_for_night_shifts', 'available_for_weekend_work',

                // Emergency contact moved to driver_next_of_kin
                'emergency_contact_name', 'emergency_contact_phone', 'emergency_contact_relationship',

                // KYC timestamps moved to driver_documents
                'kyc_step', 'kyc_step_data', 'kyc_step_1_completed_at', 'kyc_step_2_completed_at',
                'kyc_step_3_completed_at', 'kyc_completed_at', 'kyc_submitted_at', 'kyc_reviewed_at',
                'kyc_reviewed_by', 'kyc_rejection_reason', 'kyc_submission_ip', 'kyc_user_agent',
                'kyc_last_activity_at',

                // OCR moved to driver_documents
                'ocr_verification_status', 'ocr_verification_notes', 'nin_verification_data',
                'nin_verified_at', 'nin_ocr_match_score', 'frsc_verification_data', 'frsc_verified_at',
                'frsc_ocr_match_score',

                // Profile moved to driver_documents
                'profile_picture', 'passport_photograph', 'registered_at', 'last_active_at',

                // Other fields
                'created_by_admin_id', 'profile_completion_percentage', 'registration_source',
                'registration_ip'
            ];

            foreach ($columnsToDrop as $column) {
                if (Schema::hasColumn('drivers', $column)) {
                    $table->dropColumn($column);
                }
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
        Schema::table('drivers', function (Blueprint $table) {
            // Add back all the removed columns (reverse migration)
            // Personal info
            $table->date('date_of_birth')->nullable();
            $table->string('gender')->nullable();
            $table->string('religion')->nullable();
            $table->string('blood_group')->nullable();
            $table->decimal('height_meters', 5, 2)->nullable();
            $table->string('disability_status')->nullable();
            $table->unsignedBigInteger('nationality_id')->nullable();
            $table->string('nin_number', 11)->nullable();

            // Professional info
            $table->string('current_employer')->nullable();
            $table->integer('years_of_experience')->nullable();
            $table->date('employment_start_date')->nullable();
            $table->boolean('is_working')->nullable();
            $table->string('previous_company')->nullable();
            $table->text('reason_stopped_working')->nullable();
            $table->string('license_number')->nullable();
            $table->string('license_class')->nullable();
            $table->date('license_issue_date')->nullable();
            $table->date('license_expiry_date')->nullable();
            $table->boolean('has_vehicle')->nullable();
            $table->string('vehicle_type')->nullable();
            $table->integer('vehicle_year')->nullable();

            // Banking
            $table->unsignedBigInteger('bank_id')->nullable();
            $table->string('account_number')->nullable();
            $table->string('account_name')->nullable();
            $table->string('bvn_number')->nullable();

            // Work preferences
            $table->string('preferred_work_location')->nullable();
            $table->boolean('available_for_night_shifts')->nullable();
            $table->boolean('available_for_weekend_work')->nullable();

            // Emergency contact
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->string('emergency_contact_relationship')->nullable();

            // KYC
            $table->integer('kyc_step')->nullable();
            $table->json('kyc_step_data')->nullable();
            $table->timestamp('kyc_step_1_completed_at')->nullable();
            $table->timestamp('kyc_step_2_completed_at')->nullable();
            $table->timestamp('kyc_step_3_completed_at')->nullable();
            $table->timestamp('kyc_completed_at')->nullable();
            $table->timestamp('kyc_submitted_at')->nullable();
            $table->timestamp('kyc_reviewed_at')->nullable();
            $table->unsignedBigInteger('kyc_reviewed_by')->nullable();
            $table->text('kyc_rejection_reason')->nullable();
            $table->string('kyc_submission_ip', 45)->nullable();
            $table->string('kyc_user_agent', 500)->nullable();
            $table->timestamp('kyc_last_activity_at')->nullable();

            // OCR
            $table->enum('ocr_verification_status', ['pending', 'passed', 'failed'])->default('pending');
            $table->text('ocr_verification_notes')->nullable();
            $table->json('nin_verification_data')->nullable();
            $table->timestamp('nin_verified_at')->nullable();
            $table->decimal('nin_ocr_match_score', 5, 2)->nullable();
            $table->json('frsc_verification_data')->nullable();
            $table->timestamp('frsc_verified_at')->nullable();
            $table->decimal('frsc_ocr_match_score', 5, 2)->nullable();

            // Profile
            $table->string('profile_picture')->nullable();
            $table->string('passport_photograph')->nullable();
            $table->timestamp('registered_at')->nullable();
            $table->timestamp('last_active_at')->nullable();

            // Other
            $table->unsignedBigInteger('created_by_admin_id')->nullable();
            $table->decimal('profile_completion_percentage', 5, 2)->nullable();
            $table->string('registration_source')->nullable();
            $table->string('registration_ip', 45)->nullable();

            // Foreign keys
            $table->foreign('nationality_id')->references('id')->on('nationalities')->onDelete('set null');
            $table->foreign('bank_id')->references('id')->on('banks')->onDelete('set null');
            $table->foreign('verified_by')->references('id')->on('admin_users')->onDelete('set null');
            $table->foreign('kyc_reviewed_by')->references('id')->on('admin_users')->onDelete('set null');
            $table->foreign('created_by_admin_id')->references('id')->on('admin_users')->onDelete('set null');

            // Indexes
            $table->index(['status', 'verification_status']);
            $table->index(['verified_at', 'verified_by']);
            $table->index(['nationality_id']);
            $table->index(['driver_id']);
            $table->index(['phone']);
            $table->index(['email']);
            $table->index(['kyc_status', 'kyc_step']);
            $table->index(['ocr_verification_status']);
            $table->index(['created_at']);
        });
    }
};
