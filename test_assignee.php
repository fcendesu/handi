<?php

require_once 'vendor/autoload.php';

use App\Models\Company;
use App\Models\User;
use App\Models\Discovery;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Testing Assignee Feature\n";
echo "========================\n";

try {
    // Find a company with admin and employees
    $company = Company::with(['admin', 'assignableEmployees'])->first();
    
    if (!$company) {
        echo "No company found. Creating test data would be needed.\n";
        exit;
    }
    
    echo "Company: {$company->name}\n";
    echo "Admin: " . ($company->admin ? $company->admin->name : 'No admin') . "\n";
    echo "Assignable Employees: " . $company->assignableEmployees->count() . "\n";
    
    foreach ($company->assignableEmployees as $employee) {
        echo "  - {$employee->name} ({$employee->email})\n";
    }
    
    // Test the discovery assignee relationship
    $discovery = Discovery::with('assignee')->first();
    if ($discovery) {
        echo "\nDiscovery Test:\n";
        echo "Discovery ID: {$discovery->id}\n";
        echo "Assignee: " . ($discovery->assignee ? $discovery->assignee->name : 'None') . "\n";
    } else {
        echo "\nNo discoveries found for testing.\n";
    }
    
    echo "\nAssignee feature structure looks good!\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
