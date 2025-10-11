<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('companies')) {
            Schema::create('companies', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('company_id')->unique();
                $table->string('registration_number')->nullable();
                $table->string('tax_id')->nullable();
                $table->string('email');
                $table->string('phone');
                $table->string('website')->nullable();
                $table->text('address');
                $table->string('state');
                $table->string('lga')->nullable();
                $table->string('postal_code')->nullable();
                $table->enum('industry', ['Manufacturing','Oil & Gas','Construction','Agriculture','Mining','Food & Beverages','Logistics','Other'])->nullable();
                $table->enum('company_size', ['1-10','11-50','51-200','201-1000','1000+'])->nullable();
                $table->text('description')->nullable();
                $table->string('contact_person_name');
                $table->string('contact_person_title')->nullable();
                $table->string('contact_person_phone');
                $table->string('contact_person_email');
                $table->decimal('default_commission_rate', 5, 2)->default(15.00);
                $table->enum('payment_terms', ['Immediate','7 days','14 days','30 days'])->default('7 days');
                $table->json('preferred_regions')->nullable();
                $table->json('vehicle_types_needed')->nullable();
                $table->enum('status', ['Active','Inactive','Suspended'])->default('Active');
                $table->enum('verification_status', ['Pending','Verified','Rejected'])->default('Pending');
                $table->timestamp('verified_at')->nullable();
                $table->unsignedBigInteger('verified_by')->nullable();
                $table->string('logo')->nullable();
                $table->string('registration_certificate')->nullable();
                $table->string('tax_certificate')->nullable();
                $table->json('additional_documents')->nullable();
                $table->integer('total_requests')->default(0);
                $table->integer('fulfilled_requests')->default(0);
                $table->decimal('total_amount_paid', 12, 2)->default(0.00);
                $table->decimal('average_rating', 3, 2)->default(0.00);
                $table->timestamps();
                $table->softDeletes();

                // Indexes
                // company_id already declared unique above; avoid duplicate index names
                $table->unique('email');
                $table->index('status');
                $table->index('verification_status');
                $table->index('state');
                $table->index('company_size');
                $table->index('registration_number');
                $table->index('phone');
                $table->index('created_at');
            });

            // defensive FK: only add if admin_users table exists
            try {
                if (Schema::hasTable('companies') && Schema::hasTable('admin_users')) {
                    Schema::table('companies', function (Blueprint $table) {
                        $table->foreign('verified_by')->references('id')->on('admin_users');
                    });
                }
            } catch (\Exception $e) {
                // ignore foreign key creation errors to keep migration safe
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
