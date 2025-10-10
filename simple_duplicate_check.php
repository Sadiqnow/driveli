<?php

echo "=== DRIVELINK DUPLICATE ENTRIES CHECK ===\n\n";

try {
    // Direct PDO connection
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=drivelink', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Database connected successfully!\n\n";
    
    // 1. Check duplicate emails in drivers
    echo "1. CHECKING DRIVERS_NORMALIZED - DUPLICATE EMAILS:\n";
    echo str_repeat("-", 50) . "\n";
    
    $stmt = $pdo->query("
        SELECT email, COUNT(*) as count 
        FROM drivers 
        WHERE email IS NOT NULL AND email != '' 
        GROUP BY email 
        HAVING COUNT(*) > 1
        ORDER BY count DESC
    ");
    
    $duplicateEmails = $stmt->fetchAll(PDO::FETCH_OBJ);
    if (!empty($duplicateEmails)) {
        echo "❌ FOUND " . count($duplicateEmails) . " DUPLICATE EMAILS:\n";
        foreach ($duplicateEmails as $dup) {
            echo "   • Email: {$dup->email} (Count: {$dup->count})\n";
            
            // Get details of duplicate records
            $detailStmt = $pdo->prepare("
                SELECT id, driver_id, first_name, surname, created_at, deleted_at 
                FROM drivers 
                WHERE email = ? 
                ORDER BY created_at
            ");
            $detailStmt->execute([$dup->email]);
            $records = $detailStmt->fetchAll(PDO::FETCH_OBJ);
            
            foreach ($records as $record) {
                $status = $record->deleted_at ? '[DELETED]' : '[ACTIVE]';
                echo "     → ID: {$record->id}, Driver: {$record->driver_id}, Name: {$record->first_name} {$record->surname}, Created: {$record->created_at} {$status}\n";
            }
            echo "\n";
        }
    } else {
        echo "✅ No duplicate emails found\n";
    }
    
    echo "\n";
    
    // 2. Check duplicate phone numbers
    echo "2. CHECKING DRIVERS_NORMALIZED - DUPLICATE PHONE NUMBERS:\n";
    echo str_repeat("-", 50) . "\n";
    
    $stmt = $pdo->query("
        SELECT phone, COUNT(*) as count 
        FROM drivers 
        WHERE phone IS NOT NULL AND phone != '' 
        GROUP BY phone 
        HAVING COUNT(*) > 1
        ORDER BY count DESC
    ");
    
    $duplicatePhones = $stmt->fetchAll(PDO::FETCH_OBJ);
    if (!empty($duplicatePhones)) {
        echo "❌ FOUND " . count($duplicatePhones) . " DUPLICATE PHONE NUMBERS:\n";
        foreach ($duplicatePhones as $dup) {
            echo "   • Phone: {$dup->phone} (Count: {$dup->count})\n";
            
            // Get details of duplicate records
            $detailStmt = $pdo->prepare("
                SELECT id, driver_id, first_name, surname, email, created_at, deleted_at 
                FROM drivers 
                WHERE phone = ? 
                ORDER BY created_at
            ");
            $detailStmt->execute([$dup->phone]);
            $records = $detailStmt->fetchAll(PDO::FETCH_OBJ);
            
            foreach ($records as $record) {
                $status = $record->deleted_at ? '[DELETED]' : '[ACTIVE]';
                echo "     → ID: {$record->id}, Driver: {$record->driver_id}, Name: {$record->first_name} {$record->surname}, Email: {$record->email}, Created: {$record->created_at} {$status}\n";
            }
            echo "\n";
        }
    } else {
        echo "✅ No duplicate phone numbers found\n";
    }
    
    echo "\n";
    
    // 3. Check duplicate driver_id
    echo "3. CHECKING DRIVERS_NORMALIZED - DUPLICATE DRIVER IDs:\n";
    echo str_repeat("-", 50) . "\n";
    
    $stmt = $pdo->query("
        SELECT driver_id, COUNT(*) as count 
        FROM drivers 
        WHERE driver_id IS NOT NULL AND driver_id != '' 
        GROUP BY driver_id 
        HAVING COUNT(*) > 1
        ORDER BY count DESC
    ");
    
    $duplicateDriverIds = $stmt->fetchAll(PDO::FETCH_OBJ);
    if (!empty($duplicateDriverIds)) {
        echo "❌ FOUND " . count($duplicateDriverIds) . " DUPLICATE DRIVER IDs:\n";
        foreach ($duplicateDriverIds as $dup) {
            echo "   • Driver ID: {$dup->driver_id} (Count: {$dup->count})\n";
        }
    } else {
        echo "✅ No duplicate driver IDs found\n";
    }
    
    echo "\n";
    
    // 4. Check duplicate NIN numbers
    echo "4. CHECKING DRIVERS_NORMALIZED - DUPLICATE NIN NUMBERS:\n";
    echo str_repeat("-", 50) . "\n";
    
    $stmt = $pdo->query("
        SELECT nin_number, COUNT(*) as count 
        FROM drivers 
        WHERE nin_number IS NOT NULL AND nin_number != '' 
        GROUP BY nin_number 
        HAVING COUNT(*) > 1
        ORDER BY count DESC
    ");
    
    $duplicateNINs = $stmt->fetchAll(PDO::FETCH_OBJ);
    if (!empty($duplicateNINs)) {
        echo "❌ FOUND " . count($duplicateNINs) . " DUPLICATE NIN NUMBERS:\n";
        foreach ($duplicateNINs as $dup) {
            echo "   • NIN: {$dup->nin_number} (Count: {$dup->count})\n";
        }
    } else {
        echo "✅ No duplicate NIN numbers found\n";
    }
    
    echo "\n";
    
    // 5. Check admin users duplicates
    echo "5. CHECKING ADMIN_USERS - DUPLICATE EMAILS:\n";
    echo str_repeat("-", 50) . "\n";
    
    $stmt = $pdo->query("
        SELECT email, COUNT(*) as count 
        FROM admin_users 
        WHERE email IS NOT NULL AND email != '' 
        GROUP BY email 
        HAVING COUNT(*) > 1
        ORDER BY count DESC
    ");
    
    $duplicateAdminEmails = $stmt->fetchAll(PDO::FETCH_OBJ);
    if (!empty($duplicateAdminEmails)) {
        echo "❌ FOUND " . count($duplicateAdminEmails) . " DUPLICATE ADMIN EMAILS:\n";
        foreach ($duplicateAdminEmails as $dup) {
            echo "   • Email: {$dup->email} (Count: {$dup->count})\n";
            
            // Get details
            $detailStmt = $pdo->prepare("
                SELECT id, name, email, role, created_at, deleted_at 
                FROM admin_users 
                WHERE email = ? 
                ORDER BY created_at
            ");
            $detailStmt->execute([$dup->email]);
            $records = $detailStmt->fetchAll(PDO::FETCH_OBJ);
            
            foreach ($records as $record) {
                $status = $record->deleted_at ? '[DELETED]' : '[ACTIVE]';
                echo "     → ID: {$record->id}, Name: {$record->name}, Role: {$record->role}, Created: {$record->created_at} {$status}\n";
            }
            echo "\n";
        }
    } else {
        echo "✅ No duplicate admin emails found\n";
    }
    
    echo "\n";
    
    // 6. Check tables that exist
    echo "6. CHECKING EXISTING TABLES:\n";
    echo str_repeat("-", 50) . "\n";
    
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_NUM);
    
    $importantTables = [
        'admin_users', 'drivers', 'companies', 'company_requests', 
        'driver_matches', 'driver_documents', 'driver_performances', 
        'driver_locations', 'driver_banking_details', 'guarantors', 'commissions'
    ];
    
    echo "Tables that exist:\n";
    foreach ($tables as $table) {
        $tableName = $table[0];
        if (in_array($tableName, $importantTables)) {
            // Get row count
            $countStmt = $pdo->query("SELECT COUNT(*) as count FROM `{$tableName}`");
            $count = $countStmt->fetch(PDO::FETCH_OBJ)->count;
            echo "   ✅ {$tableName} ({$count} records)\n";
        }
    }
    
    echo "\nTables that don't exist:\n";
    foreach ($importantTables as $expectedTable) {
        $exists = false;
        foreach ($tables as $table) {
            if ($table[0] === $expectedTable) {
                $exists = true;
                break;
            }
        }
        if (!$exists) {
            echo "   ❌ {$expectedTable} (missing)\n";
        }
    }
    
} catch (PDOException $e) {
    echo "Database Error: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n=== DUPLICATE CHECK COMPLETE ===\n";

?>