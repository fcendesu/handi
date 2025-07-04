<?php

require_once 'vendor/autoload.php';

use App\Models\User;
use Illuminate\Support\Facades\Auth;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Testing API Assignable Employees Endpoint\n";
echo "==========================================\n";

try {
    // Find a company admin
    $companyAdmin = User::where('user_type', 'company_admin')->first();
    
    if (!$companyAdmin) {
        echo "No company admin found.\n";
        exit;
    }
    
    // Simulate authentication
    Auth::login($companyAdmin);
    
    echo "Logged in as: {$companyAdmin->name} (Company Admin)\n";
    echo "Company: {$companyAdmin->company->name}\n";
    
    // Test the controller method
    $controller = new App\Http\Controllers\CompanyController();
    $response = $controller->getAssignableEmployees();
    
    $responseData = json_decode($response->getContent(), true);
    
    echo "\nAPI Response:\n";
    echo "Success: " . ($responseData['success'] ? 'true' : 'false') . "\n";
    
    if ($responseData['success'] && isset($responseData['data'])) {
        echo "Assignable Employees Count: " . count($responseData['data']) . "\n";
        foreach ($responseData['data'] as $employee) {
            echo "  - ID: {$employee['id']}, Name: {$employee['name']}, Email: {$employee['email']}\n";
        }
    } else {
        echo "No data or failed response\n";
    }
    
    echo "\nAPI endpoint test complete!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
