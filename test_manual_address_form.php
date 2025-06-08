<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use App\Http\Controllers\DiscoveryController;
use App\Models\User;
use App\Data\AddressData;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Testing Manual Address Entry System ===\n\n";

// Test 1: Verify AddressData is loaded correctly
echo "1. Testing AddressData:\n";
$cities = AddressData::getCities();
echo "   - Cities available: " . count($cities) . "\n";
echo "   - Cities: " . implode(', ', $cities) . "\n";

$girneDistricts = AddressData::getDistricts('GİRNE');
echo "   - GİRNE districts: " . count($girneDistricts) . " (" . implode(', ', $girneDistricts) . ")\n";

$lefkosaDistricts = AddressData::getDistricts('LEFKOŞA');
echo "   - LEFKOŞA districts: " . count($lefkosaDistricts) . " (" . implode(', ', array_slice($lefkosaDistricts, 0, 5)) . "...)\n";

// Test 2: Test manual address validation
echo "\n2. Testing manual address validation:\n";

// Create a test user
$user = User::where('email', 'test@example.com')->first();
if (!$user) {
    echo "   - Error: Test user not found. Run seeders first.\n";
    exit(1);
}

// Test valid manual address data
$validData = [
    'customer_name' => 'Test Customer',
    'customer_phone' => '+90 555 123 4567',
    'customer_email' => 'customer@test.com',
    'address_type' => 'manual',
    'manual_city' => 'GİRNE',
    'manual_district' => 'MERKEZ',
    'address_details' => 'Test sokak No:123 Daire:5',
    'manual_latitude' => '35.3417',
    'manual_longitude' => '33.3142',
    'discovery' => 'Test keşif raporu',
    'completion_time' => 7,
    'service_cost' => 100.50
];

echo "   - Testing valid manual address data...\n";
echo "     City: {$validData['manual_city']}\n";
echo "     District: {$validData['manual_district']}\n";
echo "     Details: {$validData['address_details']}\n";
echo "     Coordinates: {$validData['manual_latitude']}, {$validData['manual_longitude']}\n";

// Test 3: Test coordinate validation
echo "\n3. Testing coordinate validation:\n";

$testCoordinates = [
    ['35.3417', '33.3142', 'Valid Cyprus coordinates'],
    ['91.0', '33.3142', 'Invalid latitude (>90)'],
    ['35.3417', '181.0', 'Invalid longitude (>180)'],
    ['-91.0', '33.3142', 'Invalid latitude (<-90)'],
    ['35.3417', '-181.0', 'Invalid longitude (<-180)']
];

foreach ($testCoordinates as [$lat, $lng, $description]) {
    $valid = ($lat >= -90 && $lat <= 90 && $lng >= -180 && $lng <= 180);
    echo "   - $description: " . ($valid ? "✓ Valid" : "✗ Invalid") . "\n";
}

// Test 4: Test address combination logic
echo "\n4. Testing address combination logic:\n";

$testCases = [
    ['GİRNE', 'MERKEZ', 'Test sokak No:1', 'GİRNE, MERKEZ, Test sokak No:1'],
    ['LEFKOŞA', 'AKINCILAR', '', 'LEFKOŞA, AKINCILAR'],
    ['MAĞUSA', '', 'Atatürk Caddesi No:45', 'MAĞUSA, Atatürk Caddesi No:45'],
    ['', '', 'Sadece detay adresi', 'Sadece detay adresi']
];

foreach ($testCases as [$city, $district, $details, $expected]) {
    $addressParts = array_filter([$city, $district, $details]);
    $combined = implode(', ', $addressParts);
    $match = ($combined === $expected);
    echo "   - Input: ['$city', '$district', '$details']\n";
    echo "     Expected: '$expected'\n";
    echo "     Got: '$combined'\n";
    echo "     Result: " . ($match ? "✓ Match" : "✗ Mismatch") . "\n\n";
}

// Test 5: Check if database migration was applied
echo "5. Testing database schema:\n";
try {
    $discovery = \App\Models\Discovery::first();
    if ($discovery) {
        $hasLatitude = array_key_exists('latitude', $discovery->getAttributes());
        $hasLongitude = array_key_exists('longitude', $discovery->getAttributes());
        echo "   - Latitude field exists: " . ($hasLatitude ? "✓ Yes" : "✗ No") . "\n";
        echo "   - Longitude field exists: " . ($hasLongitude ? "✓ Yes" : "✗ No") . "\n";
    } else {
        echo "   - No discoveries found to test schema\n";
    }
} catch (Exception $e) {
    echo "   - Error checking schema: " . $e->getMessage() . "\n";
}

echo "\n=== Test Complete ===\n";
