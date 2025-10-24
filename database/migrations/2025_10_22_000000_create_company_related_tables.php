<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create company_verifications table
        if (!Schema::hasTable('company_verifications')) {
            Schema::create('company_verifications', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('company_id');
                $table->string('verification_type', 50);
                $table->enum('status', ['pending', 'under_review', 'approved', 'rejected'])->default('pending');
                $table->json('submitted_documents')->nullable();
                $table->timestamp('verified_at')->nullable();
                $table->unsignedBigInteger('verified_by')->nullable();
                $table->text('rejection_reason')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
                $table->foreign('verified_by')->references('id')->on('admin_users')->onDelete('set null');
                $table->index(['company_id', 'status']);
                $table->index('verification_type');
            });
        }

        // Create company_request_templates table
        if (!Schema::hasTable('company_request_templates')) {
            Schema::create('company_request_templates', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('company_id');
                $table->string('name');
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->unsignedBigInteger('created_by');
                $table->timestamps();

                $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
                $table->foreign('created_by')->references('id')->on('admin_users')->onDelete('cascade');
                $table->index(['company_id', 'is_active']);
                $table->index('name');
            });
        }

        // Create company_request_fields table
        if (!Schema::hasTable('company_request_fields')) {
            Schema::create('company_request_fields', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('template_id');
                $table->string('field_name');
                $table->string('field_type', 50);
                $table->string('label');
                $table->boolean('is_required')->default(false);
                $table->json('field_options')->nullable();
                $table->integer('display_order')->default(0);
                $table->json('validation_rules')->nullable();
                $table->timestamps();

                $table->foreign('template_id')->references('id')->on('company_request_templates')->onDelete('cascade');
                $table->index(['template_id', 'display_order']);
                $table->index('field_name');
            });
        }

        // Insert sample data for company_verifications
        if (!Schema::hasTable('company_verifications') || DB::table('company_verifications')->count() == 0) {
            $verificationsData = [];
            if (DB::table('companies')->where('id', 1)->exists() && DB::table('admin_users')->where('id', 1)->exists()) {
                $verificationsData[] = [
                    'company_id' => 1,
                    'verification_type' => 'business_registration',
                    'status' => 'approved',
                    'submitted_documents' => json_encode(['registration_cert.pdf', 'tax_cert.pdf']),
                    'verified_at' => now(),
                    'verified_by' => 1,
                    'rejection_reason' => null,
                    'notes' => 'All documents verified successfully',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            if (DB::table('companies')->where('id', 2)->exists()) {
                $verificationsData[] = [
                    'company_id' => 2,
                    'verification_type' => 'tax_certificate',
                    'status' => 'pending',
                    'submitted_documents' => json_encode(['tax_cert.pdf']),
                    'verified_at' => null,
                    'verified_by' => null,
                    'rejection_reason' => null,
                    'notes' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            if (DB::table('companies')->where('id', 3)->exists() && DB::table('admin_users')->where('id', 1)->exists()) {
                $verificationsData[] = [
                    'company_id' => 3,
                    'verification_type' => 'business_registration',
                    'status' => 'rejected',
                    'submitted_documents' => json_encode(['expired_cert.pdf']),
                    'verified_at' => now(),
                    'verified_by' => 1,
                    'rejection_reason' => 'Certificate expired',
                    'notes' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            if (DB::table('companies')->where('id', 4)->exists()) {
                $verificationsData[] = [
                    'company_id' => 4,
                    'verification_type' => 'identity_verification',
                    'status' => 'under_review',
                    'submitted_documents' => json_encode(['id_card.pdf', 'passport.pdf']),
                    'verified_at' => null,
                    'verified_by' => null,
                    'rejection_reason' => null,
                    'notes' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            if (DB::table('companies')->where('id', 5)->exists() && DB::table('admin_users')->where('id', 1)->exists()) {
                $verificationsData[] = [
                    'company_id' => 5,
                    'verification_type' => 'business_registration',
                    'status' => 'approved',
                    'submitted_documents' => json_encode(['registration_cert.pdf']),
                    'verified_at' => now()->subDays(5),
                    'verified_by' => 1,
                    'rejection_reason' => null,
                    'notes' => 'Verified via third-party service',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            if (!empty($verificationsData)) {
                DB::table('company_verifications')->insert($verificationsData);
            }
        }

        // Insert sample data for company_request_templates
        $templatesData = [];
        if (DB::table('companies')->where('id', 1)->exists() && DB::table('admin_users')->where('id', 1)->exists()) {
            $templatesData[] = [
                'company_id' => 1,
                'name' => 'Standard Driver Request',
                'description' => 'Template for standard driver assignment requests',
                'is_active' => true,
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        if (DB::table('companies')->where('id', 2)->exists() && DB::table('admin_users')->where('id', 1)->exists()) {
            $templatesData[] = [
                'company_id' => 2,
                'name' => 'Urgent Delivery Request',
                'description' => 'Template for urgent delivery assignments',
                'is_active' => true,
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        if (DB::table('companies')->where('id', 3)->exists() && DB::table('admin_users')->where('id', 1)->exists()) {
            $templatesData[] = [
                'company_id' => 3,
                'name' => 'Long-term Contract',
                'description' => 'Template for long-term driver contracts',
                'is_active' => true,
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        if (DB::table('companies')->where('id', 4)->exists() && DB::table('admin_users')->where('id', 1)->exists()) {
            $templatesData[] = [
                'company_id' => 4,
                'name' => 'Part-time Driver',
                'description' => 'Template for part-time driver positions',
                'is_active' => false,
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        if (DB::table('companies')->where('id', 5)->exists() && DB::table('admin_users')->where('id', 1)->exists()) {
            $templatesData[] = [
                'company_id' => 5,
                'name' => 'Seasonal Driver Request',
                'description' => 'Template for seasonal driver assignments',
                'is_active' => true,
                'created_by' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        if (!empty($templatesData)) {
            DB::table('company_request_templates')->insert($templatesData);
        }

        // Insert sample data for company_request_fields
        $fieldsData = [];
        if (DB::table('company_request_templates')->where('id', 1)->exists()) {
            $fieldsData[] = [
                'template_id' => 1,
                'field_name' => 'experience_years',
                'field_type' => 'number',
                'label' => 'Years of Experience',
                'is_required' => true,
                'validation_rules' => json_encode(['min' => 0, 'max' => 50]),
                'display_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            $fieldsData[] = [
                'template_id' => 1,
                'field_name' => 'vehicle_type',
                'field_type' => 'select',
                'label' => 'Preferred Vehicle Type',
                'is_required' => false,
                'field_options' => json_encode(['sedan', 'suv', 'truck', 'motorcycle']),
                'display_order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        if (DB::table('company_request_templates')->where('id', 2)->exists()) {
            $fieldsData[] = [
                'template_id' => 2,
                'field_name' => 'delivery_deadline',
                'field_type' => 'datetime',
                'label' => 'Delivery Deadline',
                'is_required' => true,
                'display_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        if (DB::table('company_request_templates')->where('id', 3)->exists()) {
            $fieldsData[] = [
                'template_id' => 3,
                'field_name' => 'contract_duration',
                'field_type' => 'select',
                'label' => 'Contract Duration',
                'is_required' => true,
                'field_options' => json_encode(['3 months', '6 months', '1 year', '2 years']),
                'display_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        if (DB::table('company_request_templates')->where('id', 4)->exists()) {
            $fieldsData[] = [
                'template_id' => 4,
                'field_name' => 'availability_hours',
                'field_type' => 'text',
                'label' => 'Available Hours',
                'is_required' => false,
                'validation_rules' => json_encode(['max_length' => 100]),
                'display_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        if (!empty($fieldsData)) {
            DB::table('company_request_fields')->insert($fieldsData);
        }


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_request_fields');
        Schema::dropIfExists('company_request_templates');
        Schema::dropIfExists('company_verifications');
    }
};
