<?php

echo "=== LGA Setup Verification ===\n";

// Test database connection
try {
    $pdo = new PDO('mysql:host=127.0.0.1;dbname=drivelink_db', 'root', '');
    echo "✓ Database connection successful\n";
    
    // Check if tables exist
    $tables = ['states', 'local_governments', 'nationalities', 'banks'];
    foreach ($tables as $table) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) as count FROM {$table}");
            $count = $stmt->fetch()['count'];
            echo "✓ Table '{$table}' exists with {$count} records\n";
        } catch (PDOException $e) {
            echo "✗ Table '{$table}' missing or error: " . $e->getMessage() . "\n";
        }
    }
    
    // If no data, let's seed it
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM states");
    $stateCount = $stmt->fetch()['count'];
    
    if ($stateCount == 0) {
        echo "\n--- Seeding initial data ---\n";
        
        // Insert Nigerian states
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
        
        foreach ($states as $state) {
            $stmt = $pdo->prepare("INSERT INTO states (name, code, created_at, updated_at) VALUES (?, ?, NOW(), NOW())");
            $stmt->execute([$state['name'], $state['code']]);
        }
        echo "✓ Inserted " . count($states) . " states\n";
        
        // Insert sample LGAs for Lagos
        $lagosStmt = $pdo->query("SELECT id FROM states WHERE code = 'LA'");
        $lagosId = $lagosStmt->fetch()['id'];
        
        $lagosLgas = [
            'Agege', 'Ajeromi-Ifelodun', 'Alimosho', 'Amuwo-Odofin', 'Apapa',
            'Badagry', 'Epe', 'Eti Osa', 'Ibeju-Lekki', 'Ifako-Ijaiye',
            'Ikeja', 'Ikorodu', 'Kosofe', 'Lagos Island', 'Lagos Mainland',
            'Mushin', 'Ojo', 'Oshodi-Isolo', 'Shomolu', 'Surulere'
        ];
        
        foreach ($lagosLgas as $lga) {
            $stmt = $pdo->prepare("INSERT INTO local_governments (state_id, name, created_at, updated_at) VALUES (?, ?, NOW(), NOW())");
            $stmt->execute([$lagosId, $lga]);
        }
        echo "✓ Inserted " . count($lagosLgas) . " LGAs for Lagos\n";
        
        // Insert sample LGAs for FCT
        $fctStmt = $pdo->query("SELECT id FROM states WHERE code = 'FC'");
        $fctId = $fctStmt->fetch()['id'];
        
        $fctLgas = [
            'Abaji', 'Bwari', 'Gwagwalada', 'Kuje', 'Kwali', 'Municipal Area Council'
        ];
        
        foreach ($fctLgas as $lga) {
            $stmt = $pdo->prepare("INSERT INTO local_governments (state_id, name, created_at, updated_at) VALUES (?, ?, NOW(), NOW())");
            $stmt->execute([$fctId, $lga]);
        }
        echo "✓ Inserted " . count($fctLgas) . " LGAs for FCT\n";
    }
    
    // Final count verification
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM states");
    $finalStates = $stmt->fetch()['count'];
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM local_governments");
    $finalLgas = $stmt->fetch()['count'];
    
    echo "\n=== Final Status ===\n";
    echo "States: {$finalStates}\n";
    echo "LGAs: {$finalLgas}\n";
    
    if ($finalStates > 0 && $finalLgas > 0) {
        echo "✓ LGA setup is complete and working!\n";
        echo "\nAPI endpoints should now work:\n";
        echo "- GET /api/states\n";
        echo "- GET /api/states/{stateId}/lgas\n";
        echo "\nLGA dropdown should now populate correctly.\n";
    } else {
        echo "✗ Setup incomplete - data is missing\n";
    }
    
} catch (PDOException $e) {
    echo "✗ Database error: " . $e->getMessage() . "\n";
}