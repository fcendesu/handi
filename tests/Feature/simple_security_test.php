<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Property;
use App\Models\TransactionLog;

echo "=== SIMPLE SECURITY TEST ===\n";

// Get test users
$companyAdmin = User::where('user_type', User::TYPE_COMPANY_ADMIN)->first();
$soloHandyman = User::where('user_type', User::TYPE_SOLO_HANDYMAN)->first();

if (!$companyAdmin || !$soloHandyman) {
    echo "No test users found\n";
    exit;
}

echo "Company Admin: {$companyAdmin->name} (Company: {$companyAdmin->company_id})\n";
echo "Solo Handyman: {$soloHandyman->name}\n\n";

// Test property access scoping
$companyProperties = Property::accessibleBy($companyAdmin)->pluck('id');
$soloProperties = Property::accessibleBy($soloHandyman)->pluck('id');

echo "Properties accessible:\n";
echo "- Company Admin: " . $companyProperties->implode(', ') . "\n";
echo "- Solo Handyman: " . $soloProperties->implode(', ') . "\n\n";

// All property transaction logs
$allPropertyLogs = TransactionLog::where('entity_type', 'property')->get();
echo "Total property transaction logs: {$allPropertyLogs->count()}\n";

// Company admin's scoped property logs (FIXED logic)
$accessiblePropertyIds = Property::accessibleBy($companyAdmin)->pluck('id');
$companyPropertyLogs = TransactionLog::where('entity_type', 'property')
    ->whereIn('entity_id', $accessiblePropertyIds)
    ->get();

echo "Company admin can see: {$companyPropertyLogs->count()} property logs\n";

// Check if any of these logs belong to solo handyman properties
$soloPropertyIds = Property::where('user_id', $soloHandyman->id)->pluck('id');
$leakedLogs = $companyPropertyLogs->whereIn('entity_id', $soloPropertyIds);

echo "Leaked solo handyman logs: {$leakedLogs->count()}\n";

if ($leakedLogs->count() === 0) {
    echo "✅ SECURITY FIX WORKING: No data leakage\n";
} else {
    echo "❌ SECURITY ISSUE: Data leaked\n";
}

// Test old broken logic for comparison
$brokenPropertyLogs = TransactionLog::where('entity_type', 'property')->get();
$brokenLeaked = $brokenPropertyLogs->whereIn('entity_id', $soloPropertyIds);

echo "\nOLD BROKEN LOGIC would show:\n";
echo "- Total property logs: {$brokenPropertyLogs->count()}\n";
echo "- Leaked solo logs: {$brokenLeaked->count()}\n";

echo "\n=== TEST COMPLETE ===\n";
