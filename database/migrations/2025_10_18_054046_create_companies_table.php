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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('company_id')->unique();
            $table->string('registration_number')->nullable();
            $table->string('tax_id')->nullable();
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->string('website')->nullable();
            $table->text('address')->nullable();
            $table->string('state')->nullable();
            $table->string('lga')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('industry')->nullable();
            $table->string('company_size')->nullable();
            $table->text('description')->nullable();
            $table->string('contact_person_name')->nullable();
            $table->string('contact_person_title')->nullable();
            $table->string('contact_person_phone')->nullable();
            $table->string('contact_person_email')->nullable();
            $table->decimal('default_commission_rate', 5, 2)->default(0);
            $table->string('payment_terms')->nullable();
            $table->json('preferred_regions')->nullable();
            $table->json('vehicle_types_needed')->nullable();
            $table->enum('status', ['Active', 'Inactive', 'Suspended'])->default('Active');
            $table->enum('verification_status', ['Pending', 'Verified', 'Rejected'])->default('Pending');
            $table->timestamp('verified_at')->nullable();
            $table->unsignedBigInteger('verified_by')->nullable();
            $table->string('logo')->nullable();
            $table->string('registration_certificate')->nullable();
            $table->string('tax_certificate')->nullable();
            $table->json('additional_documents')->nullable();
            $table->integer('total_requests')->default(0);
            $table->integer('fulfilled_requests')->default(0);
            $table->decimal('total_amount_paid', 15, 2)->default(0);
            $table->decimal('average_rating', 3, 2)->default(0);
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->rememberToken();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('verified_by')->references('id')->on('admin_users')->onDelete('set null');
            $table->index(['status', 'verification_status']);
            $table->index('company_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('companies');
    }
};
