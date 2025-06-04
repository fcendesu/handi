<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Property;
use App\Models\User;

echo "=== Property Ownership Debug ===\n\n";

// Check all properties
$properties = Property::all(['id', 'name', 'company_id', 'user_id']);
echo "Total properties: " . $properties->count() . "\n\n";

echo "Properties ownership data:\n";
foreach ($properties as $prop) {
    $company_id = $prop->company_id ?? 'NULL';
    $user_id = $prop->user_id ?? 'NULL';
    echo "ID: {$prop->id}, Name: {$prop->name}, Company ID: {$company_id}, User ID: {$user_id}\n";
}

echo "\n=== User Types ===\n";
$users = User::all(['id', 'name', 'email', 'user_type', 'company_id']);
foreach ($users as $user) {
    $company_id = $user->company_id ?? 'NULL';
    echo "ID: {$user->id}, Name: {$user->name}, Type: {$user->user_type}, Company ID: {$company_id}\n";
}

echo "\n=== Testing Scoping ===\n";

// Test scoping for each user type
foreach ($users as $user) {
    echo "\nTesting user: {$user->name} (Type: {$user->user_type})\n";

    $accessibleProperties = Property::accessibleBy($user)->get(['id', 'name']);
    echo "Can access " . $accessibleProperties->count() . " properties:\n";

    foreach ($accessibleProperties as $prop) {
        echo "  - {$prop->name} (ID: {$prop->id})\n";
    }
}

echo "\n=== Data Integrity Check ===\n";
$invalidProperties = Property::whereNotNull('company_id')->whereNotNull('user_id')->get();
echo "Properties with both company_id AND user_id (data integrity issue): " . $invalidProperties->count() . "\n";

foreach ($invalidProperties as $prop) {
    echo "  - INVALID: {$prop->name} (ID: {$prop->id}, Company: {$prop->company_id}, User: {$prop->user_id})\n";
}
