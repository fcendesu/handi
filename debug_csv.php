<?php

echo "Starting CSV parsing...\n";

$csvFile = 'Address.csv';

echo "Checking if file exists: " . ($csvFile) . "\n";
if (!file_exists($csvFile)) {
    echo "File does not exist!\n";
    exit(1);
}

echo "File exists. Size: " . filesize($csvFile) . " bytes\n";

// Read first few lines to debug
$lines = file($csvFile, FILE_IGNORE_NEW_LINES);
echo "Total lines: " . count($lines) . "\n";
echo "First 5 lines:\n";
for ($i = 0; $i < min(5, count($lines)); $i++) {
    echo "Line $i: " . $lines[$i] . "\n";
}

?>