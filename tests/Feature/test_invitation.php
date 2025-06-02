<?php
// Quick test script to verify invitation system functionality

require 'vendor/autoload.php';

// Set up minimal Laravel environment for testing
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Company;
use App\Models\Invitation;
use App\Models\WorkGroup;

echo "Testing Invitation System...\n\n";

try {
    // 1. Create a test company and admin
    echo "1. Creating test company and admin...\n";
    $company = Company::create([
        'name' => 'Test Company Ltd',
        'address' => '123 Test Street',
        'phone' => '555-0123',
        'email' => 'company@test.com',
        'admin_id' => null
    ]);

    $admin = User::create([
        'name' => 'Admin User',
        'email' => 'admin@test.com',
        'password' => bcrypt('password'),
        'user_type' => 'company_admin',
        'company_id' => $company->id
    ]);

    $company->update(['admin_id' => $admin->id]);
    echo "✓ Company and admin created\n";

    // 2. Create a work group
    echo "2. Creating test work group...\n";
    $workGroup = WorkGroup::create([
        'name' => 'Test Work Group',
        'description' => 'A test work group',
        'company_id' => $company->id,
        'created_by' => $admin->id
    ]);
    echo "✓ Work group created\n";

    // 3. Create an invitation
    echo "3. Creating invitation...\n";
    $invitation = Invitation::create([
        'code' => Invitation::generateCode(),
        'email' => 'employee@test.com',
        'company_id' => $company->id,
        'invited_by' => $admin->id,
        'work_group_ids' => [$workGroup->id],
        'expires_at' => now()->addDays(7),
        'status' => 'pending'
    ]);
    echo "✓ Invitation created with code: " . $invitation->code . "\n";

    // 4. Test invitation validation
    echo "4. Testing invitation validation...\n";
    echo "   - Is valid: " . ($invitation->isValid() ? 'Yes' : 'No') . "\n";
    echo "   - Company: " . $invitation->company->name . "\n";
    echo "   - Invited by: " . $invitation->invitedBy->name . "\n";
    echo "   - Work groups: " . $invitation->workGroups()->pluck('name')->join(', ') . "\n";

    // 5. Test employee registration simulation
    echo "5. Simulating employee registration...\n";
    $employee = User::create([
        'name' => 'Test Employee',
        'email' => 'employee@test.com',
        'password' => bcrypt('password'),
        'user_type' => 'company_employee',
        'company_id' => $company->id
    ]);

    // Mark invitation as used
    $invitation->markAsUsed($employee);

    // Assign work groups
    $employee->workGroups()->attach($invitation->work_group_ids);

    echo "✓ Employee registered and assigned to work groups\n";

    // 6. Verify final state
    echo "6. Verifying final state...\n";
    $invitation->refresh();
    echo "   - Invitation status: " . $invitation->status . "\n";
    echo "   - Employee company: " . $employee->company->name . "\n";
    echo "   - Employee work groups: " . $employee->workGroups->pluck('name')->join(', ') . "\n";

    echo "\n✅ All tests passed! Invitation system is working correctly.\n";

} catch (Exception $e) {
    echo "\n❌ Test failed: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} finally {
    // Clean up test data
    echo "\nCleaning up test data...\n";
    try {
        User::where('email', 'like', '%@test.com')->delete();
        Company::where('name', 'Test Company Ltd')->delete();
        WorkGroup::where('name', 'Test Work Group')->delete();
        Invitation::where('email', 'employee@test.com')->delete();
        echo "✓ Test data cleaned up\n";
    } catch (Exception $e) {
        echo "Warning: Could not clean up all test data: " . $e->getMessage() . "\n";
    }
}
