<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Property;
use App\Models\User;
use App\Models\Company;
use Illuminate\Support\Facades\Hash;

echo "=== Creating Test Data for Security Testing ===\n\n";

// Create Company 1 with admin and properties
$company1 = Company::create([
    'name' => 'Test Company 1',
    'address' => '123 Test St',
    'phone' => '555-0001',
    'email' => 'company1@test.com',
    'admin_id' => null,
]);

$companyAdmin1 = User::create([
    'name' => 'Company 1 Admin',
    'email' => 'admin1@test.com',
    'password' => Hash::make('password'),
    'user_type' => User::TYPE_COMPANY_ADMIN,
    'company_id' => $company1->id,
]);

$company1->update(['admin_id' => $companyAdmin1->id]);

// Create Company 2 with admin and properties
$company2 = Company::create([
    'name' => 'Test Company 2',
    'address' => '456 Test Ave',
    'phone' => '555-0002',
    'email' => 'company2@test.com',
    'admin_id' => null,
]);

$companyAdmin2 = User::create([
    'name' => 'Company 2 Admin',
    'email' => 'admin2@test.com',
    'password' => Hash::make('password'),
    'user_type' => User::TYPE_COMPANY_ADMIN,
    'company_id' => $company2->id,
]);

$company2->update(['admin_id' => $companyAdmin2->id]);

// Create Solo Handymen
$soloHandyman1 = User::create([
    'name' => 'Solo Handyman 1',
    'email' => 'solo1@test.com',
    'password' => Hash::make('password'),
    'user_type' => User::TYPE_SOLO_HANDYMAN,
    'company_id' => null,
]);

$soloHandyman2 = User::create([
    'name' => 'Solo Handyman 2',
    'email' => 'solo2@test.com',
    'password' => Hash::make('password'),
    'user_type' => User::TYPE_SOLO_HANDYMAN,
    'company_id' => null,
]);

echo "Created users:\n";
echo "- Company 1 Admin: {$companyAdmin1->email}\n";
echo "- Company 2 Admin: {$companyAdmin2->email}\n";
echo "- Solo Handyman 1: {$soloHandyman1->email}\n";
echo "- Solo Handyman 2: {$soloHandyman2->email}\n\n";

// Create properties for each
$properties = [
    // Company 1 properties    Property::create([
        'name' => 'Company 1 Property A',
        'city' => 'Lefkoşa',
        'district' => 'Köşklüçiftlik',
        'street' => 'Test Street 1',
        'door_apartment_no' => '1A',
        'company_id' => $company1->id,
        'user_id' => null,
    ]),
    Property::create([
        'name' => 'Company 1 Property B',
        'city' => 'Lefkoşa',
        'district' => 'Köşklüçiftlik',
        'street' => 'Test Street 2',
        'door_apartment_no' => '2B',
        'company_id' => $company1->id,
        'user_id' => null,
    ]),
    // Company 2 properties
    Property::create([
        'name' => 'Company 2 Property A',
        'city' => 'Mağusa',
        'district' => 'Karakol',
        'street' => 'Test Avenue 1',
        'door_apartment_no' => '3A',
        'company_id' => $company2->id,
        'user_id' => null,
    ]),
    Property::create([
        'name' => 'Company 2 Property B',
        'city' => 'Mağusa',
        'district' => 'Karakol',
        'street' => 'Test Avenue 2',
        'door_apartment_no' => '4B',
        'company_id' => $company2->id,
        'user_id' => null,
    ]),

    // Solo Handyman 1 properties
    Property::create([
        'name' => 'Solo 1 Property A',
        'city' => 'Girne',
        'district' => 'Karşıyaka',
        'street' => 'Solo Street 1',
        'door_apartment_no' => '5A',
        'company_id' => null,
        'user_id' => $soloHandyman1->id,
    ]),
    Property::create([
        'name' => 'Solo 1 Property B',
        'city' => 'Girne',
        'district' => 'Karşıyaka',
        'street' => 'Solo Street 2',
        'door_apartment_no' => '6B',
        'company_id' => null,
        'user_id' => $soloHandyman1->id,
    ]),

    // Solo Handyman 2 properties
    Property::create([
        'name' => 'Solo 2 Property A',
        'city' => 'Güzelyurt',
        'district' => 'Morphou',
        'street' => 'Solo Avenue 1',
        'door_apartment_no' => '7A',
        'company_id' => null,
        'user_id' => $soloHandyman2->id,
    ]),
    Property::create([
        'name' => 'Solo 2 Property B',
        'city' => 'Güzelyurt',
        'district' => 'Morphou',
        'street' => 'Solo Avenue 2',
        'door_apartment_no' => '8B',
        'company_id' => null,
        'user_id' => $soloHandyman2->id,
    ]),
];

echo "Created properties:\n";
foreach ($properties as $prop) {
    $owner = $prop->company_id ? "Company {$prop->company_id}" : "Solo User {$prop->user_id}";
    echo "- {$prop->name} (owned by {$owner})\n";
}

echo "\n=== Testing Data Isolation ===\n";

// Test each user can only see their own properties
$users = [$companyAdmin1, $companyAdmin2, $soloHandyman1, $soloHandyman2];

foreach ($users as $user) {
    echo "\n{$user->name} ({$user->user_type}) should see:\n";

    $accessibleProperties = Property::accessibleBy($user)->get(['id', 'name']);
    echo "  Actually sees {$accessibleProperties->count()} properties:\n";

    foreach ($accessibleProperties as $prop) {
        echo "    - {$prop->name}\n";
    }

    // Expected count
    if ($user->user_type === User::TYPE_COMPANY_ADMIN) {
        $expected = Property::where('company_id', $user->company_id)->count();
        echo "  Expected: {$expected} properties\n";
    } else {
        $expected = Property::where('user_id', $user->id)->count();
        echo "  Expected: {$expected} properties\n";
    }

    $status = ($accessibleProperties->count() === $expected) ? "✅ CORRECT" : "❌ SECURITY ISSUE";
    echo "  Status: {$status}\n";
}

echo "\nTest data creation complete!\n";
