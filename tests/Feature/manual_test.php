<?php

// Manual test to verify registration endpoints work
echo "Testing Registration System Endpoints\n";
echo "====================================\n\n";

// Test 1: Check registration page loads
echo "Test 1: Registration page accessibility\n";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://127.0.0.1:8000/register');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if ($httpCode === 200 && strpos($response, 'Join Handi') !== false) {
    echo "✓ Registration page loads successfully\n";
    echo "✓ Contains expected content ('Join Handi')\n";
} else {
    echo "✗ Registration page failed to load properly (HTTP: $httpCode)\n";
}

curl_close($ch);
echo "\n";

// Test 2: Check if form has expected elements
echo "Test 2: Registration form elements\n";
if (strpos($response, 'Solo Handyman') !== false) {
    echo "✓ Solo Handyman option present\n";
} else {
    echo "✗ Solo Handyman option missing\n";
}

if (strpos($response, 'Company Owner') !== false) {
    echo "✓ Company Owner option present\n";
} else {
    echo "✗ Company Owner option missing\n";
}

if (strpos($response, 'name="user_type"') !== false) {
    echo "✓ User type selection field present\n";
} else {
    echo "✗ User type selection field missing\n";
}

if (strpos($response, 'create_company') !== false) {
    echo "✓ Optional company creation checkbox present\n";
} else {
    echo "✗ Optional company creation checkbox missing\n";
}

echo "\n";

// Test 3: Check JavaScript functionality
echo "Test 3: Frontend JavaScript\n";
if (strpos($response, 'toggleSections') !== false) {
    echo "✓ Dynamic form toggling JavaScript present\n";
} else {
    echo "✗ Dynamic form toggling JavaScript missing\n";
}

if (strpos($response, 'user-type-card') !== false) {
    echo "✓ Interactive user type cards present\n";
} else {
    echo "✗ Interactive user type cards missing\n";
}

echo "\n";
echo "Manual testing complete!\n";
echo "Visit http://127.0.0.1:8000/register to test the interface manually.\n";
