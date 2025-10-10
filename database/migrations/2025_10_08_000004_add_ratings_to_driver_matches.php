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
        if (!Schema::hasTable('driver_matches')) {
            return;
        }

        Schema::table('driver_matches', function (Blueprint $table) {
            if (!Schema::hasColumn('driver_matches', 'driver_rating')) {
                // decimal(total, places)
                $table->decimal('driver_rating', 2, 1)->nullable();
            }

            if (!Schema::hasColumn('driver_matches', 'company_rating')) {
                $table->decimal('company_rating', 2, 1)->nullable();
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
        if (!Schema::hasTable('driver_matches')) {
            return;
        }

        Schema::table('driver_matches', function (Blueprint $table) {
            $drop = [];
            if (Schema::hasColumn('driver_matches', 'company_rating')) {
                $drop[] = 'company_rating';
            }
            if (Schema::hasColumn('driver_matches', 'driver_rating')) {
                $drop[] = 'driver_rating';
            }
            if (!empty($drop)) {
                $table->dropColumn($drop);
            }
        });
    }
};
