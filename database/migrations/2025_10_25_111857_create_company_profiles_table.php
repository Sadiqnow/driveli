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
        Schema::create('company_profiles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('company_id');
            $table->text('mission_statement')->nullable();
            $table->text('vision_statement')->nullable();
            $table->text('core_values')->nullable();
            $table->json('social_media_links')->nullable();
            $table->string('facebook_url')->nullable();
            $table->string('twitter_url')->nullable();
            $table->string('linkedin_url')->nullable();
            $table->string('instagram_url')->nullable();
            $table->text('company_history')->nullable();
            $table->json('certifications')->nullable();
            $table->json('awards')->nullable();
            $table->decimal('employee_count', 8, 0)->nullable();
            $table->decimal('annual_revenue', 15, 2)->nullable();
            $table->string('headquarters_location')->nullable();
            $table->json('branch_locations')->nullable();
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->unique('company_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('company_profiles');
    }
};
