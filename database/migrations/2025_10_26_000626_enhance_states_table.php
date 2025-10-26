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
        if (!Schema::hasColumn('states', 'state_code')) {
            $columnsToAdd[] = function (Blueprint $table) {
                $table->string('state_code', 10)->nullable()->after('name');
            };
        }
        if (!Schema::hasColumn('states', 'country_id')) {
            $columnsToAdd[] = function (Blueprint $table) {
                $table->foreignId('country_id')->nullable()->constrained('countries')->onDelete('cascade')->after('state_code');
            };
        }
        if (!Schema::hasColumn('states', 'is_active')) {
            $columnsToAdd[] = function (Blueprint $table) {
                $table->boolean('is_active')->default(true)->after('country_id');
            };
        }

        if (!empty($columnsToAdd)) {
            Schema::table('states', function (Blueprint $table) use ($columnsToAdd) {
                foreach ($columnsToAdd as $addColumn) {
                    $addColumn($table);
                }
            });
        }

        // Insert Nigerian states data only if table is empty
        if (DB::table('states')->count() == 0) {
            $nigerianStates = [
                ['name' => 'Abia', 'state_code' => 'AB'],
                ['name' => 'Adamawa', 'state_code' => 'AD'],
                ['name' => 'Akwa Ibom', 'state_code' => 'AK'],
                ['name' => 'Anambra', 'state_code' => 'AN'],
                ['name' => 'Bauchi', 'state_code' => 'BA'],
                ['name' => 'Bayelsa', 'state_code' => 'BY'],
                ['name' => 'Benue', 'state_code' => 'BE'],
                ['name' => 'Borno', 'state_code' => 'BO'],
                ['name' => 'Cross River', 'state_code' => 'CR'],
                ['name' => 'Delta', 'state_code' => 'DE'],
                ['name' => 'Ebonyi', 'state_code' => 'EB'],
                ['name' => 'Edo', 'state_code' => 'ED'],
                ['name' => 'Ekiti', 'state_code' => 'EK'],
                ['name' => 'Enugu', 'state_code' => 'EN'],
                ['name' => 'FCT', 'state_code' => 'FC'],
                ['name' => 'Gombe', 'state_code' => 'GO'],
                ['name' => 'Imo', 'state_code' => 'IM'],
                ['name' => 'Jigawa', 'state_code' => 'JI'],
                ['name' => 'Kaduna', 'state_code' => 'KD'],
                ['name' => 'Kano', 'state_code' => 'KN'],
                ['name' => 'Katsina', 'state_code' => 'KT'],
                ['name' => 'Kebbi', 'state_code' => 'KE'],
                ['name' => 'Kogi', 'state_code' => 'KO'],
                ['name' => 'Kwara', 'state_code' => 'KW'],
                ['name' => 'Lagos', 'state_code' => 'LA'],
                ['name' => 'Nasarawa', 'state_code' => 'NA'],
                ['name' => 'Niger', 'state_code' => 'NI'],
                ['name' => 'Ogun', 'state_code' => 'OG'],
                ['name' => 'Ondo', 'state_code' => 'ON'],
                ['name' => 'Osun', 'state_code' => 'OS'],
                ['name' => 'Oyo', 'state_code' => 'OY'],
                ['name' => 'Plateau', 'state_code' => 'PL'],
                ['name' => 'Rivers', 'state_code' => 'RI'],
                ['name' => 'Sokoto', 'state_code' => 'SO'],
                ['name' => 'Taraba', 'state_code' => 'TA'],
                ['name' => 'Yobe', 'state_code' => 'YO'],
                ['name' => 'Zamfara', 'state_code' => 'ZA'],
            ];

            $nigeriaId = DB::table('countries')->where('iso_code_2', 'NG')->value('id');

            foreach ($nigerianStates as $state) {
                DB::table('states')->insert([
                    'name' => $state['name'],
                    'state_code' => $state['state_code'],
                    'code' => $state['state_code'], // Fill the existing 'code' column
                    'country_id' => $nigeriaId,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
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
        Schema::table('states', function (Blueprint $table) {
            $table->dropForeign(['country_id']);
            $table->dropColumn(['state_code', 'country_id', 'is_active']);
        });
    }
};
