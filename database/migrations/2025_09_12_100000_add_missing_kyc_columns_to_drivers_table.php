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
            // Check and add KYC columns if they don't exist
            if (!Schema::hasColumn('drivers', 'kyc_status')) {
                $table->string('kyc_status', 50)->default('not_started');
            }
            
            if (!Schema::hasColumn('drivers', 'kyc_step')) {
                $table->integer('kyc_step')->default(0);
            }
            
            if (!Schema::hasColumn('drivers', 'kyc_step_data')) {
                $table->json('kyc_step_data')->nullable();
            }
            
            if (!Schema::hasColumn('drivers', 'kyc_rejection_reason')) {
                $table->text('kyc_rejection_reason')->nullable();
            }
            
            // Add other columns that might be missing from the Driver model fillable array
            if (!Schema::hasColumn('drivers', 'driver_id')) {
                $table->string('driver_id')->unique();
            }
            
            if (!Schema::hasColumn('drivers', 'middle_name')) {
                $table->string('middle_name')->nullable();
            }
            
            if (!Schema::hasColumn('drivers', 'nickname')) {
                $table->string('nickname')->nullable();
            }
            
            if (!Schema::hasColumn('drivers', 'phone_2')) {
                $table->string('phone_2')->nullable();
            }
            
            if (!Schema::hasColumn('drivers', 'religion')) {
                $table->string('religion')->nullable();
            }
            
            if (!Schema::hasColumn('drivers', 'blood_group')) {
                $table->string('blood_group', 10)->nullable();
            }
            
            if (!Schema::hasColumn('drivers', 'height_meters')) {
                $table->decimal('height_meters', 3, 2)->nullable();
            }
            
            if (!Schema::hasColumn('drivers', 'disability_status')) {
                $table->string('disability_status')->default('None');
            }
            
            if (!Schema::hasColumn('drivers', 'nationality_id')) {
                $table->unsignedBigInteger('nationality_id')->nullable();
            }
            
            if (!Schema::hasColumn('drivers', 'state_of_origin_id')) {
                $table->unsignedBigInteger('state_of_origin_id')->nullable();
            }
            
            if (!Schema::hasColumn('drivers', 'lga_of_origin_id')) {
                $table->unsignedBigInteger('lga_of_origin_id')->nullable();
            }
            
            if (!Schema::hasColumn('drivers', 'profile_picture')) {
                $table->string('profile_picture')->nullable();
            }
            
            if (!Schema::hasColumn('drivers', 'nin_number')) {
                $table->string('nin_number', 11)->nullable();
            }
            
            if (!Schema::hasColumn('drivers', 'nin_verification_data')) {
                $table->json('nin_verification_data')->nullable();
            }
            
            if (!Schema::hasColumn('drivers', 'license_number')) {
                $table->string('license_number')->nullable();
            }
            
            if (!Schema::hasColumn('drivers', 'license_class')) {
                $table->string('license_class')->nullable();
            }
            
            if (!Schema::hasColumn('drivers', 'license_issue_date')) {
                $table->date('license_issue_date')->nullable();
            }
            
            if (!Schema::hasColumn('drivers', 'license_expiry_date')) {
                $table->date('license_expiry_date')->nullable();
            }
            
            if (!Schema::hasColumn('drivers', 'status')) {
                $table->string('status', 50)->default('inactive');
            }
            
            if (!Schema::hasColumn('drivers', 'verification_status')) {
                $table->string('verification_status', 50)->default('pending');
            }
            
            if (!Schema::hasColumn('drivers', 'is_active')) {
                $table->boolean('is_active')->default(true);
            }
            
            if (!Schema::hasColumn('drivers', 'ocr_verification_status')) {
                $table->string('ocr_verification_status')->nullable();
            }
            
            if (!Schema::hasColumn('drivers', 'ocr_verification_notes')) {
                $table->text('ocr_verification_notes')->nullable();
            }
            
            if (!Schema::hasColumn('drivers', 'ocr_match_scores')) {
                $table->json('ocr_match_scores')->nullable();
            }
            
            if (!Schema::hasColumn('drivers', 'profile_completion_percentage')) {
                $table->integer('profile_completion_percentage')->default(0);
            }
            
            if (!Schema::hasColumn('drivers', 'registration_source')) {
                $table->string('registration_source')->default('web');
            }
            
            if (!Schema::hasColumn('drivers', 'registration_ip')) {
                $table->string('registration_ip')->nullable();
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
            $columns = [
                'kyc_status', 'kyc_step', 'kyc_step_data', 'kyc_rejection_reason',
                'middle_name', 'nickname', 'phone_2', 'religion', 'blood_group',
                'height_meters', 'disability_status', 'nationality_id', 
                'state_of_origin_id', 'lga_of_origin_id', 'profile_picture',
                'nin_number', 'nin_verification_data', 'license_class',
                'license_issue_date', 'ocr_verification_status', 'ocr_verification_notes',
                'ocr_match_scores', 'profile_completion_percentage', 
                'registration_source', 'registration_ip'
            ];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('drivers', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};