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
        if (Schema::hasTable('drivers')) {
            return;
        }

        Schema::create('drivers', function (Blueprint $table) {
            $table->id();
            $table->string('driver_id')->unique();
            $table->string('nickname')->nullable();
            $table->string('first_name')->nullable();
            $table->string('middle_name')->nullable();
            $table->string('surname')->nullable();
            $table->string('last_name')->nullable();
            $table->string('email')->nullable()->index();
            $table->string('phone')->nullable()->index();
            $table->string('phone_2')->nullable();
            $table->timestamp('phone_verified_at')->nullable();
            $table->string('phone_verification_status')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('email_verification_status')->nullable();
            $table->string('password')->nullable();
            $table->string('remember_token')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('gender')->nullable();
            $table->string('religion')->nullable();
            $table->string('blood_group')->nullable();
            $table->decimal('height_meters', 5, 2)->nullable();
            $table->string('disability_status')->nullable();
            $table->unsignedBigInteger('nationality_id')->nullable();
            $table->unsignedBigInteger('state_of_origin')->nullable();
            $table->unsignedBigInteger('lga_of_origin')->nullable();
            $table->text('address_of_origin')->nullable();
            $table->string('profile_picture')->nullable();
            $table->string('profile_photo')->nullable();
            $table->string('nin_number')->nullable();
            $table->string('nin_document')->nullable();
            $table->string('license_number')->nullable();
            $table->string('license_class')->nullable();
            $table->date('license_expiry_date')->nullable();
            $table->string('frsc_document')->nullable();
            $table->string('license_front_image')->nullable();
            $table->string('license_back_image')->nullable();
            $table->string('passport_photograph')->nullable();
            $table->json('additional_documents')->nullable();
            $table->string('current_employer')->nullable();
            $table->integer('experience_years')->nullable();
            $table->date('employment_start_date')->nullable();
            $table->text('residence_address')->nullable();
            $table->text('residential_address')->nullable();
            $table->unsignedBigInteger('residence_state_id')->nullable();
            $table->unsignedBigInteger('residence_lga_id')->nullable();
            $table->json('vehicle_types')->nullable();
            $table->json('work_regions')->nullable();
            $table->json('special_skills')->nullable();
            $table->string('status')->default('inactive');
            $table->string('verification_status')->default('pending');
            $table->boolean('is_active')->default(true);
            $table->timestamp('registered_at')->nullable();
            $table->text('verification_notes')->nullable();
            $table->string('ocr_verification_status')->nullable();
            $table->text('ocr_verification_notes')->nullable();
            $table->json('nin_verification_data')->nullable();
            $table->timestamp('nin_verified_at')->nullable();
            $table->decimal('nin_ocr_match_score', 5, 2)->nullable();
            $table->json('frsc_verification_data')->nullable();
            $table->timestamp('frsc_verified_at')->nullable();
            $table->decimal('frsc_ocr_match_score', 5, 2)->nullable();
            $table->integer('kyc_step')->nullable();
            $table->string('kyc_status')->nullable();
            $table->json('kyc_step_data')->nullable();
            $table->timestamp('kyc_step_1_completed_at')->nullable();
            $table->timestamp('kyc_step_2_completed_at')->nullable();
            $table->timestamp('kyc_step_3_completed_at')->nullable();
            $table->timestamp('kyc_completed_at')->nullable();
            $table->timestamp('kyc_submitted_at')->nullable();
            $table->timestamp('kyc_reviewed_at')->nullable();
            $table->unsignedBigInteger('kyc_reviewed_by')->nullable();
            $table->integer('kyc_retry_count')->default(0);
            $table->text('kyc_rejection_reason')->nullable();
            $table->string('kyc_submission_ip', 45)->nullable();
            $table->string('kyc_user_agent', 500)->nullable();
            $table->timestamp('kyc_last_activity_at')->nullable();
            $table->string('full_address')->nullable();
            $table->string('city')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('driver_license_scan')->nullable();
            $table->string('national_id')->nullable();
            $table->string('passport_photo')->nullable();
            $table->string('bvn_number')->nullable();
            $table->unsignedBigInteger('created_by_admin_id')->nullable();
            $table->unsignedBigInteger('state_id')->nullable();
            $table->unsignedBigInteger('lga_id')->nullable();
            $table->integer('years_of_experience')->nullable();
            $table->string('previous_company')->nullable();
            $table->boolean('has_vehicle')->nullable();
            $table->string('vehicle_type')->nullable();
            $table->integer('vehicle_year')->nullable();
            $table->unsignedBigInteger('bank_id')->nullable();
            $table->string('account_number')->nullable();
            $table->string('account_name')->nullable();
            $table->string('bvn')->nullable();
            $table->string('preferred_work_location')->nullable();
            $table->boolean('available_for_night_shifts')->nullable();
            $table->boolean('available_for_weekend_work')->nullable();
            $table->timestamp('registration_date')->nullable();
            $table->string('drivers_license_photo_path')->nullable();
            $table->string('profile_photo_path')->nullable();
            $table->string('national_id_path')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->string('state')->nullable();
            $table->string('national_id_image')->nullable();
            $table->string('proof_of_address_path')->nullable();
            $table->string('guarantor_letter_path')->nullable();
            $table->string('vehicle_registration_path')->nullable();
            $table->string('insurance_certificate_path')->nullable();
            $table->boolean('available')->nullable();
            $table->softDeletes();
            $table->timestamps();

            // indexes - avoid duplicate index creation if they already exist
            try {
                $table->index(['email']);
            } catch (\Exception $e) {}
            try {
                $table->index(['phone']);
            } catch (\Exception $e) {}
            try {
                $table->index(['driver_id']);
            } catch (\Exception $e) {}
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('drivers');
    }
};
