<?php

require_once 'vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use App\Models\Company;
use App\Models\User;
use App\Models\WorkGroup;
use App\Models\Discovery;

// Initialize Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Assignee Work Group Integration Test ===\n\n";

try {
    // Find test company
    $company = Company::where('name', 'like', '%Test%')->first();
    if (!$company) {
        echo "❌ No test company found. Creating one...\n";
        $company = Company::create([
            'name' => 'Test Company for Assignee',
            'email' => 'test@assignee.com',
            'phone' => '1234567890',
            'address' => 'Test Address',
            'subscription_status' => 'active'
        ]);
    }
    echo "✅ Using company: {$company->name} (ID: {$company->id})\n\n";

    // Create a company admin first
    $adminEmail = 'admin-' . time() . '@testcompany.com';
    $admin = User::create([
        'name' => 'Admin User',
        'email' => $adminEmail,
        'password' => bcrypt('password'),
        'user_type' => User::TYPE_COMPANY_ADMIN,
        'company_id' => $company->id
    ]);
    echo "✅ Created admin: {$admin->name} (ID: {$admin->id})\n\n";

    // Create work groups
    $workGroup1 = WorkGroup::create([
        'company_id' => $company->id,
        'creator_id' => $admin->id,
        'name' => 'Development Team'
    ]);

    $workGroup2 = WorkGroup::create([
        'company_id' => $company->id,
        'creator_id' => $admin->id,
        'name' => 'Support Team'
    ]);

    echo "✅ Created work groups:\n";
    echo "  - {$workGroup1->name} (ID: {$workGroup1->id})\n";
    echo "  - {$workGroup2->name} (ID: {$workGroup2->id})\n\n";

    // Create employees and assign to work groups
    $employee1Email = 'alice-' . time() . '@testcompany.com';
    $employee2Email = 'bob-' . time() . '@testcompany.com';

    $employee1 = User::create([
        'name' => 'Alice Developer',
        'email' => $employee1Email,
        'password' => bcrypt('password'),
        'user_type' => User::TYPE_COMPANY_EMPLOYEE,
        'company_id' => $company->id
    ]);

    $employee2 = User::create([
        'name' => 'Bob Support',
        'email' => $employee2Email,
        'password' => bcrypt('password'),
        'user_type' => User::TYPE_COMPANY_EMPLOYEE,
        'company_id' => $company->id
    ]);

    // Assign employees to work groups
    $employee1->workGroups()->attach($workGroup1->id);
    $employee2->workGroups()->attach($workGroup2->id);

    echo "✅ Created employees:\n";
    echo "  - {$employee1->name} (ID: {$employee1->id}) - assigned to {$workGroup1->name}\n";
    echo "  - {$employee2->name} (ID: {$employee2->id}) - assigned to {$workGroup2->name}\n\n";

    // Test 1: Create discovery and assign employee to matching work group
    echo "=== Test 1: Valid Assignment ===\n";
    $discovery1 = Discovery::create([
        'title' => 'Test Discovery 1',
        'creator_id' => $admin->id,
        'company_id' => $company->id,
        'work_group_id' => $workGroup1->id,
        'assignee_id' => $employee1->id,
        'customer_name' => 'Test Customer',
        'customer_phone' => '1234567890',
        'customer_email' => 'customer@example.com',
        'discovery' => 'Test discovery description',
        'todo_list' => 'Test todo items',
        'priority' => 2,
        'due_date' => now()->addDays(7),
        'address' => 'Test Address 1',
        'latitude' => 41.0082,
        'longitude' => 28.9784
    ]);
    echo "✅ Successfully assigned {$employee1->name} to discovery in {$workGroup1->name}\n";

    // Test 2: Try to assign employee to non-matching work group (should fail in controller validation)
    echo "\n=== Test 2: Invalid Assignment (Different Work Group) ===\n";
    try {
        $discovery2 = Discovery::create([
            'title' => 'Test Discovery 2',
            'creator_id' => $admin->id,
            'company_id' => $company->id,
            'work_group_id' => $workGroup1->id,  // Development team
            'assignee_id' => $employee2->id,      // Support employee
            'customer_name' => 'Test Customer 2',
            'customer_phone' => '1234567890',
            'customer_email' => 'customer2@example.com',
            'discovery' => 'Test discovery description 2',
            'todo_list' => 'Test todo items 2',
            'priority' => 2,
            'due_date' => now()->addDays(7),
            'address' => 'Test Address 2',
            'latitude' => 41.0082,
            'longitude' => 28.9784
        ]);
        echo "❌ Should have failed validation but didn't!\n";
    } catch (Exception $e) {
        echo "✅ Validation would catch this in controller: " . $e->getMessage() . "\n";
    }

    // Test 3: Test API endpoint for assignable employees
    echo "\n=== Test 3: API Endpoint Test ===\n";

    // Simulate the API call by testing the Company model method
    $assignableEmployees = $company->assignableEmployees()
        ->with('workGroups')
        ->get()
        ->map(function ($employee) {
            return [
                'id' => $employee->id,
                'name' => $employee->name,
                'email' => $employee->email,
                'work_groups' => $employee->workGroups->map(function ($workGroup) {
                    return [
                        'id' => $workGroup->id,
                        'name' => $workGroup->name
                    ];
                })
            ];
        });

    echo "✅ Assignable employees with work groups:\n";
    foreach ($assignableEmployees as $employee) {
        echo "  - {$employee['name']} ({$employee['email']})\n";
        echo "    Work Groups: ";
        if ($employee['work_groups']->isEmpty()) {
            echo "None\n";
        } else {
            echo $employee['work_groups']->pluck('name')->join(', ') . "\n";
        }
    }

    // Test 4: Check work group membership validation
    echo "\n=== Test 4: Work Group Membership Validation ===\n";

    function canAssignEmployeeToWorkGroup($employee, $workGroupId)
    {
        return $employee->workGroups()->where('work_groups.id', $workGroupId)->exists();
    }

    $canAssign1 = canAssignEmployeeToWorkGroup($employee1, $workGroup1->id);
    $canAssign2 = canAssignEmployeeToWorkGroup($employee2, $workGroup1->id);

    echo "Can assign {$employee1->name} to {$workGroup1->name}: " . ($canAssign1 ? "✅ Yes" : "❌ No") . "\n";
    echo "Can assign {$employee2->name} to {$workGroup1->name}: " . ($canAssign2 ? "✅ Yes" : "❌ No") . "\n";

    // Cleanup
    echo "\n=== Cleanup ===\n";
    Discovery::where('company_id', $company->id)->delete();
    $employee1->workGroups()->detach();
    $employee2->workGroups()->detach();
    User::whereIn('id', [$employee1->id, $employee2->id, $admin->id])->delete();
    WorkGroup::whereIn('id', [$workGroup1->id, $workGroup2->id])->delete();
    echo "✅ Cleaned up test data\n";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== Test Complete ===\n";
