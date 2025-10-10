<?php
// Replace occurrences of 'drivers' -> 'drivers' in PHP files (excluding vendor)
$root = realpath(__DIR__ . '/..');
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root));
$changed = [];
$skipDirs = ['vendor', '.git', 'node_modules', 'storage'];
foreach ($it as $file) {
    if (!$file->isFile()) continue;
    $path = $file->getPathname();
    $ext = pathinfo($path, PATHINFO_EXTENSION);
    if ($ext !== 'php' && $ext !== 'sql' && $ext !== 'md') continue; // handle php, sql, md
    // skip vendor and other dirs
    foreach ($skipDirs as $d) {
        if (strpos($path, DIRECTORY_SEPARATOR . $d . DIRECTORY_SEPARATOR) !== false) continue 2;
    }
    $contents = file_get_contents($path);
    $original = $contents;
    $contents = str_replace("drivers", "drivers", $contents);
    $contents = str_replace("Drivers", "Drivers", $contents);
    if ($contents !== $original) {
        file_put_contents($path, $contents);
        $changed[] = $path;
        echo "Updated: $path\n";
    }
}
echo "Done. Total files updated: " . count($changed) . "\n";
