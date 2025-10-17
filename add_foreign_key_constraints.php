<?php

require_once 'vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();

class ForeignKeyConstraintAdder
{
    private $constraints = [
        'driver_next_of_kin' => [
            'fk_driver_next_of_kin_driver_id' => 'ALTER TABLE driver_next_of_kin ADD CONSTRAINT fk_driver_next_of_kin_driver_id FOREIGN KEY (driver_id) REFERENCES drivers(id) ON DELETE CASCADE ON UPDATE CASCADE'
        ],
        'driver_performance' => [
            'fk_driver_performance_driver_id' => 'ALTER TABLE driver_performance ADD CONSTRAINT fk_driver_performance_driver_id FOREIGN KEY (driver_id) REFERENCES drivers(id) ON DELETE CASCADE ON UPDATE CASCADE'
        ],
        'driver_banking_details' => [
            'fk_driver_banking_details_driver_id' => 'ALTER TABLE driver_banking_details ADD CONSTRAINT fk_driver_banking_details_driver_id FOREIGN KEY (driver_id) REFERENCES drivers(id) ON DELETE CASCADE ON UPDATE CASCADE'
        ],
        'driver_documents' => [
            'fk_driver_documents_driver_id' => 'ALTER TABLE driver_documents ADD CONSTRAINT fk_driver_documents_driver_id FOREIGN KEY (driver_id) REFERENCES drivers(id) ON DELETE CASCADE ON UPDATE CASCADE'
        ],
        'driver_matches' => [
            'fk_driver_matches_driver_id' => 'ALTER TABLE driver_matches ADD CONSTRAINT fk_driver_matches_driver_id FOREIGN KEY (driver_id) REFERENCES drivers(id) ON DELETE CASCADE ON UPDATE CASCADE'
        ],
        'driver_category_requirements' => [
            'fk_driver_category_requirements_driver_id' => 'ALTER TABLE driver_category_requirements ADD CONSTRAINT fk_driver_category_requirements_driver_id FOREIGN KEY (driver_id) REFERENCES drivers(id) ON DELETE CASCADE ON UPDATE CASCADE'
        ],
    ];

    public function addConstraints()
    {
        echo "🔗 ADDING FOREIGN KEY CONSTRAINTS\n";
        echo "=================================\n\n";

        foreach ($this->constraints as $table => $tableConstraints) {
            echo "Processing table: {$table}\n";

            foreach ($tableConstraints as $constraintName => $sql) {
                try {
                    // Check if constraint already exists
                    $existingConstraints = $this->getExistingConstraints($table);

                    if (in_array($constraintName, $existingConstraints)) {
                        echo "   ⚠️  Constraint {$constraintName} already exists\n";
                        continue;
                    }

                    // Execute the constraint addition
                    DB::statement($sql);
                    echo "   ✅ Added constraint: {$constraintName}\n";

                } catch (\Exception $e) {
                    echo "   ❌ Failed to add {$constraintName}: {$e->getMessage()}\n";
                }
            }
            echo "\n";
        }

        echo "✅ FOREIGN KEY CONSTRAINTS ADDED\n";
    }

    private function getExistingConstraints($table)
    {
        try {
            $databaseName = config('database.connections.mysql.database');
            $constraints = DB::select("
                SELECT CONSTRAINT_NAME
                FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
                WHERE TABLE_SCHEMA = ?
                  AND TABLE_NAME = ?
                  AND CONSTRAINT_TYPE = 'FOREIGN KEY'
            ", [$databaseName, $table]);

            return array_column($constraints, 'CONSTRAINT_NAME');
        } catch (\Exception $e) {
            echo "   ⚠️  Could not check existing constraints: {$e->getMessage()}\n";
            return [];
        }
    }

    public function verifyConstraints()
    {
        echo "🔍 VERIFYING CONSTRAINTS\n";
        echo "======================\n\n";

        foreach ($this->constraints as $table => $tableConstraints) {
            echo "Table: {$table}\n";

            $existing = $this->getExistingConstraints($table);
            $expected = array_keys($tableConstraints);

            foreach ($expected as $constraint) {
                if (in_array($constraint, $existing)) {
                    echo "   ✅ {$constraint}\n";
                } else {
                    echo "   ❌ {$constraint} (missing)\n";
                }
            }
            echo "\n";
        }
    }
}

// Run the constraint addition
$adder = new ForeignKeyConstraintAdder();
$adder->addConstraints();
$adder->verifyConstraints();
