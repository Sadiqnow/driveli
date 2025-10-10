<?php

// List of model files that need to be updated
$modelFiles = [
    'app/Models/DriverBankingDetail.php',
    'app/Models/DriverEmploymentHistory.php',
    'app/Models/DriverLocation.php',
    'app/Models/DriverNextOfKin.php',
    'app/Models/DriverPerformance.php',
    'app/Models/DriverReferee.php',
    'app/Models/DriverPreference.php',
];

$replacements = [
    'return $this->belongsTo(Driver::class);' => 'return $this->belongsTo(DriverNormalized::class, \'driver_id\');',
    'return $this->belongsTo(Driver::class, \'driver_id\');' => 'return $this->belongsTo(DriverNormalized::class, \'driver_id\');',
];

echo "Fixing driver relationships in model files...\n\n";

foreach ($modelFiles as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        $originalContent = $content;
        
        foreach ($replacements as $search => $replace) {
            $content = str_replace($search, $replace, $content);
        }
        
        if ($content !== $originalContent) {
            file_put_contents($file, $content);
            echo "✓ Updated: $file\n";
        } else {
            echo "- No changes needed: $file\n";
        }
    } else {
        echo "⚠ File not found: $file\n";
    }
}

echo "\n✅ Driver relationship fixes completed!\n";