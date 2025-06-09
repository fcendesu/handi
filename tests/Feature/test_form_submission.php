<?php

// Test discovery form submission
$url = 'http://localhost:8000/discovery';
$postUrl = 'http://localhost:8000/discovery';

// Get CSRF token first
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, '/tmp/cookie.txt');
curl_setopt($ch, CURLOPT_COOKIEFILE, '/tmp/cookie.txt');
$response = curl_exec($ch);

// Extract CSRF token
preg_match('/name="csrf-token" content="([^"]+)"/', $response, $matches);
$csrfToken = $matches[1] ?? '';

if (!$csrfToken) {
    echo "Error: Could not extract CSRF token\n";
    exit(1);
}

echo "CSRF token extracted: " . substr($csrfToken, 0, 10) . "...\n";

// Test data for manual address
$postData = [
    '_token' => $csrfToken,
    'customer_name' => 'Test Customer',
    'customer_phone' => '+90 555 123 4567',
    'customer_email' => 'customer@test.com',
    'address_type' => 'manual',
    'manual_city' => 'GİRNE',
    'manual_district' => 'MERKEZ',
    'address_details' => 'Test sokak No:123 Daire:5',
    'manual_latitude' => '35.3417',
    'manual_longitude' => '33.3142',
    'discovery' => 'Test keşif raporu detayları',
    'completion_time' => '7',
    'service_cost' => '100.50'
];

echo "Testing manual address form submission...\n";
echo "Data to submit:\n";
foreach ($postData as $key => $value) {
    if ($key !== '_token') {
        echo "  $key: $value\n";
    }
}

// Submit form
curl_setopt($ch, CURLOPT_URL, $postUrl);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
curl_setopt($ch, CURLOPT_HEADER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

echo "\nResponse HTTP Code: $httpCode\n";

if ($httpCode == 302) {
    // Get redirect location
    preg_match('/Location: ([^\r\n]+)/', $response, $matches);
    $redirectLocation = $matches[1] ?? '';
    echo "Redirected to: $redirectLocation\n";
    
    if (strpos($redirectLocation, 'discovery') !== false) {
        echo "✓ Form submission successful - redirected back to discovery page\n";
    } else {
        echo "✗ Unexpected redirect location\n";
    }
} else {
    // Check for validation errors
    if (strpos($response, 'validation') !== false || strpos($response, 'error') !== false) {
        echo "✗ Validation errors found in response\n";
        
        // Try to extract error messages
        preg_match_all('/<p class="mt-1 text-sm text-red-600">([^<]+)<\/p>/', $response, $errorMatches);
        if (!empty($errorMatches[1])) {
            echo "Error messages:\n";
            foreach ($errorMatches[1] as $error) {
                echo "  - $error\n";
            }
        }
    } else {
        echo "✓ Form displayed successfully\n";
    }
}

curl_close($ch);

echo "\nTest completed.\n";
