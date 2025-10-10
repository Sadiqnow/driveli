<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckDuplicates extends Command
{
    protected $signature = 'drivelink:check-duplicates';
    protected $description = 'Check for duplicate entries in database tables';

    public function handle()
    {
        $this->info('=== DRIVELINK DUPLICATE ENTRIES CHECK ===');
        $this->newLine();

        // Check drivers duplicates
        $this->checkDriversDuplicates();
        
        // Check admin_users duplicates
        $this->checkAdminUsersDuplicates();
        
        // Check other tables
        $this->checkOtherTables();
        
        $this->info('=== DUPLICATE CHECK COMPLETE ===');
    }
    
    private function checkDriversDuplicates()
    {
        $this->info('1. CHECKING DRIVERS_NORMALIZED TABLE:');
        $this->line(str_repeat('-', 50));
        
        try {
            // Check duplicate emails
            $duplicateEmails = DB::select("
                SELECT email, COUNT(*) as count 
                FROM drivers 
                WHERE email IS NOT NULL AND email != '' 
                GROUP BY email 
                HAVING COUNT(*) > 1
                ORDER BY count DESC
            ");
            
            if (!empty($duplicateEmails)) {
                $this->error("❌ FOUND " . count($duplicateEmails) . " DUPLICATE EMAILS:");
                foreach ($duplicateEmails as $dup) {
                    $this->line("   • Email: {$dup->email} (Count: {$dup->count})");
                    
                    // Get details
                    $records = DB::select("
                        SELECT id, driver_id, first_name, surname, created_at, deleted_at 
                        FROM drivers 
                        WHERE email = ? 
                        ORDER BY created_at
                    ", [$dup->email]);
                    
                    foreach ($records as $record) {
                        $status = $record->deleted_at ? '[DELETED]' : '[ACTIVE]';
                        $this->line("     → ID: {$record->id}, Driver: {$record->driver_id}, Name: {$record->first_name} {$record->surname} {$status}");
                    }
                    $this->newLine();
                }
            } else {
                $this->info("✅ No duplicate emails found");
            }
            
            // Check duplicate phones
            $duplicatePhones = DB::select("
                SELECT phone, COUNT(*) as count 
                FROM drivers 
                WHERE phone IS NOT NULL AND phone != '' 
                GROUP BY phone 
                HAVING COUNT(*) > 1
                ORDER BY count DESC
            ");
            
            if (!empty($duplicatePhones)) {
                $this->error("❌ FOUND " . count($duplicatePhones) . " DUPLICATE PHONE NUMBERS:");
                foreach ($duplicatePhones as $dup) {
                    $this->line("   • Phone: {$dup->phone} (Count: {$dup->count})");
                    
                    $records = DB::select("
                        SELECT id, driver_id, first_name, surname, email, created_at, deleted_at 
                        FROM drivers 
                        WHERE phone = ? 
                        ORDER BY created_at
                    ", [$dup->phone]);
                    
                    foreach ($records as $record) {
                        $status = $record->deleted_at ? '[DELETED]' : '[ACTIVE]';
                        $this->line("     → ID: {$record->id}, Driver: {$record->driver_id}, Name: {$record->first_name} {$record->surname} {$status}");
                    }
                    $this->newLine();
                }
            } else {
                $this->info("✅ No duplicate phone numbers found");
            }
            
            // Check duplicate driver_id
            $duplicateDriverIds = DB::select("
                SELECT driver_id, COUNT(*) as count 
                FROM drivers 
                WHERE driver_id IS NOT NULL AND driver_id != '' 
                GROUP BY driver_id 
                HAVING COUNT(*) > 1
                ORDER BY count DESC
            ");
            
            if (!empty($duplicateDriverIds)) {
                $this->error("❌ FOUND " . count($duplicateDriverIds) . " DUPLICATE DRIVER IDs:");
                foreach ($duplicateDriverIds as $dup) {
                    $this->line("   • Driver ID: {$dup->driver_id} (Count: {$dup->count})");
                }
            } else {
                $this->info("✅ No duplicate driver IDs found");
            }
            
            // Check duplicate NIN numbers
            $duplicateNINs = DB::select("
                SELECT nin_number, COUNT(*) as count 
                FROM drivers 
                WHERE nin_number IS NOT NULL AND nin_number != '' 
                GROUP BY nin_number 
                HAVING COUNT(*) > 1
                ORDER BY count DESC
            ");
            
            if (!empty($duplicateNINs)) {
                $this->error("❌ FOUND " . count($duplicateNINs) . " DUPLICATE NIN NUMBERS:");
                foreach ($duplicateNINs as $dup) {
                    $this->line("   • NIN: {$dup->nin_number} (Count: {$dup->count})");
                }
            } else {
                $this->info("✅ No duplicate NIN numbers found");
            }
            
        } catch (\Exception $e) {
            $this->error("Error checking drivers: " . $e->getMessage());
        }
        
        $this->newLine();
    }
    
    private function checkAdminUsersDuplicates()
    {
        $this->info('2. CHECKING ADMIN_USERS TABLE:');
        $this->line(str_repeat('-', 50));
        
        try {
            $duplicateEmails = DB::select("
                SELECT email, COUNT(*) as count 
                FROM admin_users 
                WHERE email IS NOT NULL AND email != '' 
                GROUP BY email 
                HAVING COUNT(*) > 1
                ORDER BY count DESC
            ");
            
            if (!empty($duplicateEmails)) {
                $this->error("❌ FOUND " . count($duplicateEmails) . " DUPLICATE ADMIN EMAILS:");
                foreach ($duplicateEmails as $dup) {
                    $this->line("   • Email: {$dup->email} (Count: {$dup->count})");
                    
                    $records = DB::select("
                        SELECT id, name, email, role, created_at, deleted_at 
                        FROM admin_users 
                        WHERE email = ? 
                        ORDER BY created_at
                    ", [$dup->email]);
                    
                    foreach ($records as $record) {
                        $status = $record->deleted_at ? '[DELETED]' : '[ACTIVE]';
                        $this->line("     → ID: {$record->id}, Name: {$record->name}, Role: {$record->role} {$status}");
                    }
                    $this->newLine();
                }
            } else {
                $this->info("✅ No duplicate admin emails found");
            }
            
        } catch (\Exception $e) {
            $this->error("Error checking admin_users: " . $e->getMessage());
        }
        
        $this->newLine();
    }
    
    private function checkOtherTables()
    {
        $this->info('3. CHECKING OTHER TABLES:');
        $this->line(str_repeat('-', 50));
        
        // Check what tables exist
        $tables = DB::select("SHOW TABLES");
        $tableNames = array_map(function($table) {
            return array_values((array)$table)[0];
        }, $tables);
        
        $this->info("Available tables:");
        foreach ($tableNames as $table) {
            try {
                $count = DB::scalar("SELECT COUNT(*) FROM `{$table}`");
                $this->line("   • {$table} ({$count} records)");
            } catch (\Exception $e) {
                $this->line("   • {$table} (error counting)");
            }
        }
        
        // Check specific table duplicates if they exist
        if (in_array('driver_documents', $tableNames)) {
            $duplicateDocs = DB::select("
                SELECT driver_id, document_type, COUNT(*) as count 
                FROM driver_documents 
                GROUP BY driver_id, document_type 
                HAVING COUNT(*) > 1
            ");
            
            if (!empty($duplicateDocs)) {
                $this->error("❌ FOUND " . count($duplicateDocs) . " DUPLICATE DRIVER DOCUMENTS:");
                foreach ($duplicateDocs as $dup) {
                    $this->line("   • Driver ID: {$dup->driver_id}, Document: {$dup->document_type} (Count: {$dup->count})");
                }
            } else {
                $this->info("✅ No duplicate driver documents found");
            }
        }
        
        $this->newLine();
    }
}