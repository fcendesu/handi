<?php

require_once 'vendor/autoload.php';

// Boot Laravel
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Company;

echo "Testing Existing Admin Can Fetch Employees\n";
echo "==========================================\n\n";

try {
    // Find an existing company admin
    $admin = User::where('user_type', User::TYPE_COMPANY_ADMIN)->first();
    
    if (!$admin) {
        echo "❌ No company admin found in database\n";
        echo "Creating a test admin...\n";
        
        // Find a company or create one
        $company = Company::first();
        if (!$company) {
            echo "❌ No company found either\n";
            exit;
        }
        
        $admin = User::create([
            'name' => 'Test API Admin',
            'email' => 'api.test@example.com',
            'password' => bcrypt('password123'),
            'user_type' => User::TYPE_COMPANY_ADMIN,
            'company_id' => $company->id
        ]);
        echo "✅ Created test admin: {$admin->name}\n";
    } else {
        echo "✅ Found existing admin: {$admin->name} (ID: {$admin->id})\n";
    }

    // Test login endpoint first
    echo "\nStep 1: Testing Login Endpoint\n";
    echo "------------------------------\n";
    
    $loginData = [
        'email' => $admin->email,
        'password' => 'password123' // This might not work for existing users
    ];
    
    // Let's create a token directly instead
    echo "Creating token directly for admin...\n";
    $token = $admin->createToken('api-test-token');
    $plainTextToken = $token->plainTextToken;
    
    echo "✅ Token created: " . substr($plainTextToken, 0, 20) . "...\n";

    // Test the API endpoint
    echo "\nStep 2: Testing API Endpoint\n";
    echo "----------------------------\n";
    
    $headers = [
        'Authorization: Bearer ' . $plainTextToken,
        'Accept: application/json',
        'Content-Type: application/json'
    ];

    $url = 'http://localhost:8000/api/company/assignable-employees';
    echo "Testing URL: {$url}\n";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_VERBOSE, true);
    curl_setopt($ch, CURLOPT_STDERR, fopen('php://output', 'w'));

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    echo "\nResult:\n";
    echo "HTTP Code: {$httpCode}\n";
    echo "Response: {$response}\n";
    
    if ($error) {
        echo "cURL Error: {$error}\n";
    }

    // Clean up the token
    $token->accessToken->delete();
    echo "\n✅ Token cleaned up\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\nTest complete!\n";
