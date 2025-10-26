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
        if (!Schema::hasColumn('banks', 'bank_code')) {
            $columnsToAdd[] = function (Blueprint $table) {
                $table->string('bank_code', 10)->nullable()->after('name');
            };
        }
        if (!Schema::hasColumn('banks', 'swift_code')) {
            $columnsToAdd[] = function (Blueprint $table) {
                $table->string('swift_code', 20)->nullable()->after('bank_code');
            };
        }
        if (!Schema::hasColumn('banks', 'country_id')) {
            $columnsToAdd[] = function (Blueprint $table) {
                $table->foreignId('country_id')->nullable()->constrained('countries')->onDelete('cascade')->after('swift_code');
            };
        }

        if (!empty($columnsToAdd)) {
            Schema::table('banks', function (Blueprint $table) use ($columnsToAdd) {
                foreach ($columnsToAdd as $addColumn) {
                    $addColumn($table);
                }
            });
        }

        // Insert major Nigerian banks only if table is empty
        if (DB::table('banks')->count() == 0) {
            $nigerianBanks = [
                ['name' => 'Access Bank', 'bank_code' => '044', 'swift_code' => 'ABNGNGLA'],
                ['name' => 'First Bank of Nigeria', 'bank_code' => '011', 'swift_code' => 'FBNINGLA'],
                ['name' => 'Guaranty Trust Bank', 'bank_code' => '058', 'swift_code' => 'GTBINGLA'],
                ['name' => 'United Bank for Africa', 'bank_code' => '033', 'swift_code' => 'UBNINGLA'],
                ['name' => 'Zenith Bank', 'bank_code' => '057', 'swift_code' => 'ZEIBNGLA'],
                ['name' => 'Ecobank Nigeria', 'bank_code' => '050', 'swift_code' => 'ECOCNGLA'],
                ['name' => 'Fidelity Bank', 'bank_code' => '070', 'swift_code' => 'FDLINGLA'],
                ['name' => 'Union Bank of Nigeria', 'bank_code' => '032', 'swift_code' => 'UBNNNGLA'],
                ['name' => 'Sterling Bank', 'bank_code' => '106', 'swift_code' => 'STBLNGLA'],
                ['name' => 'Wema Bank', 'bank_code' => '035', 'swift_code' => 'WEMANGLA'],
            ];

            $nigeriaId = DB::table('countries')->where('iso_code_2', 'NG')->value('id');

            foreach ($nigerianBanks as $bank) {
                DB::table('banks')->insert([
                    'name' => $bank['name'],
                    'code' => $bank['bank_code'], // Fill the existing 'code' column
                    'bank_code' => $bank['bank_code'],
                    'swift_code' => $bank['swift_code'],
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
        Schema::table('banks', function (Blueprint $table) {
            $table->dropForeign(['country_id']);
            $table->dropColumn(['bank_code', 'swift_code', 'country_id', 'is_active']);
        });
    }
};
