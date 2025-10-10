<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Ensure states table exists
        if (!Schema::hasTable('states')) {
            Schema::create('states', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('code', 2)->unique();
                $table->timestamps();
            });
        }

        // Ensure local_governments table exists
        if (!Schema::hasTable('local_governments')) {
            Schema::create('local_governments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('state_id')->constrained('states')->onDelete('cascade');
                $table->string('name');
                $table->timestamps();
                
                $table->index(['state_id', 'name']);
            });
        }

        // Ensure nationalities table exists
        if (!Schema::hasTable('nationalities')) {
            Schema::create('nationalities', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('code', 3)->unique();
                $table->timestamps();
            });
        }

        // Ensure banks table exists
        if (!Schema::hasTable('banks')) {
            Schema::create('banks', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('code', 10)->unique();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // Seed data if tables are empty
        $this->seedEssentialData();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Don't drop tables in down() - data loss prevention
    }

    /**
     * Seed essential data
     */
    private function seedEssentialData(): void
    {
        // Seed states if empty
        if (DB::table('states')->count() === 0) {
            $states = [
                ['name' => 'Abia', 'code' => 'AB'],
                ['name' => 'Adamawa', 'code' => 'AD'],
                ['name' => 'Akwa Ibom', 'code' => 'AK'],
                ['name' => 'Anambra', 'code' => 'AN'],
                ['name' => 'Bauchi', 'code' => 'BA'],
                ['name' => 'Bayelsa', 'code' => 'BY'],
                ['name' => 'Benue', 'code' => 'BN'],
                ['name' => 'Borno', 'code' => 'BO'],
                ['name' => 'Cross River', 'code' => 'CR'],
                ['name' => 'Delta', 'code' => 'DE'],
                ['name' => 'Ebonyi', 'code' => 'EB'],
                ['name' => 'Edo', 'code' => 'ED'],
                ['name' => 'Ekiti', 'code' => 'EK'],
                ['name' => 'Enugu', 'code' => 'EN'],
                ['name' => 'FCT', 'code' => 'FC'],
                ['name' => 'Gombe', 'code' => 'GO'],
                ['name' => 'Imo', 'code' => 'IM'],
                ['name' => 'Jigawa', 'code' => 'JI'],
                ['name' => 'Kaduna', 'code' => 'KD'],
                ['name' => 'Kano', 'code' => 'KN'],
                ['name' => 'Katsina', 'code' => 'KT'],
                ['name' => 'Kebbi', 'code' => 'KE'],
                ['name' => 'Kogi', 'code' => 'KO'],
                ['name' => 'Kwara', 'code' => 'KW'],
                ['name' => 'Lagos', 'code' => 'LA'],
                ['name' => 'Nasarawa', 'code' => 'NA'],
                ['name' => 'Niger', 'code' => 'NI'],
                ['name' => 'Ogun', 'code' => 'OG'],
                ['name' => 'Ondo', 'code' => 'ON'],
                ['name' => 'Osun', 'code' => 'OS'],
                ['name' => 'Oyo', 'code' => 'OY'],
                ['name' => 'Plateau', 'code' => 'PL'],
                ['name' => 'Rivers', 'code' => 'RI'],
                ['name' => 'Sokoto', 'code' => 'SO'],
                ['name' => 'Taraba', 'code' => 'TA'],
                ['name' => 'Yobe', 'code' => 'YO'],
                ['name' => 'Zamfara', 'code' => 'ZA'],
            ];

            // Add timestamps
            $timestamp = now();
            foreach ($states as &$state) {
                $state['created_at'] = $timestamp;
                $state['updated_at'] = $timestamp;
            }

            DB::table('states')->insert($states);
        }

        // Seed essential LGAs if empty
        if (DB::table('local_governments')->count() === 0) {
            $this->seedEssentialLGAs();
        }

        // Seed nationalities if empty
        if (DB::table('nationalities')->count() === 0) {
            $nationalities = [
                ['name' => 'Nigerian', 'code' => 'NG'],
                ['name' => 'Ghanaian', 'code' => 'GH'],
                ['name' => 'Beninese', 'code' => 'BJ'],
                ['name' => 'Togolese', 'code' => 'TG'],
                ['name' => 'Cameroonian', 'code' => 'CM'],
                ['name' => 'American', 'code' => 'US'],
                ['name' => 'British', 'code' => 'GB'],
                ['name' => 'Other', 'code' => 'XX'],
            ];

            $timestamp = now();
            foreach ($nationalities as &$nationality) {
                $nationality['created_at'] = $timestamp;
                $nationality['updated_at'] = $timestamp;
            }

            // Use insertOrIgnore to avoid duplicate key conflicts in test runs
            DB::table('nationalities')->insertOrIgnore($nationalities);
        }
    }

    /**
     * Seed essential LGAs for key states
     */
    private function seedEssentialLGAs(): void
    {
        $timestamp = now();

        // Lagos LGAs
        $lagosId = DB::table('states')->where('code', 'LA')->value('id');
        if ($lagosId) {
            $lagosLgas = [
                'Agege', 'Ajeromi-Ifelodun', 'Alimosho', 'Amuwo-Odofin', 'Apapa',
                'Badagry', 'Epe', 'Eti Osa', 'Ibeju-Lekki', 'Ifako-Ijaiye',
                'Ikeja', 'Ikorodu', 'Kosofe', 'Lagos Island', 'Lagos Mainland',
                'Mushin', 'Ojo', 'Oshodi-Isolo', 'Shomolu', 'Surulere'
            ];

            $lgaData = [];
            foreach ($lagosLgas as $lgaName) {
                $lgaData[] = [
                    'state_id' => $lagosId,
                    'name' => $lgaName,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ];
            }
            DB::table('local_governments')->insert($lgaData);
        }

        // FCT LGAs
        $fctId = DB::table('states')->where('code', 'FC')->value('id');
        if ($fctId) {
            $fctLgas = [
                'Abaji', 'Bwari', 'Gwagwalada', 'Kuje', 'Kwali', 'Municipal Area Council'
            ];

            $lgaData = [];
            foreach ($fctLgas as $lgaName) {
                $lgaData[] = [
                    'state_id' => $fctId,
                    'name' => $lgaName,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ];
            }
            DB::table('local_governments')->insert($lgaData);
        }

        // Rivers LGAs
        $riversId = DB::table('states')->where('code', 'RI')->value('id');
        if ($riversId) {
            $riversLgas = [
                'Port Harcourt', 'Obio/Akpor', 'Eleme', 'Ikwerre', 'Etche',
                'Oyigbo', 'Tai', 'Gokana', 'Khana', 'Degema'
            ];

            $lgaData = [];
            foreach ($riversLgas as $lgaName) {
                $lgaData[] = [
                    'state_id' => $riversId,
                    'name' => $lgaName,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ];
            }
            DB::table('local_governments')->insert($lgaData);
        }
    }
};