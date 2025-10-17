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
        Schema::table('driver_next_of_kin', function (Blueprint $table) {
            // Personal information fields moved from drivers table
            $table->date('date_of_birth')->nullable()->after('relationship');
            $table->string('gender')->nullable()->after('date_of_birth');
            $table->string('religion')->nullable()->after('gender');
            $table->string('blood_group')->nullable()->after('religion');
            $table->decimal('height_meters', 5, 2)->nullable()->after('blood_group');
            $table->string('disability_status')->nullable()->after('height_meters');
            $table->unsignedBigInteger('nationality_id')->nullable()->after('disability_status');
            $table->string('nin_number', 11)->nullable()->after('nationality_id');

            // Foreign key for nationality
            $table->foreign('nationality_id')->references('id')->on('nationalities')->onDelete('set null');

            // Indexes
            $table->index(['nationality_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('driver_next_of_kin', function (Blueprint $table) {
            $table->dropForeign(['nationality_id']);
            $table->dropIndex(['nationality_id']);
            $table->dropColumn([
                'date_of_birth',
                'gender',
                'religion',
                'blood_group',
                'height_meters',
                'disability_status',
                'nationality_id',
                'nin_number'
            ]);
        });
    }
};
