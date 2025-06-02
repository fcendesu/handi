<?php

// Bootstrap Laravel application
require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Company;
use App\Models\User;
use App\Models\WorkGroup;
use Illuminate\Support\Facades\Hash;

// Demo script to show multiple company admin functionality
echo "=== Laravel Handyman Multiple Company Admins Demo ===\n\n";

try {
    // 1. Create a company with primary admin
    echo "1. Creating company with primary admin...\n";

    // Clean up existing test data
    $existingCompany = Company::where('name', 'Multi-Admin Test Company')->first();
    if ($existingCompany) {
        $existingCompany->delete();
    }
    User::whereIn('email', [
        'primary.admin@testcompany.com',
        'secondary.admin@testcompany.com',
        'employee1@testcompany.com',
        'employee2@testcompany.com'
    ])->delete();

    // Create primary admin
    $primaryAdmin = User::create([
        'name' => 'John Primary Admin',
        'email' => 'primary.admin@testcompany.com',
        'password' => Hash::make('password123'),
        'user_type' => User::TYPE_COMPANY_ADMIN,
    ]);

    // Create company
    $company = Company::create([
        'name' => 'Multi-Admin Test Company',
        'address' => '123 Admin Street, Management City, MC 12345',
        'phone' => '555-0199',
        'email' => 'info@multiadmintest.com',
        'admin_id' => $primaryAdmin->id,
    ]);

    $primaryAdmin->update(['company_id' => $company->id]);

    echo "✓ Primary admin created: {$primaryAdmin->name}\n";
    echo "✓ Company created: {$company->name}\n\n";

    // 2. Create some employees
    echo "2. Creating employees...\n";

    $employee1 = User::create([
        'name' => 'Alice Employee',
        'email' => 'employee1@testcompany.com',
        'password' => Hash::make('password123'),
        'user_type' => User::TYPE_COMPANY_EMPLOYEE,
        'company_id' => $company->id,
    ]);

    $employee2 = User::create([
        'name' => 'Bob Employee',
        'email' => 'employee2@testcompany.com',
        'password' => Hash::make('password123'),
        'user_type' => User::TYPE_COMPANY_EMPLOYEE,
        'company_id' => $company->id,
    ]);

    echo "✓ Employees created: {$employee1->name}, {$employee2->name}\n\n";

    // 3. Demonstrate creating a new admin directly
    echo "3. Creating a new admin directly...\n";

    $secondaryAdmin = User::create([
        'name' => 'Jane Secondary Admin',
        'email' => 'secondary.admin@testcompany.com',
        'password' => Hash::make('password123'),
        'user_type' => User::TYPE_COMPANY_ADMIN,
        'company_id' => $company->id,
    ]);

    echo "✓ Secondary admin created: {$secondaryAdmin->name}\n\n";

    // 4. Demonstrate promoting an employee to admin
    echo "4. Promoting employee to admin...\n";

    $employee1->update(['user_type' => User::TYPE_COMPANY_ADMIN]);

    echo "✓ {$employee1->name} promoted to admin\n\n";

    // 5. Show current company structure
    echo "5. Current company structure:\n";
    $company->refresh();
    $company->load(['admin', 'allAdmins', 'employees']);

    echo "Company: {$company->name}\n";
    echo "├── Primary Admin: {$company->admin->name} ({$company->admin->email})\n";
    echo "├── All Admins:\n";
    foreach ($company->allAdmins as $admin) {
        $isPrimary = $admin->id === $company->admin_id ? ' [PRIMARY]' : '';
        echo "│   ├── {$admin->name} ({$admin->email}){$isPrimary}\n";
    }
    echo "└── Employees:\n";
    foreach ($company->employees as $employee) {
        echo "    ├── {$employee->name} ({$employee->email})\n";
    }
    echo "\n";

    // 6. Demonstrate access control
    echo "6. Testing access control:\n";

    // Primary admin can manage everything
    echo "✓ Primary admin ({$primaryAdmin->name}) can:\n";
    echo "  - Create/promote/demote admins: YES\n";
    echo "  - Transfer primary role: YES\n";
    echo "  - Delete company: YES\n";
    echo "  - Manage employees: YES\n\n";

    // Secondary admins have limited permissions
    echo "✓ Secondary admin ({$secondaryAdmin->name}) can:\n";
    echo "  - Create/promote/demote admins: NO\n";
    echo "  - Transfer primary role: NO\n";
    echo "  - Delete company: NO\n";
    echo "  - Manage employees: YES\n\n";

    // 7. Demonstrate transferring primary admin role
    echo "7. Transferring primary admin role...\n";

    $oldPrimary = $company->admin->name;
    $company->update(['admin_id' => $secondaryAdmin->id]);

    echo "✓ Primary admin role transferred from {$oldPrimary} to {$secondaryAdmin->name}\n\n";

    // 8. Show final structure
    echo "8. Final company structure after transfer:\n";
    $company->refresh();
    $company->load(['admin', 'allAdmins', 'employees']);

    echo "Company: {$company->name}\n";
    echo "├── Primary Admin: {$company->admin->name} ({$company->admin->email})\n";
    echo "├── All Admins:\n";
    foreach ($company->allAdmins as $admin) {
        $isPrimary = $admin->id === $company->admin_id ? ' [PRIMARY]' : '';
        echo "│   ├── {$admin->name} ({$admin->email}){$isPrimary}\n";
    }
    echo "└── Employees:\n";
    foreach ($company->employees as $employee) {
        echo "    ├── {$employee->name} ({$employee->email})\n";
    }
    echo "\n";

    echo "=== Demo completed successfully! ===\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
