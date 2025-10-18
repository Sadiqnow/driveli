                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                             <?php

$host = '127.0.0.1';
$username = 'root';
$password = '';
$database = 'drivelink_db';

try {
    $pdo = new PDO("mysql:host={$host};dbname={$database}", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare("DESCRIBE drivers");
    $stmt->execute();
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stateIdExists = false;
    foreach($columns as $col) {
        if ($col['Field'] == 'state_id') {
            $stateIdExists = true;
            echo "state_id column exists: Type: {$col['Type']}, Null: {$col['Null']}, Default: {$col['Default']}\n";
        }
    }

    if (!$stateIdExists) {
        echo "state_id column does NOT exist in drivers table.\n";
        echo "Adding the column...\n";

        $pdo->exec("ALTER TABLE drivers ADD COLUMN state_id BIGINT UNSIGNED NULL");
        echo "Added state_id column.\n";
    } else {
        echo "state_id column already exists.\n";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
