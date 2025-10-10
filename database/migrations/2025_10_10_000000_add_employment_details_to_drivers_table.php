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
        Schema::table('drivers', function (Blueprint $table) {
            if (!Schema::hasColumn('drivers', 'current_employer')) {
                $table->string('current_employer')->nullable();
            }
            if (!Schema::hasColumn('drivers', 'employment_start_date')) {
                $table->date('employment_start_date')->nullable();
            }
            $table->boolean('is_working')->default(false)->after('current_employer');
            $table->string('previous_workplace')->nullable()->after('is_working');
            $table->string('previous_work_id_record')->nullable()->after('previous_workplace');
            $table->text('reason_stopped_working')->nullable()->after('previous_work_id_record');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('drivers', function (Blueprint $table) {
            $table->dropColumn(['is_working', 'previous_workplace', 'previous_work_id_record', 'reason_stopped_working']);
        });
    }
};
