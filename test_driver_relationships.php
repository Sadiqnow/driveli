<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "🔍 Testing DriverNormalized relationships...\n\n";

try {
    $models = [
        'DriverDocument' => App\Models\DriverDocument::class,
        'CompanyRequest' => App\Models\CompanyRequest::class,
        'DriverMatch' => App\Models\DriverMatch::class,
        'Guarantor' => App\Models\Guarantor::class,
        'Commission' => App\Models\Commission::class,
        'DriverLocation' => App\Models\DriverLocation::class,
    ];

    foreach ($models as $name => $class) {
        try {
            // Test if model can be instantiated
            $instance = new $class();
            echo "✓ {$name}: Model instantiated successfully\n";
            
            // Test if driver relationship exists
            if (method_exists($instance, 'driver')) {
                $relation = $instance->driver();
                $relatedModel = $relation->getRelated();
                
                if ($relatedModel instanceof App\Models\DriverNormalized) {
                    echo "  ✓ Driver relationship correctly points to DriverNormalized\n";
                } elseif ($relatedModel instanceof App\Models\Driver) {
                    echo "  ⚠ Driver relationship still points to old Driver model\n";
                } else {
                    echo "  ❌ Driver relationship points to unknown model: " . get_class($relatedModel) . "\n";
                }
            } else {
                echo "  - No driver relationship method found\n";
            }
            
        } catch (Exception $e) {
            echo "❌ {$name}: Error - " . $e->getMessage() . "\n";
        }
        echo "\n";
    }
    
    // Test DriverNormalized model itself
    echo "🧪 Testing DriverNormalized model...\n";
    $driver = new App\Models\DriverNormalized();
    echo "✓ Table name: " . $driver->getTable() . "\n";
    echo "✓ Uses SoftDeletes: " . (in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses($driver)) ? 'Yes' : 'No') . "\n";
    
    // Test count (if table exists)
    try {
        $count = App\Models\DriverNormalized::count();
        echo "✓ Record count: {$count}\n";
    } catch (Exception $e) {
        echo "⚠ Cannot count records (table may not exist): " . $e->getMessage() . "\n";
    }
    
    echo "\n✅ Relationship testing completed!\n";

} catch (Exception $e) {
    echo "❌ Critical error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}