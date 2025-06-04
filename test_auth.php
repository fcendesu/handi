<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Create a simple request
$request = Illuminate\Http\Request::create('/test-auth', 'GET');

$response = $kernel->handle($request);

// Bootstrap Laravel
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use App\Models\Property;

echo "=== Testing Authentication and Data ===\n";

// Check if we have users
$userCount = User::count();
echo "Total users: $userCount\n";

if ($userCount > 0) {
    $user = User::first();
    echo "First user: {$user->name} ({$user->email}) - Type: {$user->user_type}\n";
}

// Check if we have properties
$propertyCount = Property::count();
echo "Total properties: $propertyCount\n";

if ($propertyCount > 0) {
    $property = Property::first();
    echo "First property: {$property->name} (ID: {$property->id})\n";

    // Test the edit route data
    echo "\n=== Testing Property Edit Data ===\n";

    try {
        $cities = \App\Models\Property::$cities;
        echo "Cities available: " . count($cities) . "\n";

        $districts = \App\Models\Property::getDistrictsByCity($property->city);
        echo "Districts for {$property->city}: " . count($districts) . "\n";

        echo "Property data looks good!\n";

    } catch (Exception $e) {
        echo "Error getting property data: " . $e->getMessage() . "\n";
    }
}

echo "\n=== Test Complete ===\n";
