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
        Schema::table('countries', function (Blueprint $table) {
            $table->string('iso_code_2', 2)->nullable()->after('name');
            $table->string('iso_code_3', 3)->nullable()->after('iso_code_2');
            $table->string('phone_code', 10)->nullable()->after('iso_code_3');
            $table->string('currency_code', 3)->nullable()->after('phone_code');
            $table->string('currency_symbol', 10)->nullable()->after('currency_code');
            $table->boolean('is_active')->default(true)->after('currency_symbol');
        });

        // Insert some basic countries data
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
