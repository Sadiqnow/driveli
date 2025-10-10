<?php

require_once 'vendor/autoload.php';

// Load Laravel environment
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

class DuplicateChecker
{
    public function checkAllTables()
    {
        echo "=== DRIVELINK DATABASE DUPLICATE ENTRIES CHECKER ===\n\n";
        
        $this->checkAdminUsers();
        $this->checkDrivers();
        $this->checkCompanies();
        $this->checkCompanyRequests();
        $this->checkDriverMatches();
        $this->checkDriverDocuments();
        $this->checkDriverPerformances();
        $this->checkDriverLocations();
        $this->checkDriverBankingDetails();
        $this->checkGuarantors();
        $this->checkCommissions();
        
        echo "\n=== DUPLICATE CHECK COMPLETE ===\n";
    }
    
    private function checkAdminUsers()
    {
        echo "1. CHECKING ADMIN_USERS TABLE:\n";
        echo str_repeat("-", 50) . "\n";
        
        try {
            // Check for duplicate emails
            $duplicateEmails = DB::select("
                SELECT email, COUNT(*) as count 
                FROM admin_users 
                WHERE email IS NOT NULL 
                GROUP BY email 
                HAVING COUNT(*) > 1
            ");
            
            if (!empty($duplicateEmails)) {
                echo "❌ DUPLICATE EMAILS FOUND:\n";
                foreach ($duplicateEmails as $dup) {
                    echo "   Email: {$dup->email} (Count: {$dup->count})\n";
                    
                    // Show the duplicate records
                    $records = DB::select("SELECT id, name, email, created_at, deleted_at FROM admin_users WHERE email = ?", [$dup->email]);
                    foreach ($records as $record) {
                        $status = $record->deleted_at ? 'DELETED' : 'ACTIVE';
                        echo "     -> ID: {$record->id}, Name: {$record->name}, Created: {$record->created_at} [{$status}]\n";
                    }
                }
            } else {
                echo "✅ No duplicate emails found\n";
            }
            
            // Check for duplicate names (potential issue)
            $duplicateNames = DB::select("
                SELECT name, COUNT(*) as count 
                FROM admin_users 
                WHERE name IS NOT NULL AND deleted_at IS NULL
                GROUP BY name 
                HAVING COUNT(*) > 1
            ");
            
            if (!empty($duplicateNames)) {
                echo "⚠️  DUPLICATE NAMES FOUND (Active records only):\n";
                foreach ($duplicateNames as $dup) {
                    echo "   Name: {$dup->name} (Count: {$dup->count})\n";
                }
            } else {
                echo "✅ No duplicate names found\n";
            }
            
        } catch (Exception $e) {
            echo "❌ Error checking admin_users: " . $e->getMessage() . "\n";
        }
        
        echo "\n";
    }
    
    private function checkDrivers()
    {
        echo "2. CHECKING DRIVERS_NORMALIZED TABLE:\n";
        echo str_repeat("-", 50) . "\n";
        
        try {
            // Check for duplicate emails
            $duplicateEmails = DB::select("
                SELECT email, COUNT(*) as count 
                FROM drivers 
                WHERE email IS NOT NULL 
                GROUP BY email 
                HAVING COUNT(*) > 1
            ");
            
            if (!empty($duplicateEmails)) {
                echo "❌ DUPLICATE EMAILS FOUND:\n";
                foreach ($duplicateEmails as $dup) {
                    echo "   Email: {$dup->email} (Count: {$dup->count})\n";
                    
                    $records = DB::select("SELECT id, driver_id, first_name, surname, email, created_at, deleted_at FROM drivers WHERE email = ?", [$dup->email]);
                    foreach ($records as $record) {
                        $status = $record->deleted_at ? 'DELETED' : 'ACTIVE';
                        echo "     -> ID: {$record->id}, Driver ID: {$record->driver_id}, Name: {$record->first_name} {$record->surname}, Created: {$record->created_at} [{$status}]\n";
                    }
                }
            } else {
                echo "✅ No duplicate emails found\n";
            }
            
            // Check for duplicate phones
            $duplicatePhones = DB::select("
                SELECT phone, COUNT(*) as count 
                FROM drivers 
                WHERE phone IS NOT NULL 
                GROUP BY phone 
                HAVING COUNT(*) > 1
            ");
            
            if (!empty($duplicatePhones)) {
                echo "❌ DUPLICATE PHONE NUMBERS FOUND:\n";
                foreach ($duplicatePhones as $dup) {
                    echo "   Phone: {$dup->phone} (Count: {$dup->count})\n";
                    
                    $records = DB::select("SELECT id, driver_id, first_name, surname, phone, created_at, deleted_at FROM drivers WHERE phone = ?", [$dup->phone]);
                    foreach ($records as $record) {
                        $status = $record->deleted_at ? 'DELETED' : 'ACTIVE';
                        echo "     -> ID: {$record->id}, Driver ID: {$record->driver_id}, Name: {$record->first_name} {$record->surname}, Created: {$record->created_at} [{$status}]\n";
                    }
                }
            } else {
                echo "✅ No duplicate phone numbers found\n";
            }
            
            // Check for duplicate driver_id
            $duplicateDriverIds = DB::select("
                SELECT driver_id, COUNT(*) as count 
                FROM drivers 
                WHERE driver_id IS NOT NULL 
                GROUP BY driver_id 
                HAVING COUNT(*) > 1
            ");
            
            if (!empty($duplicateDriverIds)) {
                echo "❌ DUPLICATE DRIVER IDs FOUND:\n";
                foreach ($duplicateDriverIds as $dup) {
                    echo "   Driver ID: {$dup->driver_id} (Count: {$dup->count})\n";
                }
            } else {
                echo "✅ No duplicate driver IDs found\n";
            }
            
            // Check for duplicate NIN numbers
            $duplicateNINs = DB::select("
                SELECT nin_number, COUNT(*) as count 
                FROM drivers 
                WHERE nin_number IS NOT NULL 
                GROUP BY nin_number 
                HAVING COUNT(*) > 1
            ");
            
            if (!empty($duplicateNINs)) {
                echo "❌ DUPLICATE NIN NUMBERS FOUND:\n";
                foreach ($duplicateNINs as $dup) {
                    echo "   NIN: {$dup->nin_number} (Count: {$dup->count})\n";
                }
            } else {
                echo "✅ No duplicate NIN numbers found\n";
            }
            
            // Check for duplicate license numbers
            $duplicateLicenses = DB::select("
                SELECT license_number, COUNT(*) as count 
                FROM drivers 
                WHERE license_number IS NOT NULL 
                GROUP BY license_number 
                HAVING COUNT(*) > 1
            ");
            
            if (!empty($duplicateLicenses)) {
                echo "❌ DUPLICATE LICENSE NUMBERS FOUND:\n";
                foreach ($duplicateLicenses as $dup) {
                    echo "   License: {$dup->license_number} (Count: {$dup->count})\n";
                }
            } else {
                echo "✅ No duplicate license numbers found\n";
            }
            
        } catch (Exception $e) {
            echo "❌ Error checking drivers: " . $e->getMessage() . "\n";
        }
        
        echo "\n";
    }
    
    private function checkCompanies()
    {
        echo "3. CHECKING COMPANIES TABLE:\n";
        echo str_repeat("-", 50) . "\n";
        
        try {
            // Check if table exists
            $tableExists = DB::select("SHOW TABLES LIKE 'companies'");
            if (empty($tableExists)) {
                echo "⚠️  Companies table does not exist\n\n";
                return;
            }
            
            // Check for duplicate emails
            $duplicateEmails = DB::select("
                SELECT email, COUNT(*) as count 
                FROM companies 
                WHERE email IS NOT NULL 
                GROUP BY email 
                HAVING COUNT(*) > 1
            ");
            
            if (!empty($duplicateEmails)) {
                echo "❌ DUPLICATE EMAILS FOUND:\n";
                foreach ($duplicateEmails as $dup) {
                    echo "   Email: {$dup->email} (Count: {$dup->count})\n";
                }
            } else {
                echo "✅ No duplicate emails found\n";
            }
            
            // Check for duplicate company names
            $duplicateNames = DB::select("
                SELECT name, COUNT(*) as count 
                FROM companies 
                WHERE name IS NOT NULL 
                GROUP BY name 
                HAVING COUNT(*) > 1
            ");
            
            if (!empty($duplicateNames)) {
                echo "❌ DUPLICATE COMPANY NAMES FOUND:\n";
                foreach ($duplicateNames as $dup) {
                    echo "   Name: {$dup->name} (Count: {$dup->count})\n";
                }
            } else {
                echo "✅ No duplicate company names found\n";
            }
            
        } catch (Exception $e) {
            echo "❌ Error checking companies: " . $e->getMessage() . "\n";
        }
        
        echo "\n";
    }
    
    private function checkCompanyRequests()
    {
        echo "4. CHECKING COMPANY_REQUESTS TABLE:\n";
        echo str_repeat("-", 50) . "\n";
        
        try {
            // Check if table exists
            $tableExists = DB::select("SHOW TABLES LIKE 'company_requests'");
            if (empty($tableExists)) {
                echo "⚠️  Company_requests table does not exist\n\n";
                return;
            }
            
            // Check for duplicate request_id
            $duplicateRequestIds = DB::select("
                SELECT request_id, COUNT(*) as count 
                FROM company_requests 
                WHERE request_id IS NOT NULL 
                GROUP BY request_id 
                HAVING COUNT(*) > 1
            ");
            
            if (!empty($duplicateRequestIds)) {
                echo "❌ DUPLICATE REQUEST IDs FOUND:\n";
                foreach ($duplicateRequestIds as $dup) {
                    echo "   Request ID: {$dup->request_id} (Count: {$dup->count})\n";
                }
            } else {
                echo "✅ No duplicate request IDs found\n";
            }
            
        } catch (Exception $e) {
            echo "❌ Error checking company_requests: " . $e->getMessage() . "\n";
        }
        
        echo "\n";
    }
    
    private function checkDriverMatches()
    {
        echo "5. CHECKING DRIVER_MATCHES TABLE:\n";
        echo str_repeat("-", 50) . "\n";
        
        try {
            // Check if table exists
            $tableExists = DB::select("SHOW TABLES LIKE 'driver_matches'");
            if (empty($tableExists)) {
                echo "⚠️  Driver_matches table does not exist\n\n";
                return;
            }
            
            // Check for duplicate driver-request combinations
            $duplicateMatches = DB::select("
                SELECT driver_id, company_request_id, COUNT(*) as count 
                FROM driver_matches 
                WHERE driver_id IS NOT NULL AND company_request_id IS NOT NULL 
                GROUP BY driver_id, company_request_id 
                HAVING COUNT(*) > 1
            ");
            
            if (!empty($duplicateMatches)) {
                echo "❌ DUPLICATE DRIVER-REQUEST MATCHES FOUND:\n";
                foreach ($duplicateMatches as $dup) {
                    echo "   Driver ID: {$dup->driver_id}, Request ID: {$dup->company_request_id} (Count: {$dup->count})\n";
                }
            } else {
                echo "✅ No duplicate driver-request matches found\n";
            }
            
        } catch (Exception $e) {
            echo "❌ Error checking driver_matches: " . $e->getMessage() . "\n";
        }
        
        echo "\n";
    }
    
    private function checkDriverDocuments()
    {
        echo "6. CHECKING DRIVER_DOCUMENTS TABLE:\n";
        echo str_repeat("-", 50) . "\n";
        
        try {
            // Check if table exists
            $tableExists = DB::select("SHOW TABLES LIKE 'driver_documents'");
            if (empty($tableExists)) {
                echo "⚠️  Driver_documents table does not exist\n\n";
                return;
            }
            
            // Check for duplicate driver-document_type combinations
            $duplicateDocs = DB::select("
                SELECT driver_id, document_type, COUNT(*) as count 
                FROM driver_documents 
                WHERE driver_id IS NOT NULL AND document_type IS NOT NULL 
                GROUP BY driver_id, document_type 
                HAVING COUNT(*) > 1
            ");
            
            if (!empty($duplicateDocs)) {
                echo "❌ DUPLICATE DRIVER DOCUMENTS FOUND:\n";
                foreach ($duplicateDocs as $dup) {
                    echo "   Driver ID: {$dup->driver_id}, Document Type: {$dup->document_type} (Count: {$dup->count})\n";
                }
            } else {
                echo "✅ No duplicate driver documents found\n";
            }
            
        } catch (Exception $e) {
            echo "❌ Error checking driver_documents: " . $e->getMessage() . "\n";
        }
        
        echo "\n";
    }
    
    private function checkDriverPerformances()
    {
        echo "7. CHECKING DRIVER_PERFORMANCES TABLE:\n";
        echo str_repeat("-", 50) . "\n";
        
        try {
            // Check if table exists
            $tableExists = DB::select("SHOW TABLES LIKE 'driver_performances'");
            if (empty($tableExists)) {
                echo "⚠️  Driver_performances table does not exist\n\n";
                return;
            }
            
            // Check for duplicate driver_id entries
            $duplicatePerformances = DB::select("
                SELECT driver_id, COUNT(*) as count 
                FROM driver_performances 
                WHERE driver_id IS NOT NULL 
                GROUP BY driver_id 
                HAVING COUNT(*) > 1
            ");
            
            if (!empty($duplicatePerformances)) {
                echo "❌ DUPLICATE DRIVER PERFORMANCE RECORDS FOUND:\n";
                foreach ($duplicatePerformances as $dup) {
                    echo "   Driver ID: {$dup->driver_id} (Count: {$dup->count})\n";
                }
            } else {
                echo "✅ No duplicate driver performance records found\n";
            }
            
        } catch (Exception $e) {
            echo "❌ Error checking driver_performances: " . $e->getMessage() . "\n";
        }
        
        echo "\n";
    }
    
    private function checkDriverLocations()
    {
        echo "8. CHECKING DRIVER_LOCATIONS TABLE:\n";
        echo str_repeat("-", 50) . "\n";
        
        try {
            // Check if table exists
            $tableExists = DB::select("SHOW TABLES LIKE 'driver_locations'");
            if (empty($tableExists)) {
                echo "⚠️  Driver_locations table does not exist\n\n";
                return;
            }
            
            // Check for multiple primary locations per driver
            $multiplePrimary = DB::select("
                SELECT driver_id, location_type, COUNT(*) as count 
                FROM driver_locations 
                WHERE is_primary = 1 
                GROUP BY driver_id, location_type 
                HAVING COUNT(*) > 1
            ");
            
            if (!empty($multiplePrimary)) {
                echo "❌ MULTIPLE PRIMARY LOCATIONS FOUND (Should be only 1 per type per driver):\n";
                foreach ($multiplePrimary as $dup) {
                    echo "   Driver ID: {$dup->driver_id}, Location Type: {$dup->location_type} (Count: {$dup->count})\n";
                }
            } else {
                echo "✅ No multiple primary locations found\n";
            }
            
        } catch (Exception $e) {
            echo "❌ Error checking driver_locations: " . $e->getMessage() . "\n";
        }
        
        echo "\n";
    }
    
    private function checkDriverBankingDetails()
    {
        echo "9. CHECKING DRIVER_BANKING_DETAILS TABLE:\n";
        echo str_repeat("-", 50) . "\n";
        
        try {
            // Check if table exists
            $tableExists = DB::select("SHOW TABLES LIKE 'driver_banking_details'");
            if (empty($tableExists)) {
                echo "⚠️  Driver_banking_details table does not exist\n\n";
                return;
            }
            
            // Check for duplicate account numbers
            $duplicateAccounts = DB::select("
                SELECT account_number, COUNT(*) as count 
                FROM driver_banking_details 
                WHERE account_number IS NOT NULL 
                GROUP BY account_number 
                HAVING COUNT(*) > 1
            ");
            
            if (!empty($duplicateAccounts)) {
                echo "❌ DUPLICATE ACCOUNT NUMBERS FOUND:\n";
                foreach ($duplicateAccounts as $dup) {
                    echo "   Account Number: {$dup->account_number} (Count: {$dup->count})\n";
                }
            } else {
                echo "✅ No duplicate account numbers found\n";
            }
            
            // Check for multiple primary banking details per driver
            $multiplePrimaryBanking = DB::select("
                SELECT driver_id, COUNT(*) as count 
                FROM driver_banking_details 
                WHERE is_primary = 1 
                GROUP BY driver_id 
                HAVING COUNT(*) > 1
            ");
            
            if (!empty($multiplePrimaryBanking)) {
                echo "❌ MULTIPLE PRIMARY BANKING DETAILS FOUND (Should be only 1 per driver):\n";
                foreach ($multiplePrimaryBanking as $dup) {
                    echo "   Driver ID: {$dup->driver_id} (Count: {$dup->count})\n";
                }
            } else {
                echo "✅ No multiple primary banking details found\n";
            }
            
        } catch (Exception $e) {
            echo "❌ Error checking driver_banking_details: " . $e->getMessage() . "\n";
        }
        
        echo "\n";
    }
    
    private function checkGuarantors()
    {
        echo "10. CHECKING GUARANTORS TABLE:\n";
        echo str_repeat("-", 50) . "\n";
        
        try {
            // Check if table exists
            $tableExists = DB::select("SHOW TABLES LIKE 'guarantors'");
            if (empty($tableExists)) {
                echo "⚠️  Guarantors table does not exist\n\n";
                return;
            }
            
            // Check for duplicate phone numbers
            $duplicatePhones = DB::select("
                SELECT phone, COUNT(*) as count 
                FROM guarantors 
                WHERE phone IS NOT NULL 
                GROUP BY phone 
                HAVING COUNT(*) > 1
            ");
            
            if (!empty($duplicatePhones)) {
                echo "❌ DUPLICATE GUARANTOR PHONE NUMBERS FOUND:\n";
                foreach ($duplicatePhones as $dup) {
                    echo "   Phone: {$dup->phone} (Count: {$dup->count})\n";
                }
            } else {
                echo "✅ No duplicate guarantor phone numbers found\n";
            }
            
        } catch (Exception $e) {
            echo "❌ Error checking guarantors: " . $e->getMessage() . "\n";
        }
        
        echo "\n";
    }
    
    private function checkCommissions()
    {
        echo "11. CHECKING COMMISSIONS TABLE:\n";
        echo str_repeat("-", 50) . "\n";
        
        try {
            // Check if table exists
            $tableExists = DB::select("SHOW TABLES LIKE 'commissions'");
            if (empty($tableExists)) {
                echo "⚠️  Commissions table does not exist\n\n";
                return;
            }
            
            // Check for duplicate commission entries per match
            $duplicateCommissions = DB::select("
                SELECT driver_match_id, COUNT(*) as count 
                FROM commissions 
                WHERE driver_match_id IS NOT NULL 
                GROUP BY driver_match_id 
                HAVING COUNT(*) > 1
            ");
            
            if (!empty($duplicateCommissions)) {
                echo "❌ DUPLICATE COMMISSION ENTRIES FOUND:\n";
                foreach ($duplicateCommissions as $dup) {
                    echo "   Driver Match ID: {$dup->driver_match_id} (Count: {$dup->count})\n";
                }
            } else {
                echo "✅ No duplicate commission entries found\n";
            }
            
        } catch (Exception $e) {
            echo "❌ Error checking commissions: " . $e->getMessage() . "\n";
        }
        
        echo "\n";
    }
}

// Run the duplicate checker
try {
    $checker = new DuplicateChecker();
    $checker->checkAllTables();
} catch (Exception $e) {
    echo "Fatal Error: " . $e->getMessage() . "\n";
}

?>