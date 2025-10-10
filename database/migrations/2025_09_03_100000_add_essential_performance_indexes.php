<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations for essential performance indexes only.
     * This migration focuses on the most critical indexes and avoids conflicts.
     */
    public function up()
    {
        // Use raw SQL to check for existing indexes and create only if needed
        $this->createIndexSafely('admin_users', 'role', 'idx_admin_role_safe');
        $this->createIndexSafely('admin_users', 'last_login_at', 'idx_admin_last_login_safe');
        
        // Essential driver indexes
        $this->createIndexSafely('drivers', 'email', 'idx_driver_email_safe');
        $this->createIndexSafely('drivers', 'phone', 'idx_driver_phone_safe');
        $this->createIndexSafely('drivers', 'verification_status', 'idx_driver_verification_safe');
        $this->createIndexSafely('drivers', 'last_active_at', 'idx_driver_last_active_safe');
        $this->createIndexSafely('drivers', 'created_at', 'idx_driver_created_safe');
        
        // OCR verification index if column exists
        if (Schema::hasColumn('drivers', 'ocr_verification_status')) {
            $this->createIndexSafely('drivers', 'ocr_verification_status', 'idx_driver_ocr_safe');
        }
        
        // KYC indexes only if columns exist and no conflicts
        if (Schema::hasColumn('drivers', 'kyc_status') && !$this->indexNameExists('idx_kyc_step_status')) {
            $this->createIndexSafely('drivers', 'kyc_status', 'idx_driver_kyc_status_safe');
        }
        
        // Company essential indexes
        $this->createIndexSafely('companies', 'email', 'idx_company_email_safe');
        $this->createIndexSafely('companies', 'status', 'idx_company_status_safe');
        
        // Company requests indexes
        $this->createIndexSafely('company_requests', 'company_id', 'idx_company_req_company_safe');
        $this->createIndexSafely('company_requests', 'driver_id', 'idx_company_req_driver_safe');
        $this->createIndexSafely('company_requests', 'status', 'idx_company_req_status_safe');
        
        // Driver matches indexes
        $this->createIndexSafely('driver_matches', 'driver_id', 'idx_driver_match_driver_safe');
        $this->createIndexSafely('driver_matches', 'company_request_id', 'idx_driver_match_request_safe');
        $this->createIndexSafely('driver_matches', 'status', 'idx_driver_match_status_safe');
    }

    /**
     * Create an index safely using raw SQL to avoid conflicts
     */
    private function createIndexSafely(string $table, string $column, string $indexName): void
    {
        try {
            // Check if index already exists
            $exists = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName]);
            
            if (empty($exists)) {
                // Also check if any index exists on this column
                $columnIndexExists = DB::select("SHOW INDEX FROM `{$table}` WHERE Column_name = ?", [$column]);
                
                if (empty($columnIndexExists)) {
                    DB::statement("CREATE INDEX `{$indexName}` ON `{$table}` (`{$column}`)");
                }
            }
        } catch (\Exception $e) {
            // Log but don't fail the migration
            \Log::info("Could not create index {$indexName} on {$table}.{$column}: " . $e->getMessage());
        }
    }

    /**
     * Check if an index name exists in the database
     */
    private function indexNameExists(string $indexName): bool
    {
        try {
            $result = DB::select("SELECT COUNT(*) as count FROM information_schema.statistics WHERE index_name = ? AND table_schema = DATABASE()", [$indexName]);
            return $result[0]->count > 0;
        } catch (\Exception $e) {
            return true; // Assume exists to be safe
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        // Drop only the indexes we created
        $indexes = [
            'admin_users' => ['idx_admin_role_safe', 'idx_admin_last_login_safe'],
            'drivers' => [
                'idx_driver_email_safe', 'idx_driver_phone_safe', 'idx_driver_verification_safe', 
                'idx_driver_last_active_safe', 'idx_driver_created_safe', 'idx_driver_ocr_safe',
                'idx_driver_kyc_status_safe'
            ],
            'companies' => ['idx_company_email_safe', 'idx_company_status_safe'],
            'company_requests' => [
                'idx_company_req_company_safe', 'idx_company_req_driver_safe', 'idx_company_req_status_safe'
            ],
            'driver_matches' => [
                'idx_driver_match_driver_safe', 'idx_driver_match_request_safe', 'idx_driver_match_status_safe'
            ]
        ];

        foreach ($indexes as $table => $tableIndexes) {
            foreach ($tableIndexes as $index) {
                try {
                    DB::statement("DROP INDEX IF EXISTS `{$index}` ON `{$table}`");
                } catch (\Exception $e) {
                    // Index might not exist, that's OK
                }
            }
        }
    }
};