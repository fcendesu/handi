<?php

/**
 * Test the Discovery form manual address functionality via HTTP
 */

// Configuration
$baseUrl = 'http://localhost:8001';
$testUser = [
    'email' => 'admin@test.com',
    'password' => 'password'
];

// Function to make HTTP requests with session
function makeRequest($url, $method = 'GET', $data = null, $headers = []) {
    $ch = curl_init();
    
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_COOKIEJAR => '/tmp/cookies.txt',
        CURLOPT_COOKIEFILE => '/tmp/cookies.txt',
        CURLOPT_USERAGENT => 'Discovery Test Client',
        CURLOPT_HTTPHEADER => array_merge([
            'Accept: text/html,application/json',
        ], $headers),
    ]);
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return [
        'body' => $response,
        'status' => $httpCode
    ];
}

echo "Testing Discovery Form Manual Address Functionality\n";
echo "================================================\n\n";

// Test 1: Get login page and extract CSRF token
echo "1. Getting login page...\n";
$loginPage = makeRequest("$baseUrl/login");

if ($loginPage['status'] !== 200) {
    echo "❌ Failed to load login page (status: {$loginPage['status']})\n";
    exit(1);
}

// Extract CSRF token
preg_match('/<input[^>]*name="_token"[^>]*value="([^"]*)"/', $loginPage['body'], $matches);
$csrfToken = $matches[1] ?? null;

if (!$csrfToken) {
    echo "❌ Could not extract CSRF token from login page\n";
    exit(1);
}

echo "✅ Login page loaded, CSRF token extracted\n";

// Test 2: Login
echo "\n2. Logging in...\n";
$loginData = http_build_query([
    '_token' => $csrfToken,
    'email' => $testUser['email'],
    'password' => $testUser['password'],
]);

$loginResponse = makeRequest("$baseUrl/login", 'POST', $loginData, [
    'Content-Type: application/x-www-form-urlencoded'
]);

if ($loginResponse['status'] === 302 || strpos($loginResponse['body'], 'dashboard') !== false) {
    echo "✅ Login successful\n";
} else {
    echo "❌ Login failed (status: {$loginResponse['status']})\n";
    echo "Response: " . substr($loginResponse['body'], 0, 500) . "...\n";
    exit(1);
}

// Test 3: Access discovery page
echo "\n3. Loading discovery page...\n";
$discoveryPage = makeRequest("$baseUrl/discovery");

if ($discoveryPage['status'] !== 200) {
    echo "❌ Failed to load discovery page (status: {$discoveryPage['status']})\n";
    exit(1);
}

echo "✅ Discovery page loaded\n";

// Test 4: Check for manual address components
echo "\n4. Checking for manual address functionality...\n";

$checks = [
    'Leaflet CSS' => 'leaflet.css',
    'Leaflet JS' => 'leaflet.js',
    'Manual Address Selector' => 'manualAddressSelector',
    'City Dropdown' => 'manual_city',
    'District Dropdown' => 'manual_district',
    'Address Details' => 'address_details',
    'Latitude Input' => 'manual_latitude',
    'Longitude Input' => 'manual_longitude',
    'Map Container' => 'manualAddressMap'
];

foreach ($checks as $description => $needle) {
    if (strpos($discoveryPage['body'], $needle) !== false) {
        echo "✅ $description found\n";
    } else {
        echo "❌ $description not found\n";
    }
}

// Test 5: Extract form CSRF token for submission test
echo "\n5. Preparing form submission test...\n";
preg_match('/<input[^>]*name="_token"[^>]*value="([^"]*)"/', $discoveryPage['body'], $formMatches);
$formCsrfToken = $formMatches[1] ?? null;

if ($formCsrfToken) {
    echo "✅ Form CSRF token extracted\n";
    
    // Test 6: Submit discovery form with manual address
    echo "\n6. Testing form submission with manual address...\n";
    
    $formData = http_build_query([
        '_token' => $formCsrfToken,
        'customer_name' => 'Test Customer',
        'customer_phone' => '+90 533 123 4567',
        'customer_email' => 'test@example.com',
        'address_type' => 'manual',
        'manual_city' => 'Lefkoşa',
        'manual_district' => 'Köşklüçiftlik',
        'address_details' => 'Test Sokak No:123',
        'manual_latitude' => '35.1856',
        'manual_longitude' => '33.3823',
        'discovery' => 'Test discovery for manual address testing',
        'todo_list' => 'Test todo items',
        'completion_time' => '120',
        'service_cost' => '500.00',
    ]);
    
    $submitResponse = makeRequest("$baseUrl/discovery", 'POST', $formData, [
        'Content-Type: application/x-www-form-urlencoded'
    ]);
    
    if ($submitResponse['status'] === 302) {
        echo "✅ Form submission successful (redirected)\n";
    } elseif ($submitResponse['status'] === 200) {
        // Check for validation errors
        if (strpos($submitResponse['body'], 'error') !== false || strpos($submitResponse['body'], 'validation') !== false) {
            echo "❌ Form submission had validation errors\n";
            // Extract and show some error details
            preg_match_all('/<[^>]*class="[^"]*error[^"]*"[^>]*>([^<]*)</', $submitResponse['body'], $errorMatches);
            if (!empty($errorMatches[1])) {
                echo "Errors found:\n";
                foreach (array_slice($errorMatches[1], 0, 3) as $error) {
                    echo "   - " . trim($error) . "\n";
                }
            }
        } else {
            echo "✅ Form submission processed (status: 200)\n";
        }
    } else {
        echo "❌ Form submission failed (status: {$submitResponse['status']})\n";
    }
} else {
    echo "❌ Could not extract form CSRF token\n";
}

echo "\n✅ Testing completed!\n";
