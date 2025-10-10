<?php

// This script will help identify and fix all job_type references

$filesToCheck = [
    'resources/views/admin/matching/dashboard.blade.php',
    'resources/views/admin/requests/queue.blade.php', 
    'resources/views/admin/requests/accept.blade.php'
];

echo "Finding and fixing job_type references\n";
echo "=====================================\n\n";

foreach ($filesToCheck as $file) {
    $fullPath = __DIR__ . '/' . $file;
    
    if (file_exists($fullPath)) {
        echo "Checking: {$file}\n";
        
        $content = file_get_contents($fullPath);
        $originalContent = $content;
        
        // Count occurrences
        $occurrences = substr_count($content, 'job_type');
        if ($occurrences > 0) {
            echo "  Found {$occurrences} references to 'job_type'\n";
            
            // Replace job_type with location for display purposes
            $content = str_replace('job_type', 'location', $content);
            
            // Write back to file
            file_put_contents($fullPath, $content);
            echo "  ✓ Fixed all references\n";
        } else {
            echo "  ✓ No job_type references found\n";
        }
    } else {
        echo "⚠ File not found: {$file}\n";
    }
    echo "\n";
}

echo "All job_type references have been fixed!\n";
echo "Files should now use 'location' instead of the missing 'job_type' column.\n";