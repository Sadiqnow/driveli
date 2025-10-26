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
        Schema::create('drivers_consolidated', function (Blueprint $table) {
            $table->id();

            // Basic Information
            $table->string('first_name');
            $table->string('last_name');
            $table->string('middle_name')->nullable();
            $table->string('email')->unique();
            $table->string('phone_number');
            $table->date('date_of_birth');
            $table->enum('gender', ['male', 'female', 'other']);

            // Identification
            $table->string('nin_number')->unique()->nullable();
            $table->string('bvn')->nullable();
            $table->string('drivers_license_number')->unique();
            $table->date('license_expiry_date');
            $table->string('license_class');

            // Location Information
            $table->foreignId('country_id')->constrained('countries');
            $table->foreignId('state_id')->constrained('states');
            $table->foreignId('lga_id')->constrained('local_governments');
            $table->text('address');

            // Banking Information
            $table->foreignId('bank_id')->constrained('banks');
            $table->string('account_number');
            $table->string('account_name');

            // Employment & Verification
            $table->enum('employment_status', ['employed', 'self-employed', 'unemployed'])->default('unemployed');
            $table->string('employer_name')->nullable();
            $table->string('job_title')->nullable();
            $table->decimal('monthly_income', 15, 2)->nullable();
            $table->enum('verification_status', ['pending', 'verified', 'rejected'])->default('pending');
            $table->timestamp('verified_at')->nullable();

            // Profile & Documents
            $table->string('profile_picture')->nullable();
            $table->json('documents')->nullable(); // Store paths to uploaded documents
            $table->json('kyc_data')->nullable(); // Store OCR and verification data

            // System Fields
            $table->boolean('is_active')->default(true);
            $table->softDeletes();
            $table->timestamps();

            // Indexes
            $table->index(['state_id', 'lga_id']);
            $table->index(['verification_status', 'is_active']);
            $table->index('email');
            $table->index('phone_number');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('drivers_consolidated');
    }
};
