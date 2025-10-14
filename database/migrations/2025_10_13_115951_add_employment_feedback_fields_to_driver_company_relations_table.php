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
        Schema::table('driver_company_relations', function (Blueprint $table) {
            $table->date('employment_start_date')->nullable()->after('status');
            $table->date('employment_end_date')->nullable()->after('employment_start_date');
            $table->enum('performance_rating', ['excellent', 'good', 'average', 'poor', 'very_poor'])->nullable()->after('employment_end_date');
            $table->text('reason_for_leaving')->nullable()->after('performance_rating');
            $table->text('feedback_notes')->nullable()->after('reason_for_leaving');
            $table->string('feedback_token', 64)->nullable()->unique()->after('feedback_notes');
            $table->timestamp('feedback_requested_at')->nullable()->after('feedback_token');
            $table->timestamp('feedback_submitted_at')->nullable()->after('feedback_requested_at');
            $table->unsignedBigInteger('feedback_requested_by')->nullable()->after('feedback_submitted_at');
            $table->boolean('is_flagged')->default(false)->after('feedback_requested_by');
            $table->text('flag_reason')->nullable()->after('is_flagged');

            $table->foreign('feedback_requested_by')->references('id')->on('admin_users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('driver_company_relations', function (Blueprint $table) {
            $table->dropForeign(['feedback_requested_by']);
            $table->dropColumn([
                'employment_start_date',
                'employment_end_date',
                'performance_rating',
                'reason_for_leaving',
                'feedback_notes',
                'feedback_token',
                'feedback_requested_at',
                'feedback_submitted_at',
                'feedback_requested_by',
                'is_flagged',
                'flag_reason'
            ]);
        });
    }
};
