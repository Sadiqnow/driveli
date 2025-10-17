<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Create new normalized drivers table
        Schema::create('drivers_normalized', function (Blueprint $table) {
            // Primary Key
            $table->id();

            // Core Identification
            $table->string('driver_id')->unique();
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('surname');
            $table->string('email')->nullable()->unique();
            $table->string('phone')->unique();
            $table->string('phone_2')->nullable();

            // Authentication
            $table->string('password')->nullable();
            $table->string('remember_token', 100)->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('phone_verified_at')->nullable();
            $table->string('phone_verification_status')->nullable();
            $table->string('email_verification_status')->nullable();

            // Personal Information
            $table->date('date_of_birth')->nullable();
            $table->string('gender')->nullable();
            $table->string('religion')->nullable();
            $table->string('blood_group')->nullable();
            $table->decimal('height_meters', 5, 2)->nullable();
            $table->string('disability_status')->nullable();
            $table->unsignedBigInteger('nationality_id')->nullable();

            // Professional Information
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

            // Vehicle Information
            $table->boolean('has_vehicle')->nullable();
            $table->string('vehicle_type')->nullable();
            $table->integer('vehicle_year')->nullable();

            // Banking Information
            $table->unsignedBigInteger('bank_id')->nullable();
            $table->string('account_number')->nullable();
            $table->string('account_name')->nullable();
            $table->string('bvn_number')->nullable();

            // Work Preferences
            $table->string('preferred_work_location')->nullable();
            $table->boolean('available_for_night_shifts')->nullable();
            $table->boolean('available_for_weekend_work')->nullable();

            // Emergency Contact
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->string('emergency_contact_relationship')->nullable();

            // System Status
            $table->enum('status', ['active', 'inactive', 'suspended', 'blocked'])->default('active');
            $table->enum('verification_status', ['pending', 'verified', 'rejected', 'reviewing'])->default('pending');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_available')->default(true);
            $table->timestamp('registered_at')->nullable();
            $table->timestamp('last_active_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->unsignedBigInteger('verified_by')->nullable();
            $table->text('verification_notes')->nullable();

            // KYC Fields
            $table->string('kyc_status')->default('pending');
            $table->integer('kyc_step')->nullable();
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

            // OCR Verification
            $table->enum('ocr_verification_status', ['pending', 'passed', 'failed'])->default('pending');
            $table->text('ocr_verification_notes')->nullable();
            $table->json('nin_verification_data')->nullable();
            $table->timestamp('nin_verified_at')->nullable();
            $table->decimal('nin_ocr_match_score', 5, 2)->nullable();
            $table->json('frsc_verification_data')->nullable();
            $table->timestamp('frsc_verified_at')->nullable();
            $table->decimal('frsc_ocr_match_score', 5, 2)->nullable();

            // Profile and Completion
            $table->string('profile_picture')->nullable();
            $table->string('passport_photograph')->nullable();
            $table->string('nin_number', 11)->nullable();
            $table->decimal('profile_completion_percentage', 5, 2)->nullable();
            $table->string('registration_source')->nullable();
            $table->string('registration_ip', 45)->nullable();

            // Audit Fields
            $table->unsignedBigInteger('created_by_admin_id')->nullable();

            // Timestamps
            $table->timestamps();
            $table->softDeletes();

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

    public function down(): void
    {
        Schema::dropIfExists('drivers_normalized');
    }
};
