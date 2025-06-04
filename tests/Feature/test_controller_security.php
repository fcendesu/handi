<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Property;
use App\Models\TransactionLog;
use App\Http\Controllers\DiscoveryController;
use Illuminate\Http\Request;

echo "=== TESTING CONTROLLER TRANSACTION LOG METHOD ===\n\n";

function testControllerMethod()
{
    // Get test users
    $companyAdmin = User::where('user_type', User::TYPE_COMPANY_ADMIN)->first();
    $soloHandyman = User::where('user_type', User::TYPE_SOLO_HANDYMAN)->first();

    if (!$companyAdmin || !$soloHandyman) {
        echo "Missing test users\n";
        return;
    }

    echo "Testing users:\n";
    echo "- Company Admin: {$companyAdmin->name} (Company: {$companyAdmin->company_id})\n";
    echo "- Solo Handyman: {$soloHandyman->name}\n\n";

    // Get properties
    $companyProperties = Property::where('company_id', $companyAdmin->company_id)->get();
    $soloProperties = Property::where('user_id', $soloHandyman->id)->get();

    echo "Properties:\n";
    echo "- Company properties: {$companyProperties->count()}\n";
    echo "- Solo handyman properties: {$soloProperties->count()}\n\n";

    // Create some test transaction logs if they don't exist
    $existingPropertyLogs = TransactionLog::where('entity_type', 'property')->count();
    echo "Existing property transaction logs: {$existingPropertyLogs}\n";

    if ($existingPropertyLogs === 0) {
        echo "Creating test transaction logs...\n";

        // Create logs for company property
        if ($companyProperties->isNotEmpty()) {
            TransactionLog::create([
                'user_id' => $companyAdmin->id,
                'action' => 'created',
                'entity_type' => 'property',
                'entity_id' => $companyProperties->first()->id,
                'metadata' => ['test' => 'company_property_log'],
                'performed_by_type' => 'user',
                'performed_by_identifier' => $companyAdmin->id,
            ]);
        }

        // Create logs for solo handyman property
        if ($soloProperties->isNotEmpty()) {
            TransactionLog::create([
                'user_id' => $soloHandyman->id,
                'action' => 'created',
                'entity_type' => 'property',
                'entity_id' => $soloProperties->first()->id,
                'metadata' => ['test' => 'solo_property_log'],
                'performed_by_type' => 'user',
                'performed_by_identifier' => $soloHandyman->id,
            ]);
        }

        echo "Test logs created.\n";
    }

    echo "\n=== TESTING FIXED SCOPING LOGIC ===\n";

    // Test company admin access
    echo "\n1. Company Admin Access:\n";
    auth()->login($companyAdmin);

    $discoveryIds = collect(); // No discoveries for this test
    $companyUserIds = User::where('company_id', $companyAdmin->company_id)->pluck('id');
    $accessiblePropertyIds = Property::accessibleBy($companyAdmin)->pluck('id');

    echo "   - Accessible property IDs: " . $accessiblePropertyIds->implode(', ') . "\n";

    $query = TransactionLog::where(function ($q) use ($discoveryIds, $companyUserIds, $accessiblePropertyIds) {
        $q->whereIn('discovery_id', $discoveryIds)
            ->orWhereIn('user_id', $companyUserIds)
            ->orWhere(function ($propertyQuery) use ($accessiblePropertyIds) {
                $propertyQuery->where('entity_type', 'property')
                    ->whereIn('entity_id', $accessiblePropertyIds);
            });
    });

    $companyLogs = $query->where('entity_type', 'property')->get();
    echo "   - Property logs visible to company admin: {$companyLogs->count()}\n";

    foreach ($companyLogs as $log) {
        $property = Property::find($log->entity_id);
        $ownerType = $property->company_id ? 'company' : 'solo';
        echo "     * Log {$log->id}: Property {$log->entity_id} ({$ownerType}), Action: {$log->action}\n";
    }

    // Test solo handyman access
    echo "\n2. Solo Handyman Access:\n";
    auth()->login($soloHandyman);

    $soloDiscoveryIds = collect(); // No discoveries for this test
    $soloQuery = TransactionLog::where(function ($q) use ($soloHandyman, $soloDiscoveryIds) {
        $q->whereIn('discovery_id', $soloDiscoveryIds)
            ->orWhere('user_id', $soloHandyman->id);
    });

    $soloLogs = $soloQuery->where('entity_type', 'property')->get();
    echo "   - Property logs visible to solo handyman: {$soloLogs->count()}\n";

    foreach ($soloLogs as $log) {
        $property = Property::find($log->entity_id);
        $ownerType = $property->company_id ? 'company' : 'solo';
        echo "     * Log {$log->id}: Property {$log->entity_id} ({$ownerType}), Action: {$log->action}\n";
    }

    // Verify security: Check if company admin logs include solo handyman properties
    $soloPropertyIds = Property::where('user_id', $soloHandyman->id)->pluck('id');
    $leakedLogs = $companyLogs->whereIn('entity_id', $soloPropertyIds);

    echo "\n=== SECURITY VERIFICATION ===\n";
    echo "Solo handyman property IDs: " . $soloPropertyIds->implode(', ') . "\n";
    echo "Leaked logs to company admin: {$leakedLogs->count()}\n";

    if ($leakedLogs->count() === 0) {
        echo "✅ SECURITY VERIFIED: No data leakage!\n";
    } else {
        echo "❌ SECURITY ISSUE: Data leakage detected!\n";
        foreach ($leakedLogs as $log) {
            echo "   - Leaked log {$log->id} for property {$log->entity_id}\n";
        }
    }
}

try {
    testControllerMethod();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
