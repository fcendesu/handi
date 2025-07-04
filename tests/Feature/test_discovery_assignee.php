<?php

require_once 'vendor/autoload.php';

use App\Models\User;
use App\Models\Discovery;
use Illuminate\Support\Facades\Auth;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Testing Discovery Creation with Assignee\n";
echo "=========================================\n";

try {
    // Find a company admin and an employee
    $companyAdmin = User::where('user_type', 'company_admin')->first();
    $employee = User::where('user_type', 'company_employee')->first();
    
    if (!$companyAdmin || !$employee) {
        echo "Missing required users (admin or employee).\n";
        exit;
    }
    
    echo "Admin: {$companyAdmin->name}\n";
    echo "Employee: {$employee->name}\n";
    echo "Same Company: " . ($companyAdmin->company_id === $employee->company_id ? 'Yes' : 'No') . "\n";
    
    // Create a test discovery with assignee
    $discovery = Discovery::create([
        'creator_id' => $companyAdmin->id,
        'assignee_id' => $employee->id,
        'company_id' => $companyAdmin->company_id,
        'customer_name' => 'Test Customer',
        'customer_phone' => '555-0123',
        'customer_email' => 'test@customer.com',
        'address' => 'Test Address',
        'city' => 'Istanbul',
        'district' => 'Kadikoy',
        'discovery' => 'Test discovery with assignee',
        'todo_list' => 'Test todo list',
        'offer_valid_until' => now()->addDays(7),
        'status' => 'pending',
    ]);
    
    // Load relationships
    $discovery->load(['creator', 'assignee', 'company']);
    
    echo "\nCreated Discovery:\n";
    echo "ID: {$discovery->id}\n";
    echo "Creator: {$discovery->creator->name}\n";
    echo "Assignee: {$discovery->assignee->name}\n";
    echo "Company: {$discovery->company->name}\n";
    echo "Status: {$discovery->status}\n";
    
    // Test API Show method
    Auth::login($companyAdmin);
    $controller = new App\Http\Controllers\DiscoveryController();
    $response = $controller->apiShow($discovery);
    
    $responseData = json_decode($response->getContent(), true);
    
    echo "\nAPI Show Response:\n";
    echo "Success: " . ($responseData['success'] ? 'true' : 'false') . "\n";
    
    if ($responseData['success'] && isset($responseData['data']['assignee'])) {
        $assigneeData = $responseData['data']['assignee'];
        echo "Assignee in API: {$assigneeData['name']} ({$assigneeData['email']})\n";
        echo "Assignee Type: {$assigneeData['user_type']}\n";
    } else {
        echo "No assignee data in API response\n";
    }
    
    // Clean up the test discovery
    $discovery->delete();
    echo "\nTest discovery cleaned up.\n";
    
    echo "\nDiscovery assignee test complete!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
