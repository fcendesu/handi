<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== API Testing for Payment Methods ===" . PHP_EOL;

// Get a test user
$user = \App\Models\User::first();
if (!$user) {
    echo "No users found in database!" . PHP_EOL;
    exit(1);
}

// Manually authenticate the user for testing
\Illuminate\Support\Facades\Auth::login($user);

echo "Authenticated as: " . $user->email . PHP_EOL;

// Test the API endpoint logic
$controller = new \App\Http\Controllers\PaymentMethodController();

// Create a mock request
$request = new \Illuminate\Http\Request();

try {
    $response = $controller->getAccessiblePaymentMethods($request);
    $paymentMethods = json_decode($response->getContent(), true);
    
    echo PHP_EOL . "API Response:" . PHP_EOL;
    echo "Status: " . $response->getStatusCode() . PHP_EOL;
    echo "Payment Methods Count: " . count($paymentMethods) . PHP_EOL;
    
    foreach ($paymentMethods as $pm) {
        echo "  - ID: {$pm['id']}, Name: {$pm['name']}, Description: {$pm['description']}" . PHP_EOL;
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
}

echo PHP_EOL . "=== API Test completed ===" . PHP_EOL;
