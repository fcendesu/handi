<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Property;
use App\Models\TransactionLog;
use App\Models\Company;

echo "=== TESTING TRANSACTION LOG SECURITY ===\n\n";

// Test the fixed transaction log scoping
function testTransactionLogSecurity()
{
    echo "1. Getting users and properties for testing...\n";
    // Get a company admin
    $companyAdmin = User::whereHas('company')->where('user_type', User::TYPE_COMPANY_ADMIN)->first();

    // Get a solo handyman
    $soloHandyman = User::where('user_type', User::TYPE_SOLO_HANDYMAN)->whereNull('company_id')->first();

    if (!$companyAdmin) {
        echo "   ❌ No company admin found\n";
        return;
    }

    if (!$soloHandyman) {
        echo "   ❌ No solo handyman found\n";
        return;
    }

    echo "   Company Admin: {$companyAdmin->name} (ID: {$companyAdmin->id}, Company: {$companyAdmin->company_id})\n";
    echo "   Solo Handyman: {$soloHandyman->name} (ID: {$soloHandyman->id})\n\n";

    // Get company properties (should be visible to company admin)
    $companyProperties = Property::where('company_id', $companyAdmin->company_id)->get();
    echo "2. Company properties accessible to company admin: " . $companyProperties->count() . "\n";

    // Get solo handyman properties (should NOT be visible to company admin)
    $soloProperties = Property::where('user_id', $soloHandyman->id)->get();
    echo "   Solo handyman properties: " . $soloProperties->count() . "\n\n";

    // Test property scoping first
    echo "3. Testing Property scoping (baseline):\n";
    $companyAccessibleProperties = Property::accessibleBy($companyAdmin)->get();
    $soloAccessibleProperties = Property::accessibleBy($soloHandyman)->get();

    echo "   Company admin can access {$companyAccessibleProperties->count()} properties\n";
    echo "   Solo handyman can access {$soloAccessibleProperties->count()} properties\n\n";

    // Now test transaction log scoping with the FIXED logic
    echo "4. Testing FIXED Transaction Log scoping:\n";

    // Simulate the FIXED logic for company admin
    $discoveryIds = collect(); // No discoveries for this test
    $companyUserIds = User::where('company_id', $companyAdmin->company_id)->pluck('id');
    $accessiblePropertyIds = Property::accessibleBy($companyAdmin)->pluck('id');

    $companyAdminQuery = TransactionLog::where(function ($q) use ($discoveryIds, $companyUserIds, $accessiblePropertyIds) {
        $q->whereIn('discovery_id', $discoveryIds)
            ->orWhereIn('user_id', $companyUserIds)
            ->orWhere(function ($propertyQuery) use ($accessiblePropertyIds) {
                $propertyQuery->where('entity_type', 'property')
                    ->whereIn('entity_id', $accessiblePropertyIds);
            });
    });

    $companyAdminPropertyLogs = (clone $companyAdminQuery)->where('entity_type', 'property')->get();

    echo "   Company admin can see {$companyAdminPropertyLogs->count()} property transaction logs\n";

    // Check if company admin can see solo handyman's property logs (should be 0)
    $soloPropertyIds = Property::where('user_id', $soloHandyman->id)->pluck('id');
    $unauthorizedLogs = $companyAdminPropertyLogs->whereIn('entity_id', $soloPropertyIds);

    echo "   Company admin seeing solo handyman's property logs: {$unauthorizedLogs->count()} (should be 0)\n";

    if ($unauthorizedLogs->count() === 0) {
        echo "   ✅ SECURITY FIX WORKING: Company admin cannot see solo handyman's property logs\n";
    } else {
        echo "   ❌ SECURITY ISSUE: Company admin can still see solo handyman's property logs!\n";
        foreach ($unauthorizedLogs as $log) {
            echo "      - Log ID {$log->id}: Property {$log->entity_id}, Action: {$log->action}\n";
        }
    }

    // Test solo handyman scoping
    $soloDiscoveryIds = \App\Models\Discovery::where('creator_id', $soloHandyman->id)->pluck('id');
    $soloHandymanQuery = TransactionLog::where(function ($q) use ($soloHandyman, $soloDiscoveryIds) {
        $q->whereIn('discovery_id', $soloDiscoveryIds)
            ->orWhere('user_id', $soloHandyman->id);
    });

    $soloPropertyLogs = (clone $soloHandymanQuery)->where('entity_type', 'property')->get();
    echo "   Solo handyman can see {$soloPropertyLogs->count()} property transaction logs\n";

    // Test the OLD BROKEN logic for comparison
    echo "\n5. Testing OLD BROKEN logic (for comparison):\n";
    $brokenQuery = TransactionLog::where(function ($q) use ($discoveryIds, $companyUserIds) {
        $q->whereIn('discovery_id', $discoveryIds)
            ->orWhereIn('user_id', $companyUserIds)
            ->orWhere('entity_type', 'property'); // This was the bug!
    });

    $brokenPropertyLogs = (clone $brokenQuery)->where('entity_type', 'property')->get();
    $brokenUnauthorizedLogs = $brokenPropertyLogs->whereIn('entity_id', $soloPropertyIds);

    echo "   OLD logic: Company admin would see {$brokenPropertyLogs->count()} property transaction logs\n";
    echo "   OLD logic: Company admin would see {$brokenUnauthorizedLogs->count()} solo handyman's property logs\n";

    if ($brokenUnauthorizedLogs->count() > 0) {
        echo "   ❌ OLD LOGIC WAS BROKEN: Would expose solo handyman's data\n";
    }

    echo "\n=== TEST COMPLETE ===\n";
}

try {
    testTransactionLogSecurity();
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
