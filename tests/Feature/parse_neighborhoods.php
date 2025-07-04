<?php

// Script to parse neighborhoods from CSV and create the data structure

$file = fopen('Adresler.csv', 'r');
$header = fgetcsv($file); // Skip header

$neighborhoods = [];

while (($data = fgetcsv($file)) !== false) {
    if (count($data) >= 3 && trim($data[1]) === '3') {
        $fullPath = trim($data[2]);
        $parts = array_map('trim', explode(' / ', $fullPath));
        
        if (count($parts) === 3) {
            $city = $parts[0];
            $district = $parts[1];
            $neighborhood = $parts[2];
            
            if (!isset($neighborhoods[$city])) {
                $neighborhoods[$city] = [];
            }
            if (!isset($neighborhoods[$city][$district])) {
                $neighborhoods[$city][$district] = [];
            }
            
            $neighborhoods[$city][$district][] = $neighborhood;
        }
    }
}

fclose($file);

// Sort everything
foreach ($neighborhoods as $city => &$districts) {
    foreach ($districts as $district => &$hoods) {
        sort($hoods);
    }
    ksort($districts);
}
ksort($neighborhoods);

// Output PHP array format
echo "<?php\n\n";
echo "// Neighborhoods data structure\n";
echo "public static array \$neighborhoods = [\n";

foreach ($neighborhoods as $city => $districts) {
    echo "    '{$city}' => [\n";
    foreach ($districts as $district => $hoods) {
        echo "        '{$district}' => [\n";
        foreach ($hoods as $hood) {
            echo "            '{$hood}',\n";
        }
        echo "        ],\n";
    }
    echo "    ],\n";
}

echo "];\n";

echo "\n// Summary:\n";
foreach ($neighborhoods as $city => $districts) {
    $totalHoods = array_sum(array_map('count', $districts));
    echo "// {$city}: " . count($districts) . " districts, {$totalHoods} neighborhoods\n";
}
