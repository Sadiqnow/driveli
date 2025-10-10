<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class AddMissingKycColumns extends Command
{
    protected $signature = 'kyc:add-missing-columns';
    protected $description = 'Add missing KYC columns to drivers table';

    public function handle()
    {
        $this->info('Adding missing KYC columns to drivers table...');

        try {
            $columnsToAdd = [
                'city' => fn(Blueprint $table) => $table->string('city', 100)->nullable(),
                'postal_code' => fn(Blueprint $table) => $table->string('postal_code', 10)->nullable(),
                'license_issue_date' => fn(Blueprint $table) => $table->date('license_issue_date')->nullable(),
                'years_of_experience' => fn(Blueprint $table) => $table->integer('years_of_experience')->nullable(),
                'previous_company' => fn(Blueprint $table) => $table->string('previous_company', 100)->nullable(),
                'bank_id' => fn(Blueprint $table) => $table->unsignedBigInteger('bank_id')->nullable(),
                'account_number' => fn(Blueprint $table) => $table->string('account_number', 20)->nullable(),
                'account_name' => fn(Blueprint $table) => $table->string('account_name', 100)->nullable(),
                'bvn' => fn(Blueprint $table) => $table->string('bvn', 11)->nullable(),
                'residential_address' => fn(Blueprint $table) => $table->text('residential_address')->nullable(),
                'has_vehicle' => fn(Blueprint $table) => $table->boolean('has_vehicle')->nullable(),
                'vehicle_type' => fn(Blueprint $table) => $table->string('vehicle_type', 100)->nullable(),
                'vehicle_year' => fn(Blueprint $table) => $table->integer('vehicle_year')->nullable(),
                'preferred_work_location' => fn(Blueprint $table) => $table->string('preferred_work_location')->nullable(),
                'available_for_night_shifts' => fn(Blueprint $table) => $table->boolean('available_for_night_shifts')->nullable(),
                'available_for_weekend_work' => fn(Blueprint $table) => $table->boolean('available_for_weekend_work')->nullable(),
            ];

            $added = 0;
            foreach ($columnsToAdd as $columnName => $columnDefinition) {
                if (!Schema::hasColumn('drivers', $columnName)) {
                    Schema::table('drivers', function (Blueprint $table) use ($columnDefinition) {
                        $columnDefinition($table);
                    });
                    $this->info("✅ Added column: {$columnName}");
                    $added++;
                } else {
                    $this->line("⏭️  Column already exists: {$columnName}");
                }
            }

            if ($added > 0) {
                $this->info("\n✅ Successfully added {$added} missing columns!");
            } else {
                $this->info("\n✅ All columns already exist!");
            }

            return 0;

        } catch (\Exception $e) {
            $this->error("❌ Error: " . $e->getMessage());
            return 1;
        }
    }
}