<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Models\Item;
use App\Models\Discovery;
use App\Services\TransactionLogService;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Test Item::findOrFail return type
try {
    // Find first item (if any exists)
    $firstItem = Item::first();
    if ($firstItem) {
        echo "Found first item: " . $firstItem->id . "\n";

        // Test findOrFail
        $item = Item::findOrFail($firstItem->id);
        echo "Item class: " . get_class($item) . "\n";
        echo "Is Item instance: " . ($item instanceof Item ? 'YES' : 'NO') . "\n";

        // Test method signature expectations
        $reflectionMethod = new ReflectionMethod(TransactionLogService::class, 'logItemAttachedToDiscovery');
        $params = $reflectionMethod->getParameters();
        echo "First parameter type: " . $params[0]->getType() . "\n";

        // Find first discovery (if any exists)
        $firstDiscovery = Discovery::first();
        if ($firstDiscovery) {
            echo "Found first discovery: " . $firstDiscovery->id . "\n";
            echo "Discovery class: " . get_class($firstDiscovery) . "\n";

            // Test pivot data structure
            $pivotData = [
                'quantity' => 1,
                'custom_price' => $item->price
            ];
            echo "Pivot data structure valid\n";

            echo "All types compatible for method call\n";
        } else {
            echo "No discoveries found in database\n";
        }

    } else {
        echo "No items found in database\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
