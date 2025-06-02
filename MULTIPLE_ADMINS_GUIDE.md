# Multiple Company Admins Implementation Guide

## Overview

The Laravel Handyman system now supports multiple company administrators with hierarchical permissions. This guide explains how to create and manage multiple company admins.

## System Architecture

### Admin Types

1. **Primary Admin** (`company.admin_id`) - The founder/owner with full permissions
2. **Secondary Admins** - Users with `user_type = 'company_admin'` but not primary

### Permission Matrix

| Action                | Primary Admin | Secondary Admin | Employee |
| --------------------- | ------------- | --------------- | -------- |
| Create new admins     | ✅            | ❌              | ❌       |
| Promote employees     | ✅            | ❌              | ❌       |
| Demote admins         | ✅            | ❌              | ❌       |
| Transfer primary role | ✅            | ❌              | ❌       |
| Delete company        | ✅            | ❌              | ❌       |
| Manage employees      | ✅            | ✅              | ❌       |
| Manage work groups    | ✅            | ✅              | ❌       |
| View company data     | ✅            | ✅              | ❌       |

## Methods to Create Additional Admins

### Method 1: Promote Existing Employee

**Use Case**: When you want to give admin privileges to a trusted employee.

**Steps**:

1. Go to Company Management dashboard
2. Find the employee in the "Employee Management" section
3. Click the promote button (up arrow icon)
4. Confirm the promotion

**Code Implementation**:

```php
// Route: PATCH /company/employees/{employee}/promote
public function promoteToAdmin(User $employee)
{
    // Only primary admin can promote
    if (auth()->user()->company->admin_id !== auth()->user()->id) {
        abort(403, 'Only the primary company admin can promote employees.');
    }

    $employee->update(['user_type' => User::TYPE_COMPANY_ADMIN]);
}
```

### Method 2: Create New Admin Directly

**Use Case**: When you want to bring in an external person as an admin.

**Steps**:

1. Go to Company Management dashboard
2. Click "Yeni Yönetici Ekle" in the "Şirket Yöneticileri" section
3. Fill in the admin details (name, email, password)
4. Submit the form

**Code Implementation**:

```php
// Route: POST /company/admins
public function createAdmin(Request $request)
{
    $admin = User::create([
        'name' => $validated['name'],
        'email' => $validated['email'],
        'password' => Hash::make($validated['password']),
        'user_type' => User::TYPE_COMPANY_ADMIN,
        'company_id' => auth()->user()->company_id,
    ]);
}
```

### Method 3: Invitation System (Future Enhancement)

You can extend the existing invitation system to support admin invitations:

```php
// Create admin invitation
$invitation = Invitation::create([
    'code' => Invitation::generateCode(),
    'email' => 'newadmin@example.com',
    'company_id' => $company->id,
    'invited_by' => auth()->user()->id,
    'user_type' => User::TYPE_COMPANY_ADMIN, // New field
    'expires_at' => now()->addDays(7),
]);
```

## Advanced Management

### Transfer Primary Admin Role

**Use Case**: When the founder wants to hand over control to someone else.

**Steps**:

1. Ensure the target person is already a company admin
2. Click "Ana Yönetici Yap" next to their name
3. Confirm the transfer

**Warning**: This action transfers all primary admin privileges including the ability to delete the company.

### Demote Admin to Employee

**Use Case**: When an admin no longer needs administrative privileges.

**Steps**:

1. Find the admin in the "Şirket Yöneticileri" section
2. Click the demote button (down arrow icon)
3. Confirm the demotion

**Note**: You cannot demote yourself or the primary admin.

## Security Considerations

### Access Control

1. **Primary Admin Protection**: Only the primary admin can create/manage other admins
2. **Self-Protection**: Users cannot demote themselves
3. **Company Scope**: All admin operations are scoped to the specific company
4. **Policy Enforcement**: Laravel policies ensure proper authorization

### Database Integrity

1. **Foreign Key Constraints**: Proper relationships between users and companies
2. **Cascade Handling**: When primary admin is deleted, company admin_id is set to null
3. **Transaction Safety**: All multi-step operations use database transactions

## UI Components

### Admin Management Section

The company dashboard now includes a dedicated "Şirket Yöneticileri" section showing:

- Primary admin with special badge
- All secondary admins
- Action buttons (promote, demote, transfer) based on permissions

### Employee Actions

Employee rows now include a promote button for the primary admin to easily elevate trusted employees.

### Modal Dialogs

- Create Admin Modal
- Promote Employee Modal
- Demote Admin Modal
- Transfer Primary Admin Modal

## API Endpoints

All admin management functionality is available via web routes. For API access, you can extend the existing API controllers:

```php
// Add to CompanyController
public function apiCreateAdmin(Request $request): JsonResponse
public function apiPromoteEmployee(User $employee): JsonResponse
public function apiDemoteAdmin(User $admin): JsonResponse
public function apiTransferPrimaryAdmin(Request $request): JsonResponse
```

## Testing

Run the demo script to see the functionality in action:

```bash
php demo_multiple_admins.php
```

This script demonstrates:

1. Creating a company with primary admin
2. Adding employees
3. Creating secondary admins
4. Promoting employees to admins
5. Transferring primary admin role
6. Access control validation

## Troubleshooting

### Common Issues

1. **403 Forbidden**: Ensure you're logged in as the primary admin
2. **User not found**: Verify the user belongs to your company
3. **Cannot demote**: You can't demote yourself or when you're not primary admin

### Debugging

Check the Laravel logs for detailed error messages:

```bash
tail -f storage/logs/laravel.log
```

## Best Practices

1. **Limit Admin Count**: Don't create too many admins to maintain security
2. **Regular Audits**: Periodically review who has admin access
3. **Proper Handover**: When transferring primary admin role, ensure proper communication
4. **Documentation**: Keep track of why each person was made an admin
5. **Training**: Ensure new admins understand their responsibilities and limitations

## Future Enhancements

1. **Role-Based Permissions**: More granular permissions for different admin types
2. **Admin Activity Logs**: Track what each admin does for audit purposes
3. **Time-Limited Admin Access**: Temporary admin roles that expire
4. **Admin Approval Workflow**: Require approval from multiple admins for sensitive actions
5. **Admin Invitation System**: Extend invitation system for admin roles
