<?php

require __DIR__ . '/vendor/autoload.php';

use App\Models\User;
use App\Models\PaymentMethod;
use App\Http\Controllers\PaymentMethodController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Get user with ID 9 (Furkan)
$user = User::find(9);
if (!$user) {
    echo "User with ID 9 not found.\n";
    exit(1);
}

// Authenticate as this user
Auth::login($user);

echo "Testing payment method reactivation fix...\n";
echo "Authenticated as: " . $user->name . " (ID: " . $user->id . ")\n\n";

// Check current state of "Nakit" payment method for this user
$existingPaymentMethod = PaymentMethod::where('name', 'Nakit')
    ->where('user_id', $user->id)
    ->first();

if ($existingPaymentMethod) {
    echo "Existing 'Nakit' payment method found:\n";
    echo "  ID: " . $existingPaymentMethod->id . "\n";
    echo "  Name: " . $existingPaymentMethod->name . "\n";
    echo "  Active: " . ($existingPaymentMethod->is_active ? 'Yes' : 'No') . "\n";
    echo "  User ID: " . $existingPaymentMethod->user_id . "\n\n";
    
    if (!$existingPaymentMethod->is_active) {
        echo "Payment method is inactive. Testing reactivation...\n";
        
        // Create a mock request
        $request = new Request([
            'name' => 'Nakit',
            'description' => 'Test reactivation'
        ]);
        
        try {
            // Test the controller store method
            $controller = new PaymentMethodController();
            $response = $controller->store($request);
            
            // Check if the payment method was reactivated
            $reactivatedPaymentMethod = PaymentMethod::find($existingPaymentMethod->id);
            if ($reactivatedPaymentMethod && $reactivatedPaymentMethod->is_active) {
                echo "SUCCESS: Payment method was reactivated!\n";
                echo "  ID: " . $reactivatedPaymentMethod->id . "\n";
                echo "  Name: " . $reactivatedPaymentMethod->name . "\n";
                echo "  Active: " . ($reactivatedPaymentMethod->is_active ? 'Yes' : 'No') . "\n";
                echo "  Description: " . $reactivatedPaymentMethod->description . "\n";
            } else {
                echo "FAILURE: Payment method was not reactivated.\n";
            }
        } catch (Exception $e) {
            echo "ERROR: " . $e->getMessage() . "\n";
            echo "Fix did not work properly.\n";
        }
    } else {
        echo "Payment method is already active. Cannot test reactivation.\n";
    }
} else {
    echo "No existing 'Nakit' payment method found for this user.\n";
}
