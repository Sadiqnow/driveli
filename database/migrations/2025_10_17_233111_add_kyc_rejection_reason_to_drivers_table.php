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
            // Check if columns already exist before adding them
            if (!Schema::hasColumn('drivers', 'kyc_rejection_reason')) {
                $table->text('kyc_rejection_reason')->nullable()->after('kyc_retry_count');
            }

            if (!Schema::hasColumn('drivers', 'kyc_rejected_at')) {
                $table->timestamp('kyc_rejected_at')->nullable()->after('kyc_rejection_reason');
            }

            if (!Schema::hasColumn('drivers', 'kyc_rejected_by')) {
                $table->unsignedBigInteger('kyc_rejected_by')->nullable()->after('kyc_rejected_at');
                $table->foreign('kyc_rejected_by')->references('id')->on('admin_users')->onDelete('set null');
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
            if (Schema::hasColumn('drivers', 'kyc_rejected_by')) {
                $table->dropForeign(['kyc_rejected_by']);
            }

            $columnsToDrop = ['kyc_rejection_reason', 'kyc_rejected_at', 'kyc_rejected_by'];
            foreach ($columnsToDrop as $column) {
                if (Schema::hasColumn('drivers', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
