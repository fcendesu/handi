<?php

require_once 'vendor/autoload.php';

use App\Models\Discovery;
use App\Models\User;
use App\Models\TransactionLog;
use App\Services\TransactionLogService;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Config;

// Create a minimal Laravel application instance
$app = new Application(realpath(__DIR__));
$app->singleton(
    Illuminate\Contracts\Http\Kernel::class,
    App\Http\Kernel::class
);
$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);
$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

// Load configuration
$app->useConfigPath(__DIR__ . '/config');
$app->useEnvironmentPath(__DIR__);
$app->detectEnvironment(function () {
    return 'local';
});

// Bootstrap the application
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    echo "Testing Transaction Logging System...\n\n";

    // Test 1: Check if TransactionLog table exists and is accessible
    echo "1. Testing TransactionLog model...\n";
    $logCount = TransactionLog::count();
    echo "   Current transaction logs count: $logCount\n";

    // Test 2: Check if we can create a log entry manually
    echo "\n2. Testing manual log creation...\n";

    $testLog = TransactionLog::create([
        'user_id' => 1,
        'entity_type' => 'discovery',
        'entity_id' => 1,
        'discovery_id' => 1,
        'action' => TransactionLog::ACTION_CREATED,
        'performed_by_type' => TransactionLog::PERFORMER_USER,
        'new_values' => json_encode(['test' => 'value']),
        'metadata' => json_encode(['test_run' => true]),
        'ip_address' => '127.0.0.1',
        'user_agent' => 'Test Script'
    ]);

    echo "   Test log created with ID: {$testLog->id}\n";

    // Test 3: Check the service methods
    echo "\n3. Testing TransactionLogService methods...\n";

    // Get a discovery to test with
    $discovery = Discovery::first();
    if ($discovery) {
        echo "   Found discovery ID: {$discovery->id}\n";
        echo "   Customer: {$discovery->customer_name}\n";
        echo "   Status: {$discovery->status}\n";

        // Test logging a status change
        $originalLogsCount = TransactionLog::count();
        TransactionLogService::logStatusChange($discovery, 'pending', 'in_progress');
        $newLogsCount = TransactionLog::count();

        echo "   Status change logged. Logs before: $originalLogsCount, after: $newLogsCount\n";

        if ($newLogsCount > $originalLogsCount) {
            echo "   ✓ Status change logging works!\n";

            // Show the latest log
            $latestLog = TransactionLog::latest()->first();
            echo "   Latest log action: {$latestLog->action}\n";
            echo "   Latest log old values: {$latestLog->old_values}\n";
            echo "   Latest log new values: {$latestLog->new_values}\n";
        } else {
            echo "   ✗ Status change logging failed!\n";
        }
    } else {
        echo "   No discoveries found in database\n";
    }

    // Test 4: Show recent transaction logs
    echo "\n4. Recent transaction logs:\n";
    $recentLogs = TransactionLog::with(['user', 'discovery'])->latest()->take(5)->get();

    foreach ($recentLogs as $log) {
        $userName = $log->user ? $log->user->name : 'System/Customer';
        $discoveryInfo = $log->discovery ? "Discovery #{$log->discovery->id}" : "Discovery #{$log->discovery_id}";
        echo "   - {$log->action} by {$userName} on {$discoveryInfo} at {$log->created_at}\n";
    }

    // Clean up test log
    $testLog->delete();
    echo "\nTest completed successfully!\n";

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
