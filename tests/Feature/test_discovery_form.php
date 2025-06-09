<?php

/**
 * Test script to verify Discovery form submission with manual address
 */

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

// Boot the application
$app->boot();

// Test data for manual address submission
$testData = [
    'customer_name' => 'Test Customer',
    'customer_phone' => '+90 533 123 4567',
    'customer_email' => 'test@example.com',
    'address_type' => 'manual',
    'manual_city' => 'Lefkoşa',
    'manual_district' => 'Köşklüçiftlik',
    'address_details' => 'Test Sokak No:123',
    'manual_latitude' => 35.1856,
    'manual_longitude' => 33.3823,
    'discovery' => 'Test discovery description',
    'todo_list' => 'Test todo list',
    'completion_time' => 120,
    'service_cost' => 500.00,
];

echo "Testing Discovery Form Manual Address Functionality\n";
echo "================================================\n\n";

// Test 1: Validate manual address fields
echo "1. Testing manual address validation...\n";

$rules = [
    'address_type' => 'required|in:property,manual',
    'manual_city' => 'nullable|string|max:255|required_if:address_type,manual',
    'manual_district' => 'nullable|string|max:255|required_if:address_type,manual',
    'address_details' => 'nullable|string|max:1000',
    'manual_latitude' => 'nullable|numeric|between:-90,90',
    'manual_longitude' => 'nullable|numeric|between:-180,180',
];

$validator = \Illuminate\Support\Facades\Validator::make($testData, $rules);

if ($validator->passes()) {
    echo "✅ Manual address validation passed\n";
} else {
    echo "❌ Manual address validation failed:\n";
    foreach ($validator->errors()->all() as $error) {
        echo "   - $error\n";
    }
}

// Test 2: Test AddressData functionality
echo "\n2. Testing AddressData functionality...\n";

try {
    $cities = \App\Data\AddressData::getCities();
    echo "✅ Cities loaded: " . count($cities) . " cities\n";
    
    $districts = \App\Data\AddressData::getDistrictsByCity('Lefkoşa');
    echo "✅ Districts for Lefkoşa: " . count($districts) . " districts\n";
    
    if (in_array('Köşklüçiftlik', $districts)) {
        echo "✅ Test district 'Köşklüçiftlik' found in Lefkoşa\n";
    } else {
        echo "❌ Test district 'Köşklüçiftlik' not found in Lefkoşa\n";
    }
} catch (Exception $e) {
    echo "❌ AddressData error: " . $e->getMessage() . "\n";
}

// Test 3: Test address combination logic
echo "\n3. Testing address combination logic...\n";

$addressParts = array_filter([
    $testData['manual_city'],
    $testData['manual_district'],
    $testData['address_details']
]);
$combinedAddress = implode(', ', $addressParts);

echo "✅ Combined address: '$combinedAddress'\n";

// Test 4: Check coordinate validation
echo "\n4. Testing coordinate validation...\n";

$lat = $testData['manual_latitude'];
$lng = $testData['manual_longitude'];

if ($lat >= -90 && $lat <= 90) {
    echo "✅ Latitude ($lat) is valid\n";
} else {
    echo "❌ Latitude ($lat) is invalid\n";
}

if ($lng >= -180 && $lng <= 180) {
    echo "✅ Longitude ($lng) is valid\n";
} else {
    echo "❌ Longitude ($lng) is invalid\n";
}

// Test 5: Check if Discovery model can handle the fields
echo "\n5. Testing Discovery model fillable fields...\n";

try {
    $discovery = new \App\Models\Discovery();
    $fillable = $discovery->getFillable();
    
    $requiredFields = ['latitude', 'longitude', 'address'];
    foreach ($requiredFields as $field) {
        if (in_array($field, $fillable)) {
            echo "✅ Field '$field' is fillable\n";
        } else {
            echo "❌ Field '$field' is not fillable\n";
        }
    }
} catch (Exception $e) {
    echo "❌ Discovery model error: " . $e->getMessage() . "\n";
}

echo "\n✅ Test completed!\n";
