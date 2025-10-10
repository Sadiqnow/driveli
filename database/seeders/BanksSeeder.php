<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BanksSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $banks = [
            ['name' => 'Access Bank', 'code' => '044', 'is_active' => true],
            ['name' => 'Citibank', 'code' => '023', 'is_active' => true],
            ['name' => 'Diamond Bank', 'code' => '063', 'is_active' => true],
            ['name' => 'Ecobank Nigeria', 'code' => '050', 'is_active' => true],
            ['name' => 'Fidelity Bank Nigeria', 'code' => '070', 'is_active' => true],
            ['name' => 'First Bank of Nigeria', 'code' => '011', 'is_active' => true],
            ['name' => 'First City Monument Bank', 'code' => '214', 'is_active' => true],
            ['name' => 'Guaranty Trust Bank', 'code' => '058', 'is_active' => true],
            ['name' => 'Heritage Bank Plc', 'code' => '030', 'is_active' => true],
            ['name' => 'Keystone Bank Limited', 'code' => '082', 'is_active' => true],
            ['name' => 'Providus Bank Plc', 'code' => '101', 'is_active' => true],
            ['name' => 'Polaris Bank', 'code' => '076', 'is_active' => true],
            ['name' => 'Stanbic IBTC Bank Nigeria Limited', 'code' => '221', 'is_active' => true],
            ['name' => 'Standard Chartered Bank', 'code' => '068', 'is_active' => true],
            ['name' => 'Sterling Bank', 'code' => '232', 'is_active' => true],
            ['name' => 'Suntrust Bank Nigeria Limited', 'code' => '100', 'is_active' => true],
            ['name' => 'Union Bank of Nigeria', 'code' => '032', 'is_active' => true],
            ['name' => 'United Bank for Africa', 'code' => '033', 'is_active' => true],
            ['name' => 'Unity Bank Plc', 'code' => '215', 'is_active' => true],
            ['name' => 'Wema Bank', 'code' => '035', 'is_active' => true],
            ['name' => 'Zenith Bank', 'code' => '057', 'is_active' => true],
        ];

        $timestamp = now();
        foreach ($banks as &$bank) {
            $bank['created_at'] = $timestamp;
            $bank['updated_at'] = $timestamp;
        }

        DB::table('banks')->upsert($banks, ['code'], ['name', 'is_active', 'updated_at']);
    }
}
