<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VerificationRulesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('verification_rules')->insert([
            [
                'factor' => 'ocr_accuracy',
                'weight' => 0.4,
                'rules' => json_encode([
                    'method' => 'average_confidence',
                    'threshold' => 0.8,
                ]),
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'factor' => 'face_match',
                'weight' => 0.4,
                'rules' => json_encode([
                    'threshold' => 0.7,
                ]),
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'factor' => 'validation_consistency',
                'weight' => 0.2,
                'rules' => json_encode([
                    'method' => 'average_scores',
                    'threshold' => 0.8,
                ]),
                'active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
