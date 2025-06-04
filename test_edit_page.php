<?php

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Property;

echo "=== Testing Property Edit Page ===\n\n";

try {
    // Find a test user
    $user = User::where('email', 'test@test.com')->first();
    if (!$user) {
        echo "❌ Test user not found. Please create a user with email test@test.com\n";
        exit(1);
    }

    echo "✓ Found test user: {$user->name} ({$user->user_type})\n";

    // Find a property that belongs to this user
    $property = null;
    if ($user->isSoloHandyman()) {
        $property = Property::where('user_id', $user->id)->first();
    } else {
        $property = Property::where('company_id', $user->company_id)->first();
    }

    if (!$property) {
        echo "❌ No property found for this user\n";
        echo "Creating a test property...\n";

        $propertyData = [
            'name' => 'Test Property for Edit',
            'city' => 'LEFKOŞA',
            'neighborhood' => 'MERKEZ',
            'street' => 'Test Street',
            'door_apartment_no' => '1A',
            'latitude' => 35.1856,
            'longitude' => 33.3823,
            'notes' => 'This is a test property for edit page testing.',
            'is_active' => true,
        ];

        if ($user->isSoloHandyman()) {
            $propertyData['user_id'] = $user->id;
            $propertyData['company_id'] = null;
        } else {
            $propertyData['company_id'] = $user->company_id;
            $propertyData['user_id'] = null;
        }

        $property = Property::create($propertyData);
        echo "✓ Created test property: {$property->name} (ID: {$property->id})\n";
    } else {
        echo "✓ Found property: {$property->name} (ID: {$property->id})\n";
    }

    // Test controller method directly
    auth()->login($user);

    $controller = new App\Http\Controllers\PropertyController();

    echo "\n=== Testing edit method ===\n";

    try {
        $response = $controller->edit($property);
        echo "✓ Controller edit method executed successfully\n";
        echo "✓ Response type: " . get_class($response) . "\n";

        if ($response instanceof Illuminate\View\View) {
            echo "✓ View name: " . $response->name() . "\n";
            echo "✓ View data keys: " . implode(', ', array_keys($response->getData())) . "\n";

            // Check if the required data is present
            $data = $response->getData();
            if (isset($data['property'])) {
                echo "✓ Property data is present\n";
            } else {
                echo "❌ Property data is missing\n";
            }

            if (isset($data['cities'])) {
                echo "✓ Cities data is present (count: " . count($data['cities']) . ")\n";
            } else {
                echo "❌ Cities data is missing\n";
            }

            if (isset($data['districts'])) {
                echo "✓ Districts data is present (count: " . count($data['districts']) . ")\n";
            } else {
                echo "❌ Districts data is missing\n";
            }
        }

    } catch (Exception $e) {
        echo "❌ Controller error: " . $e->getMessage() . "\n";
        echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    }

    echo "\n=== Test completed ===\n";
    echo "You can now test the edit page at: http://127.0.0.1:8000/properties/{$property->id}/edit\n";
    echo "Login with: {$user->email} / password\n";

} catch (Exception $e) {
    echo "❌ Fatal error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
