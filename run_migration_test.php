<?php

echo "Testing migration by running the actual migration command...\n";

try {
    // Change to the drivelink directory
    $currentDir = getcwd();
    chdir('C:/xampp/htdocs/drivelink');
    
    // Run the migration command and capture output
    $output = shell_exec('"C:\xampp\php\php.exe" artisan migrate 2>&1');
    
    echo "Migration output:\n";
    echo $output;
    
    // Check if there are any remaining migrations to run
    $statusOutput = shell_exec('"C:\xampp\php\php.exe" artisan migrate:status 2>&1');
    echo "\nMigration status:\n";
    echo $statusOutput;
    
    chdir($currentDir);
    
} catch (Exception $e) {
    echo "Error running migration: " . $e->getMessage() . "\n";
}