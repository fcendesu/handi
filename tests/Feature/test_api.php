<?php

require_once 'app/Data/AddressData.php';

use App\Data\AddressData;

echo "=== Testing Neighborhoods API ===\n\n";

// Test 1: Get neighborhoods for GİRNE / ALSANCAK
echo "1. Testing AddressData::getNeighborhoods('GİRNE', 'ALSANCAK'):\n";
$neighborhoods = AddressData::getNeighborhoods('GİRNE', 'ALSANCAK');
echo json_encode($neighborhoods, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";

// Test 2: Check if all necessary data exists
echo "2. Summary of neighborhood data:\n";
$allNeighborhoods = AddressData::getAllNeighborhoods();
$totalCities = count($allNeighborhoods);
$totalDistricts = 0;
$totalNeighborhoods = 0;

foreach ($allNeighborhoods as $city => $districts) {
    $cityDistricts = count($districts);
    $cityNeighborhoods = array_sum(array_map('count', $districts));
    $totalDistricts += $cityDistricts;
    $totalNeighborhoods += $cityNeighborhoods;
    
    echo "   {$city}: {$cityDistricts} districts, {$cityNeighborhoods} neighborhoods\n";
}

echo "\nTotals: {$totalCities} cities, {$totalDistricts} districts, {$totalNeighborhoods} neighborhoods\n\n";

// Test 3: Test specific cases
echo "3. Testing specific cases:\n";
$testCases = [
    ['GİRNE', 'ALSANCAK'],
    ['LEFKOŞA', 'MERKEZ'],
    ['MAĞUSA', 'AKDOĞAN'],
    ['İSKELE', 'BÜYÜKKONUK'],
    ['NONEXISTENT', 'DISTRICT']
];

foreach ($testCases as $case) {
    $city = $case[0];
    $district = $case[1];
    $neighborhoods = AddressData::getNeighborhoods($city, $district);
    $count = count($neighborhoods);
    echo "   {$city} / {$district}: {$count} neighborhoods\n";
    if ($count > 0 && $count <= 5) {
        echo "     - " . implode(', ', $neighborhoods) . "\n";
    } elseif ($count > 5) {
        echo "     - " . implode(', ', array_slice($neighborhoods, 0, 5)) . " (and " . ($count - 5) . " more)\n";
    }
}

echo "\n=== Test completed ===\n";
