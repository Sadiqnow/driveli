<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class AddOCRColumns extends Command
{
    protected $signature = 'drivers:add-ocr-columns';
    protected $description = 'Add OCR verification columns to drivers table';

    public function handle()
    {
        $this->info('Adding OCR verification columns to drivers table...');
        
        try {
            Schema::table('drivers', function (Blueprint $table) {
                // Check if columns already exist before adding
                if (!Schema::hasColumn('drivers', 'nin_document')) {
                    // NIN Document OCR Verification
                    $table->string('nin_document')->nullable();
                    $table->json('nin_verification_data')->nullable();
                    $table->timestamp('nin_verified_at')->nullable();
                    $table->decimal('nin_ocr_match_score', 5, 2)->default(0);
                    
                    // FRSC License OCR Verification  
                    $table->string('frsc_document')->nullable();
                    $table->json('frsc_verification_data')->nullable();
                    $table->timestamp('frsc_verified_at')->nullable();
                    $table->decimal('frsc_ocr_match_score', 5, 2)->default(0);
                    
                    // Overall OCR Status
                    $table->enum('ocr_verification_status', ['pending', 'passed', 'failed'])->default('pending');
                    $table->text('ocr_verification_notes')->nullable();
                    
                    $this->info('✓ OCR verification columns added successfully!');
                } else {
                    $this->info('OCR columns already exist in drivers table.');
                }
            });
            
            $this->info('Operation completed successfully!');
            
        } catch (\Exception $e) {
            $this->error('Error adding OCR columns: ' . $e->getMessage());
            return 1;
        }
        
        return 0;
    }
}