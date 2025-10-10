#!/usr/bin/env php
<?php

echo "🔧 Drivelink Soft Deletes Fix Script\n";
echo "===================================\n\n";

// 1. Direct database connection approach
echo "1. Connecting to database...\n";

try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=drivelink_db;charset=utf8mb4', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
    
    echo "✅ Database connection successful!\n\n";
    
    // 2. Check which tables need deleted_at columns
    $tablesToFix = ['drivers', 'admin_users', 'companies'];
    
    foreach ($tablesToFix as $tableName) {
        echo "2. Checking table: {$tableName}\n";
        
        // Check if table exists
        $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
        $stmt->execute([$tableName]);
        $tableExists = $stmt->fetch();
        
        if (!$tableExists) {
            echo "⚠️  Table {$tableName} does not exist, skipping...\n\n";
            continue;
        }
        
        echo "✅ Table {$tableName} exists\n";
        
        // Check if deleted_at column exists
        $stmt = $pdo->prepare("SHOW COLUMNS FROM {$tableName} LIKE 'deleted_at'");
        $stmt->execute();
        $columnExists = $stmt->fetch();
        
        if ($columnExists) {
            echo "✅ deleted_at column already exists in {$tableName}\n\n";
        } else {
            echo "❌ deleted_at column missing in {$tableName}, adding it...\n";
            
            try {
                $sql = "ALTER TABLE {$tableName} ADD COLUMN deleted_at TIMESTAMP NULL DEFAULT NULL";
                $pdo->exec($sql);
                echo "✅ Successfully added deleted_at column to {$tableName}!\n\n";
            } catch (Exception $e) {
                echo "❌ Failed to add deleted_at column to {$tableName}: " . $e->getMessage() . "\n\n";
            }
        }
    }
    
    // 3. Test queries that use soft deletes
    echo "3. Testing soft delete functionality...\n";
    
    try {
        // Test driver query
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM drivers WHERE deleted_at IS NULL");
        $stmt->execute();
        $result = $stmt->fetch();
        echo "✅ Drivers query works! Found {$result['count']} active drivers\n";
        
        // Test admin_users query  
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM admin_users WHERE deleted_at IS NULL");
        $stmt->execute();
        $result = $stmt->fetch();
        echo "✅ Admin users query works! Found {$result['count']} active admin users\n";
        
        // Test companies query if table exists
        $stmt = $pdo->prepare("SHOW TABLES LIKE 'companies'");
        $stmt->execute();
        if ($stmt->fetch()) {
            $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM companies WHERE deleted_at IS NULL");
            $stmt->execute();
            $result = $stmt->fetch();
            echo "✅ Companies query works! Found {$result['count']} active companies\n";
        }
        
    } catch (Exception $e) {
        echo "❌ Query test failed: " . $e->getMessage() . "\n";
    }
    
    echo "\n🎉 Soft deletes fix completed!\n";
    echo "You should now be able to use Driver::all() and other Eloquent queries without errors.\n";
    
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    echo "Please check your database configuration in .env file.\n";
}