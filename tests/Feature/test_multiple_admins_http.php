<?php

// Test HTTP endpoints for multiple admin functionality
// Bootstrap Laravel application
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Company;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Http\Controllers\CompanyController;

echo "=== Testing Multiple Admin HTTP Endpoints ===\n\n";

try {
    // 1. Set up test data
    echo "1. Setting up test data...\n";

    // Clean up
    $company = Company::where('name', 'HTTP Test Company')->first();
    if ($company) {
        $company->delete();
    }
    User::whereIn('email', ['http.admin@test.com', 'http.employee@test.com'])->delete();

    // Create company and admin
    $admin = User::create([
        'name' => 'HTTP Test Admin',
        'email' => 'http.admin@test.com',
        'password' => Hash::make('password123'),
        'user_type' => User::TYPE_COMPANY_ADMIN,
    ]);

    $company = Company::create([
        'name' => 'HTTP Test Company',
        'address' => '123 Test St',
        'phone' => '555-0100',
        'email' => 'info@httptest.com',
        'admin_id' => $admin->id,
    ]);

    $admin->update(['company_id' => $company->id]);

    // Create employee
    $employee = User::create([
        'name' => 'HTTP Test Employee',
        'email' => 'http.employee@test.com',
        'password' => Hash::make('password123'),
        'user_type' => User::TYPE_COMPANY_EMPLOYEE,
        'company_id' => $company->id,
    ]);

    echo "✓ Test data created\n\n";

    // 2. Test controller methods directly
    echo "2. Testing controller methods...\n";

    $controller = new CompanyController();

    // Simulate authentication
    auth()->login($admin);

    // Test promote employee
    echo "Testing promote employee...\n";
    $response = $controller->promoteToAdmin($employee);
    $employee->refresh();

    if ($employee->user_type === User::TYPE_COMPANY_ADMIN) {
        echo "✓ Employee promoted to admin successfully\n";
    } else {
        echo "❌ Employee promotion failed\n";
    }

    // Test create admin
    echo "Testing create new admin...\n";
    $request = new Request([
        'name' => 'New HTTP Admin',
        'email' => 'new.admin@test.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    try {
        $response = $controller->createAdmin($request);
        $newAdmin = User::where('email', 'new.admin@test.com')->first();
        if ($newAdmin && $newAdmin->isCompanyAdmin()) {
            echo "✓ New admin created successfully\n";
        } else {
            echo "❌ New admin creation failed\n";
        }
    } catch (Exception $e) {
        echo "❌ Create admin error: " . $e->getMessage() . "\n";
    }

    // Test transfer primary admin
    echo "Testing transfer primary admin...\n";
    $transferRequest = new Request(['new_admin_id' => $employee->id]);

    try {
        $response = $controller->transferPrimaryAdmin($transferRequest);
        $company->refresh();
        if ($company->admin_id === $employee->id) {
            echo "✓ Primary admin transferred successfully\n";
        } else {
            echo "❌ Primary admin transfer failed\n";
        }
    } catch (Exception $e) {
        echo "❌ Transfer error: " . $e->getMessage() . "\n";
    }

    // 3. Show final state
    echo "\n3. Final company state:\n";
    $company->load(['admin', 'allAdmins', 'employees']);

    echo "Company: {$company->name}\n";
    echo "Primary Admin: {$company->admin->name}\n";
    echo "Total Admins: " . $company->allAdmins->count() . "\n";
    echo "Total Employees: " . $company->employees->count() . "\n";

    foreach ($company->allAdmins as $admin) {
        $isPrimary = $admin->id === $company->admin_id ? ' [PRIMARY]' : '';
        echo "- Admin: {$admin->name}{$isPrimary}\n";
    }

    foreach ($company->employees as $emp) {
        echo "- Employee: {$emp->name}\n";
    }

    echo "\n=== HTTP endpoint tests completed! ===\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
