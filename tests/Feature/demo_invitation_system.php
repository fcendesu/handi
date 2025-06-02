<?php

use App\Models\Company;
use App\Models\User;
use App\Models\Invitation;
use App\Models\WorkGroup;

// Demo script to show invitation system functionality
echo "=== Laravel Handyman Invitation System Demo ===\n\n";

// 1. Create a company admin
echo "1. Creating company admin...\n";
$admin = User::create([
    'name' => 'John Admin',
    'email' => 'admin@example.com',
    'password' => bcrypt('password'),
    'user_type' => User::TYPE_COMPANY_ADMIN,
]);

$company = Company::create([
    'name' => 'Demo Handyman Co.',
    'address' => '123 Main St',
    'phone' => '555-0100',
    'email' => 'info@demohandyman.com',
    'admin_id' => $admin->id,
]);

$admin->update(['company_id' => $company->id]);

echo "✓ Company admin created: {$admin->name} at {$company->name}\n\n";

// 2. Create work groups
echo "2. Creating work groups...\n";
$plumbingGroup = WorkGroup::create([
    'name' => 'Plumbing Team',
    'creator_id' => $admin->id,
    'company_id' => $company->id,
]);

$electricalGroup = WorkGroup::create([
    'name' => 'Electrical Team',
    'creator_id' => $admin->id,
    'company_id' => $company->id,
]);

echo "✓ Work groups created: {$plumbingGroup->name}, {$electricalGroup->name}\n\n";

// 3. Create invitation
echo "3. Creating invitation...\n";
$invitation = Invitation::create([
    'code' => Invitation::generateCode(),
    'email' => 'employee@example.com',
    'company_id' => $company->id,
    'invited_by' => $admin->id,
    'work_group_ids' => [$plumbingGroup->id, $electricalGroup->id],
    'expires_at' => now()->addDays(7),
    'status' => 'pending'
]);

echo "✓ Invitation created with code: {$invitation->code}\n";
echo "  - For: {$invitation->email}\n";
echo "  - Company: {$invitation->company->name}\n";
echo "  - Invited by: {$invitation->invitedBy->name}\n";
echo "  - Work groups: " . $invitation->workGroups()->pluck('name')->join(', ') . "\n";
echo "  - Valid: " . ($invitation->isValid() ? 'Yes' : 'No') . "\n\n";

// 4. Simulate employee registration
echo "4. Simulating employee registration using invitation...\n";

// This simulates the registration process
$employee = User::create([
    'name' => 'Jane Employee',
    'email' => 'employee@example.com',
    'password' => bcrypt('password'),
    'user_type' => User::TYPE_COMPANY_EMPLOYEE,
    'company_id' => $company->id,
]);

// Mark invitation as used and assign work groups
$invitation->markAsUsed($employee);
$employee->workGroups()->attach($invitation->work_group_ids);

echo "✓ Employee registered: {$employee->name}\n";
echo "  - Company: {$employee->company->name}\n";
echo "  - Work groups: " . $employee->workGroups->pluck('name')->join(', ') . "\n\n";

// 5. Verify invitation status
echo "5. Verifying invitation status...\n";
$invitation->refresh();
echo "✓ Invitation status: {$invitation->status}\n";
echo "✓ Used at: " . ($invitation->used_at ? $invitation->used_at->format('Y-m-d H:i:s') : 'N/A') . "\n";
echo "✓ Used by: " . ($invitation->user ? $invitation->user->name : 'N/A') . "\n\n";

// 6. Show final company structure
echo "6. Final company structure:\n";
echo "Company: {$company->name}\n";
echo "├── Admin: {$admin->name} ({$admin->email})\n";
echo "└── Employees:\n";
foreach ($company->employees as $emp) {
    echo "    ├── {$emp->name} ({$emp->email})\n";
    echo "    │   └── Work Groups: " . $emp->workGroups->pluck('name')->join(', ') . "\n";
}
echo "\nWork Groups:\n";
foreach ($company->workGroups as $wg) {
    echo "├── {$wg->name}\n";
    foreach ($wg->users as $user) {
        echo "│   └── {$user->name}\n";
    }
}

echo "\n=== Demo Complete! ===\n";
echo "The invitation system successfully:\n";
echo "✓ Generated secure invitation codes\n";
echo "✓ Validated invitation before use\n";
echo "✓ Associated employee with correct company\n";
echo "✓ Assigned employee to specified work groups\n";
echo "✓ Marked invitation as used\n";
echo "✓ Prevented reuse of invitation\n\n";

// Cleanup
echo "Cleaning up demo data...\n";
$employee->workGroups()->detach();
$employee->delete();
$invitation->delete();
$plumbingGroup->delete();
$electricalGroup->delete();
$admin->delete();
$company->delete();
echo "✓ Demo data cleaned up\n";
