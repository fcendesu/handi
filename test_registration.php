<?php

// Test script to verify registration functionality
require_once __DIR__ . '/vendor/autoload.php';

use App\Models\User;
use App\Models\Company;
use Illuminate\Support\Facades\Hash;

// Create Laravel application instance
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Testing Registration System\n";
echo "==========================\n\n";

// Test 1: Solo Handyman without company
echo "Test 1: Solo Handyman (no company)\n";
$userData = [
    'name' => 'John Test',
    'email' => 'john.test@example.com',
    'password' => Hash::make('password123'),
    'user_type' => 'solo_handyman',
    'company_id' => null,
];

try {
    // Check if user already exists
    $existingUser = User::where('email', $userData['email'])->first();
    if ($existingUser) {
        $existingUser->delete();
    }

    $user = User::create($userData);
    echo "✓ Solo handyman created successfully\n";
    echo "  - Name: {$user->name}\n";
    echo "  - Email: {$user->email}\n";
    echo "  - Type: {$user->user_type}\n";
    echo "  - Company ID: " . ($user->company_id ?? 'None') . "\n\n";
} catch (Exception $e) {
    echo "✗ Error creating solo handyman: " . $e->getMessage() . "\n\n";
}

// Test 2: Company Admin with company
echo "Test 2: Company Admin with company\n";
try {
    // Clean up any existing data
    $existingUser = User::where('email', 'admin.test@example.com')->first();
    if ($existingUser && $existingUser->company) {
        $existingUser->company->delete();
    }
    if ($existingUser) {
        $existingUser->delete();
    }

    // Create company first
    $company = Company::create([
        'name' => 'Test Company LLC',
        'address' => '123 Business St, Test City, TC 12345',
        'phone' => '555-0123',
        'email' => 'info@testcompany.com',
        'admin_id' => null, // Will be set after user creation
    ]);

    // Create admin user
    $adminUser = User::create([
        'name' => 'Admin Test',
        'email' => 'admin.test@example.com',
        'password' => Hash::make('password123'),
        'user_type' => 'company_admin',
        'company_id' => $company->id,
    ]);

    // Set admin relationship
    $company->update(['admin_id' => $adminUser->id]);

    echo "✓ Company admin created successfully\n";
    echo "  - Name: {$adminUser->name}\n";
    echo "  - Email: {$adminUser->email}\n";
    echo "  - Type: {$adminUser->user_type}\n";
    echo "  - Company: {$company->name}\n";
    echo "  - Company Admin ID: {$company->admin_id}\n\n";

} catch (Exception $e) {
    echo "✗ Error creating company admin: " . $e->getMessage() . "\n\n";
}

echo "Registration system tests completed!\n";
echo "You can now test the web interface at: http://127.0.0.1:8000/register\n";
