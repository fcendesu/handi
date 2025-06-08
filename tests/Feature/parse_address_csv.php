<?php

/**
 * Parse Address.csv to extract hierarchical address structure
 * Level 1: Cities
 * Level 2: Districts/Areas  
 * Level 3: Neighborhoods
 */

$csvFile = 'Address.csv';
$cities = [];
$districts = [];
$neighborhoods = [];

if (($handle = fopen($csvFile, 'r')) !== FALSE) {
    // Skip header row
    fgetcsv($handle);

    while (($data = fgetcsv($handle)) !== FALSE) {
        $name = trim($data[0]);
        $level = (int) trim($data[1]);
        $fullName = trim($data[2]);

        if ($level === 1) {
            // Level 1: Cities
            if (!in_array($name, $cities)) {
                $cities[] = $name;
            }
        } elseif ($level === 2) {
            // Level 2: Districts/Areas
            $parts = explode(' / ', $fullName);
            if (count($parts) >= 2) {
                $cityName = trim($parts[0]);
                $districtName = trim($parts[1]);

                if (!isset($districts[$cityName])) {
                    $districts[$cityName] = [];
                }
                if (!in_array($districtName, $districts[$cityName])) {
                    $districts[$cityName][] = $districtName;
                }
            }
        } elseif ($level === 3) {
            // Level 3: Neighborhoods
            $parts = explode(' / ', $fullName);
            if (count($parts) >= 3) {
                $cityName = trim($parts[0]);
                $districtName = trim($parts[1]);
                $neighborhoodName = trim($parts[2]);

                $key = $cityName . ' / ' . $districtName;
                if (!isset($neighborhoods[$key])) {
                    $neighborhoods[$key] = [];
                }
                if (!in_array($neighborhoodName, $neighborhoods[$key])) {
                    $neighborhoods[$key][] = $neighborhoodName;
                }
            }
        }
    }
    fclose($handle);
}

// Sort arrays
sort($cities);
foreach ($districts as $city => $districtList) {
    sort($districts[$city]);
}
foreach ($neighborhoods as $key => $neighborhoodList) {
    sort($neighborhoods[$key]);
}

echo "=== CITIES (Level 1) ===\n";
echo "Total cities: " . count($cities) . "\n";
foreach ($cities as $city) {
    echo "- $city\n";
}

echo "\n=== DISTRICTS BY CITY (Level 2) ===\n";
foreach ($districts as $city => $districtList) {
    echo "$city (" . count($districtList) . " districts):\n";
    foreach ($districtList as $district) {
        echo "  - $district\n";
    }
    echo "\n";
}

echo "\n=== NEIGHBORHOODS BY DISTRICT (Level 3) ===\n";
foreach ($neighborhoods as $key => $neighborhoodList) {
    echo "$key (" . count($neighborhoodList) . " neighborhoods):\n";
    foreach ($neighborhoodList as $neighborhood) {
        echo "  - $neighborhood\n";
    }
    echo "\n";
}

// Generate PHP array for Property model
echo "\n=== PHP ARRAYS FOR PROPERTY MODEL ===\n";

echo "\n// Cities array:\n";
echo "public static array \$cities = [\n";
foreach ($cities as $city) {
    echo "    '$city',\n";
}
echo "];\n";

echo "\n// Districts by city array:\n";
echo "public static array \$districts = [\n";
foreach ($districts as $city => $districtList) {
    echo "    '$city' => [\n";
    foreach ($districtList as $district) {
        echo "        '$district',\n";
    }
    echo "    ],\n";
}
echo "];\n";

echo "\n// Neighborhoods by district array (if needed for level 3):\n";
echo "public static array \$neighborhoods = [\n";
foreach ($neighborhoods as $key => $neighborhoodList) {
    echo "    '$key' => [\n";
    foreach ($neighborhoodList as $neighborhood) {
        echo "        '$neighborhood',\n";
    }
    echo "    ],\n";
}
echo "];\n";

echo "\nParsing complete!\n";
echo "Use the districts array for the two-level address system (City -> District).\n";
