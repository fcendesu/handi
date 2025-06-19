<?php

require_once 'app/Data/AddressData.php';

use App\Data\AddressData;

echo "=== Testing AddressData with Neighborhoods ===\n\n";

// Test 1: Get neighborhoods for GİRNE / ALSANCAK
echo "1. Neighborhoods in GİRNE / ALSANCAK:\n";
$neighborhoods = AddressData::getNeighborhoods('GİRNE', 'ALSANCAK');
foreach ($neighborhoods as $neighborhood) {
    echo "   - {$neighborhood}\n";
}
echo "   Total: " . count($neighborhoods) . " neighborhoods\n\n";

// Test 2: Get all neighborhoods for LEFKOŞA
echo "2. All districts and neighborhoods in LEFKOŞA:\n";
$lefkosaNeighborhoods = AddressData::getNeighborhoodsByCity('LEFKOŞA');
foreach ($lefkosaNeighborhoods as $district => $neighborhoods) {
    echo "   District: {$district} (" . count($neighborhoods) . " neighborhoods)\n";
    foreach ($neighborhoods as $neighborhood) {
        echo "     - {$neighborhood}\n";
    }
}
echo "\n";

// Test 3: Test neighborhood existence
echo "3. Testing neighborhood existence:\n";
$exists1 = AddressData::neighborhoodExists('GİRNE', 'ALSANCAK', 'ILGAZ');
$exists2 = AddressData::neighborhoodExists('GİRNE', 'ALSANCAK', 'NONEXISTENT');
echo "   ILGAZ exists in GİRNE/ALSANCAK: " . ($exists1 ? 'YES' : 'NO') . "\n";
echo "   NONEXISTENT exists in GİRNE/ALSANCAK: " . ($exists2 ? 'YES' : 'NO') . "\n\n";

// Test 4: Get neighborhood count
echo "4. Neighborhood counts:\n";
$count1 = AddressData::getNeighborhoodCount('GİRNE', 'ALSANCAK');
$count2 = AddressData::getNeighborhoodCount('LEFKOŞA', 'MERKEZ');
echo "   GİRNE/ALSANCAK: {$count1} neighborhoods\n";
echo "   LEFKOŞA/MERKEZ: {$count2} neighborhoods\n\n";

// Test 5: Summary statistics
echo "5. Summary by city:\n";
$totalCities = count(AddressData::getCities());
$totalDistricts = 0;
$totalNeighborhoods = 0;

foreach (AddressData::getAllNeighborhoods() as $city => $districts) {
    $cityDistricts = count($districts);
    $cityNeighborhoods = array_sum(array_map('count', $districts));
    $totalDistricts += $cityDistricts;
    $totalNeighborhoods += $cityNeighborhoods;
    
    echo "   {$city}: {$cityDistricts} districts, {$cityNeighborhoods} neighborhoods\n";
}

echo "\nOverall totals:\n";
echo "   Cities: {$totalCities}\n";
echo "   Districts: {$totalDistricts}\n";
echo "   Neighborhoods: {$totalNeighborhoods}\n";
