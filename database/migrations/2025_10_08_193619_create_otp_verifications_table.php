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
        if (!Schema::hasTable('otp_verifications')) {
            Schema::create('otp_verifications', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('driver_id');
                $table->string('verification_type'); // sms or email
                $table->string('otp_code');
                $table->timestamp('expires_at');
                $table->timestamp('verified_at')->nullable();
                $table->integer('attempts')->default(0);
                $table->timestamp('last_attempt_at')->nullable();
                $table->timestamps();

                // Indexes
                $table->index(['driver_id', 'verification_type']);
                $table->index('expires_at');
                $table->index('verified_at');
            });
        }

        // Add foreign key defensively if referenced table/column exists
        if (Schema::hasTable('otp_verifications') && Schema::hasTable('drivers')) {
            try {
                Schema::table('otp_verifications', function (Blueprint $table) {
                    if (Schema::hasColumn('otp_verifications', 'driver_id') && Schema::hasColumn('drivers', 'id')) {
                        $table->foreign('driver_id')->references('id')->on('drivers')->onDelete('cascade');
                    }
                });
            } catch (\Exception $e) {
                // ignore FK creation errors; table remains usable
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('otp_verifications');
    }
};
