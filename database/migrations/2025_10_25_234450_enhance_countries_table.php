<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Check if columns already exist before adding them
        $columnsToAdd = [];
        if (!Schema::hasColumn('countries', 'iso_code_2')) {
            $columnsToAdd[] = function (Blueprint $table) {
                $table->string('iso_code_2', 2)->nullable()->after('name');
            };
        }
        if (!Schema::hasColumn('countries', 'iso_code_3')) {
            $columnsToAdd[] = function (Blueprint $table) {
                $table->string('iso_code_3', 3)->nullable()->after('iso_code_2');
            };
        }
        if (!Schema::hasColumn('countries', 'phone_code')) {
            $columnsToAdd[] = function (Blueprint $table) {
                $table->string('phone_code', 10)->nullable()->after('iso_code_3');
            };
        }
        if (!Schema::hasColumn('countries', 'currency_code')) {
            $columnsToAdd[] = function (Blueprint $table) {
                $table->string('currency_code', 3)->nullable()->after('phone_code');
            };
        }
        if (!Schema::hasColumn('countries', 'currency_symbol')) {
            $columnsToAdd[] = function (Blueprint $table) {
                $table->string('currency_symbol', 10)->nullable()->after('currency_code');
            };
        }
        if (!Schema::hasColumn('countries', 'is_active')) {
            $columnsToAdd[] = function (Blueprint $table) {
                $table->boolean('is_active')->default(true)->after('currency_symbol');
            };
        }

        if (!empty($columnsToAdd)) {
            Schema::table('countries', function (Blueprint $table) use ($columnsToAdd) {
                foreach ($columnsToAdd as $addColumn) {
                    $addColumn($table);
                }
            });
        }

        // Insert some basic countries data only if table is empty
        if (DB::table('countries')->count() == 0) {
            DB::table('countries')->insert([
                [
                    'name' => 'Nigeria',
                    'iso_code_2' => 'NG',
                    'iso_code_3' => 'NGA',
                    'phone_code' => '+234',
                    'currency_code' => 'NGN',
                    'currency_symbol' => '₦',
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'United States',
                    'iso_code_2' => 'US',
                    'iso_code_3' => 'USA',
                    'phone_code' => '+1',
                    'currency_code' => 'USD',
                    'currency_symbol' => '$',
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'United Kingdom',
                    'iso_code_2' => 'GB',
                    'iso_code_3' => 'GBR',
                    'phone_code' => '+44',
                    'currency_code' => 'GBP',
                    'currency_symbol' => '£',
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Canada',
                    'iso_code_2' => 'CA',
                    'iso_code_3' => 'CAN',
                    'phone_code' => '+1',
                    'currency_code' => 'CAD',
                    'currency_symbol' => 'C$',
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'Ghana',
                    'iso_code_2' => 'GH',
                    'iso_code_3' => 'GHA',
                    'phone_code' => '+233',
                    'currency_code' => 'GHS',
                    'currency_symbol' => 'GH₵',
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('countries', function (Blueprint $table) {
            $table->dropColumn(['iso_code_2', 'iso_code_3', 'phone_code', 'currency_code', 'currency_symbol', 'is_active']);
        });
    }
};
