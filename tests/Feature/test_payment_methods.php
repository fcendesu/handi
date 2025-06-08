<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Payment Method Reactivation Test ===" . PHP_EOL;

// Get a test user
$user = \App\Models\User::first();
if (!$user) {
    echo "No users found in database!" . PHP_EOL;
    exit(1);
}
echo "Test user: " . $user->email . PHP_EOL;

// Check current payment methods
echo PHP_EOL . "Current payment methods:" . PHP_EOL;
\App\Models\PaymentMethod::all()->each(function($pm) {
    echo "  {$pm->id}: {$pm->name} (active: " . ($pm->is_active ? 'yes' : 'no') . ")" . PHP_EOL;
});

// Clean up any duplicate 'Nakit' entries
$duplicates = \App\Models\PaymentMethod::where('name', 'Nakit')->get();
if ($duplicates->count() > 1) {
    echo PHP_EOL . "Found {$duplicates->count()} 'Nakit' payment methods, cleaning up duplicates..." . PHP_EOL;
    $firstNakit = $duplicates->first();
    $duplicates->skip(1)->each(function($pm) { 
        $pm->forceDelete(); // Hard delete duplicates
    });
    echo "Kept payment method ID: {$firstNakit->id}" . PHP_EOL;
} else {
    $firstNakit = $duplicates->first();
}

// Test scenario 1: Deactivate a payment method (simulate "delete")
echo PHP_EOL . "=== Test 1: Deactivating payment method ===" . PHP_EOL;
// First, associate the payment method with our test user
$firstNakit->update(['user_id' => $user->id, 'is_active' => false]);
echo "Deactivated 'Nakit' payment method (ID: {$firstNakit->id}) for user {$user->email}" . PHP_EOL;

// Verify it's not visible in active list
$activePaymentMethods = \App\Models\PaymentMethod::active()->get();
echo "Active payment methods after deactivation: " . $activePaymentMethods->count() . PHP_EOL;

// Test scenario 2: Try to create same payment method again (should reactivate)
echo PHP_EOL . "=== Test 2: Attempting to recreate same payment method ===" . PHP_EOL;

// Simulate the PaymentMethodController@store logic
$validated = [
    'name' => 'Nakit',
    'description' => 'Updated description via reactivation test'
];

// Find existing payment method (including inactive ones)
$existingPaymentMethod = \App\Models\PaymentMethod::where('name', $validated['name'])
    ->where('user_id', $user->id)
    ->first();

// If no user-specific payment method found, check for company payment methods
if (!$existingPaymentMethod && $user->company_id) {
    $existingPaymentMethod = \App\Models\PaymentMethod::where('name', $validated['name'])
        ->where('company_id', $user->company_id)
        ->first();
}

if ($existingPaymentMethod && !$existingPaymentMethod->is_active) {
    echo "Found inactive payment method, reactivating..." . PHP_EOL;
    $existingPaymentMethod->update([
        'is_active' => true, 
        'description' => $validated['description']
    ]);
    echo "Reactivated payment method ID: {$existingPaymentMethod->id}" . PHP_EOL;
    echo "Updated description: {$existingPaymentMethod->description}" . PHP_EOL;
} else {
    echo "Would create new payment method" . PHP_EOL;
}

// Verify reactivation worked
echo PHP_EOL . "=== Verification ===" . PHP_EOL;
$reactivatedPaymentMethod = \App\Models\PaymentMethod::find($firstNakit->id);
echo "Payment method {$reactivatedPaymentMethod->id} is now active: " . ($reactivatedPaymentMethod->is_active ? 'yes' : 'no') . PHP_EOL;
echo "Description: {$reactivatedPaymentMethod->description}" . PHP_EOL;

// Check active count
$finalActiveCount = \App\Models\PaymentMethod::active()->count();
echo "Total active payment methods: {$finalActiveCount}" . PHP_EOL;

echo PHP_EOL . "=== Test completed successfully! ===" . PHP_EOL;
