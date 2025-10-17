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
        Schema::table('driver_performance', function (Blueprint $table) {
            // Professional information fields moved from drivers table
            $table->string('current_employer')->nullable()->after('completion_rate');
            $table->integer('years_of_experience')->nullable()->after('current_employer');
            $table->date('employment_start_date')->nullable()->after('years_of_experience');
            $table->boolean('is_working')->nullable()->after('employment_start_date');
            $table->string('previous_company')->nullable()->after('is_working');
            $table->text('reason_stopped_working')->nullable()->after('previous_company');
            $table->string('license_number')->nullable()->after('reason_stopped_working');
            $table->string('license_class')->nullable()->after('license_number');
            $table->date('license_issue_date')->nullable()->after('license_class');
            $table->date('license_expiry_date')->nullable()->after('license_issue_date');
            $table->boolean('has_vehicle')->nullable()->after('license_expiry_date');
            $table->string('vehicle_type')->nullable()->after('has_vehicle');
            $table->integer('vehicle_year')->nullable()->after('vehicle_type');

            // Work preferences
            $table->string('preferred_work_location')->nullable()->after('vehicle_year');
            $table->boolean('available_for_night_shifts')->nullable()->after('preferred_work_location');
            $table->boolean('available_for_weekend_work')->nullable()->after('available_for_night_shifts');

            // Indexes
            $table->index(['license_number']);
            $table->index(['current_employer']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('driver_performance', function (Blueprint $table) {
            $table->dropIndex(['license_number']);
            $table->dropIndex(['current_employer']);
            $table->dropColumn([
                'current_employer',
                'years_of_experience',
                'employment_start_date',
                'is_working',
                'previous_company',
                'reason_stopped_working',
                'license_number',
                'license_class',
                'license_issue_date',
                'license_expiry_date',
                'has_vehicle',
                'vehicle_type',
                'vehicle_year',
                'preferred_work_location',
                'available_for_night_shifts',
                'available_for_weekend_work'
            ]);
        });
    }
};
