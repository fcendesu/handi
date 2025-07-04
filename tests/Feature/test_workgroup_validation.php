<?php

require_once 'vendor/autoload.php';

use App\Models\User;
use App\Models\Discovery;
use App\Models\WorkGroup;
use Illuminate\Support\Facades\Auth;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Testing Work Group Assignment Validation\n";
echo "========================================\n";

try {
    // Find a company admin and employees
    $companyAdmin = User::where('user_type', 'company_admin')->first();
    $employee1 = User::where('user_type', 'company_employee')->first();

    if (!$companyAdmin || !$employee1) {
        echo "Missing required users (admin or employee).\n";
        exit;
    }

    // Find or create work groups
    $workGroup1 = WorkGroup::where('company_id', $companyAdmin->company_id)->first();

    if (!$workGroup1) {
        echo "No work groups found for testing.\n";
        exit;
    }

    echo "Admin: {$companyAdmin->name}\n";
    echo "Employee: {$employee1->name}\n";
    echo "Work Group: {$workGroup1->name}\n";

    // Check if employee is in the work group
    $employeeInWorkGroup = $employee1->workGroups()->where('work_groups.id', $workGroup1->id)->exists();
    echo "Employee in Work Group: " . ($employeeInWorkGroup ? 'Yes' : 'No') . "\n";

    // Test the API endpoint for assignable employees
    Auth::login($companyAdmin);
    $controller = new App\Http\Controllers\CompanyController();
    $response = $controller->getAssignableEmployees();

    $responseData = json_decode($response->getContent(), true);

    echo "\nAssignable Employees API Response:\n";
    if ($responseData['success'] && isset($responseData['data'])) {
        foreach ($responseData['data'] as $employee) {
            echo "- {$employee['name']} ({$employee['email']})\n";
            if (!empty($employee['work_groups'])) {
                foreach ($employee['work_groups'] as $workGroup) {
                    echo "  * Work Group: {$workGroup['name']}\n";
                }
            } else {
                echo "  * No work groups assigned\n";
            }
        }
    }

    echo "\nWork group validation test complete!\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
