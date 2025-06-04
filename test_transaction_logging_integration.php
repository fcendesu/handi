<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Models\Item;
use App\Models\Discovery;
use App\Models\User;
use App\Services\TransactionLogService;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    // Get test data
    $item = Item::first();
    $discovery = Discovery::first();
    $user = User::first();

    if (!$item || !$discovery || !$user) {
        echo "Missing test data - Item: " . ($item ? "✓" : "✗") .
            ", Discovery: " . ($discovery ? "✓" : "✗") .
            ", User: " . ($user ? "✓" : "✗") . PHP_EOL;
        exit(1);
    }

    echo "Testing transaction logging..." . PHP_EOL;
    echo "Item ID: " . $item->id . ", Type: " . get_class($item) . PHP_EOL;
    echo "Discovery ID: " . $discovery->id . ", Type: " . get_class($discovery) . PHP_EOL;

    // Test the method call
    $pivotData = [
        'quantity' => 2,
        'custom_price' => 150.00
    ];

    // Test if we can call the method
    TransactionLogService::logItemAttachedToDiscovery($item, $discovery, $pivotData, $user);
    echo "✓ Transaction logging method call successful!" . PHP_EOL;

    // Check if log was created
    $latestLog = App\Models\TransactionLog::latest()->first();
    if ($latestLog) {
        echo "✓ Transaction log created: ID " . $latestLog->id . PHP_EOL;
        echo "  Entity: " . $latestLog->entity_type . " (ID: " . $latestLog->entity_id . ")" . PHP_EOL;
        echo "  Action: " . $latestLog->action . PHP_EOL;
    } else {
        echo "✗ No transaction log found" . PHP_EOL;
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
    echo "Stack trace:" . PHP_EOL;
    echo $e->getTraceAsString() . PHP_EOL;
}
