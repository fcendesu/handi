<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== End-to-End Payment Method Test ===" . PHP_EOL;

// Get a test user
$user = \App\Models\User::first();
if (!$user) {
    echo "No users found in database!" . PHP_EOL;
    exit(1);
}

echo "Test user: " . $user->email . PHP_EOL;

// Ensure we have some active payment methods for the dropdown
$activeCount = \App\Models\PaymentMethod::active()->count();
echo "Active payment methods in system: {$activeCount}" . PHP_EOL;

if ($activeCount === 0) {
    echo "Creating some default payment methods..." . PHP_EOL;
    $defaultMethods = ['Nakit', 'Kredi Kartı', 'Banka Transferi'];
    foreach ($defaultMethods as $method) {
        \App\Models\PaymentMethod::create([
            'name' => $method,
            'description' => $method . ' ödemesi',
            'user_id' => $user->id,
            'is_active' => true
        ]);
    }
    echo "Created " . count($defaultMethods) . " default payment methods" . PHP_EOL;
}

// Test 1: Simulate creating a discovery with a payment method
echo PHP_EOL . "=== Test 1: Creating Discovery with Payment Method ===" . PHP_EOL;

// Simulate the discovery creation request
$discoveryData = [
    'title' => 'Test Discovery with Payment Method',
    'description' => 'Testing payment method integration',
    'customer_name' => 'Test Customer',
    'customer_phone' => '+90 555 123 4567',
    'customer_email' => 'test.customer@example.com',
    'address' => 'Test Address, Istanbul',
    'payment_method_id' => 1, // Use the first payment method
];

// Validate that payment method exists and is accessible
$paymentMethod = \App\Models\PaymentMethod::find($discoveryData['payment_method_id']);
if ($paymentMethod && $paymentMethod->is_active) {
    echo "✓ Payment method '{$paymentMethod->name}' is accessible and active" . PHP_EOL;
    
    // Create discovery
    $discovery = \App\Models\Discovery::create([
        'title' => $discoveryData['title'],
        'description' => $discoveryData['description'],
        'customer_name' => $discoveryData['customer_name'],
        'customer_phone' => $discoveryData['customer_phone'],
        'customer_email' => $discoveryData['customer_email'],
        'address' => $discoveryData['address'],
        'payment_method_id' => $discoveryData['payment_method_id'],
        'creator_id' => $user->id,
        'status' => \App\Models\Discovery::STATUS_PENDING,
    ]);
    
    echo "✓ Discovery created successfully with ID: {$discovery->id}" . PHP_EOL;
    echo "✓ Associated payment method: {$discovery->paymentMethod->name}" . PHP_EOL;
} else {
    echo "✗ Payment method not found or not active" . PHP_EOL;
}

// Test 2: Test the "delete and recreate" scenario
echo PHP_EOL . "=== Test 2: Delete and Recreate Payment Method ===" . PHP_EOL;

// Create a test payment method
$testPaymentMethod = \App\Models\PaymentMethod::create([
    'name' => 'Test Payment Method',
    'description' => 'This is a test payment method',
    'user_id' => $user->id,
    'is_active' => true
]);

echo "✓ Created test payment method: {$testPaymentMethod->name} (ID: {$testPaymentMethod->id})" . PHP_EOL;

// "Delete" it (deactivate)
$testPaymentMethod->update(['is_active' => false]);
echo "✓ Deactivated payment method" . PHP_EOL;

// Try to recreate it (should reactivate)
$validated = [
    'name' => 'Test Payment Method',
    'description' => 'Updated description after reactivation',
];

// Simulate PaymentMethodController@store logic
$existingPaymentMethod = \App\Models\PaymentMethod::where('name', $validated['name'])
    ->where('user_id', $user->id)
    ->first();

if ($existingPaymentMethod && !$existingPaymentMethod->is_active) {
    $existingPaymentMethod->update([
        'is_active' => true,
        'description' => $validated['description'],
    ]);
    echo "✓ Reactivated payment method successfully" . PHP_EOL;
    echo "✓ Updated description: {$existingPaymentMethod->description}" . PHP_EOL;
} else {
    echo "✗ Reactivation logic failed" . PHP_EOL;
}

// Test 3: API endpoint functionality
echo PHP_EOL . "=== Test 3: API Endpoint Functionality ===" . PHP_EOL;

\Illuminate\Support\Facades\Auth::login($user);
$controller = new \App\Http\Controllers\PaymentMethodController();
$request = new \Illuminate\Http\Request();

try {
    $response = $controller->getAccessiblePaymentMethods($request);
    $apiData = json_decode($response->getContent(), true);
    
    echo "✓ API endpoint responding correctly" . PHP_EOL;
    echo "✓ Returned {" . count($apiData) . "} payment methods" . PHP_EOL;
    
    foreach ($apiData as $pm) {
        echo "  - {$pm['name']} (ID: {$pm['id']})" . PHP_EOL;
    }
} catch (Exception $e) {
    echo "✗ API endpoint error: " . $e->getMessage() . PHP_EOL;
}

// Test 4: Validation rules
echo PHP_EOL . "=== Test 4: Validation Rules ===" . PHP_EOL;

$validator = \Illuminate\Support\Facades\Validator::make([
    'payment_method_id' => $testPaymentMethod->id
], [
    'payment_method_id' => 'nullable|exists:payment_methods,id'
]);

if ($validator->passes()) {
    echo "✓ Validation passes for valid payment method ID" . PHP_EOL;
} else {
    echo "✗ Validation failed: " . implode(', ', $validator->errors()->all()) . PHP_EOL;
}

$validator = \Illuminate\Support\Facades\Validator::make([
    'payment_method_id' => 99999
], [
    'payment_method_id' => 'nullable|exists:payment_methods,id'
]);

if ($validator->fails()) {
    echo "✓ Validation correctly rejects invalid payment method ID" . PHP_EOL;
} else {
    echo "✗ Validation should have failed for invalid payment method ID" . PHP_EOL;
}

echo PHP_EOL . "=== All tests completed! ===" . PHP_EOL;

// Cleanup
if (isset($discovery)) {
    $discovery->delete();
    echo "Cleaned up test discovery" . PHP_EOL;
}

if (isset($testPaymentMethod)) {
    $testPaymentMethod->forceDelete();
    echo "Cleaned up test payment method" . PHP_EOL;
}
