<?php

echo "PHP is working!\n";
echo "Current directory: " . __DIR__ . "\n";
echo "PHP version: " . PHP_VERSION . "\n";

if (file_exists('vendor/autoload.php')) {
    echo "Composer autoload exists\n";
} else {
    echo "Composer autoload NOT found\n";
}

if (file_exists('.env')) {
    echo ".env file exists\n";
} else {
    echo ".env file NOT found\n";
}

if (extension_loaded('pdo_mysql')) {
    echo "PDO MySQL extension loaded\n";
} else {
    echo "PDO MySQL extension NOT loaded\n";
}

?>