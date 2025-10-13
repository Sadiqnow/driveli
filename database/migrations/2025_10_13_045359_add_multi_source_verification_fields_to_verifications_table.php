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
        Schema::table('verifications', function (Blueprint $table) {
            $table->string('verification_source')->nullable()->after('type'); // e.g., 'nimc_api', 'frsc_api', 'smile_id_sdk', 'local_face_dataset', 'immigration_api', 'internal_tracking'
            $table->json('api_response')->nullable()->after('notes'); // Store full API response
            $table->timestamp('response_timestamp')->nullable()->after('api_response'); // When the verification was performed
            $table->integer('response_time_ms')->nullable()->after('response_timestamp'); // API response time
            $table->string('external_reference_id')->nullable()->after('response_time_ms'); // External API reference ID
            $table->timestamp('expires_at')->nullable()->after('external_reference_id'); // When this verification expires
            $table->boolean('requires_reverification')->default(false)->after('expires_at'); // Flag for re-verification
            $table->timestamp('last_reverification_check')->nullable()->after('requires_reverification'); // Last time re-verification was checked
            $table->index(['verification_source', 'status']);
            $table->index(['expires_at']);
            $table->index(['requires_reverification']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('verifications', function (Blueprint $table) {
            $table->dropIndex(['verification_source', 'status']);
            $table->dropIndex(['expires_at']);
            $table->dropIndex(['requires_reverification']);
            $table->dropColumn([
                'verification_source',
                'api_response',
                'response_timestamp',
                'response_time_ms',
                'external_reference_id',
                'expires_at',
                'requires_reverification',
                'last_reverification_check'
            ]);
        });
    }
};
