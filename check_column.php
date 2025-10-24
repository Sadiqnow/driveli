<?php
require 'vendor/autoload.php';

try {
    $pdo = new PDO('mysql:host=localhost;dbname=drivelink_db', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->query('DESCRIBE user_roles');
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $expiresAtExists = false;
    foreach ($columns as $column) {
        if ($column['Field'] === 'expires_at') {
            $expiresAtExists = true;
            break;
        }
    }

    if ($expiresAtExists) {
        echo "Column 'expires_at' exists in user_roles table.\n";
    } else {
        echo "Column 'expires_at' does not exist in user_roles table.\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
