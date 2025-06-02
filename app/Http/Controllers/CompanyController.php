<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class CompanyController extends Controller
{
    use AuthorizesRequests;
    public function index()
    {
        $user = auth()->user();

        // Only company admins can view their company details
        if (!$user->isCompanyAdmin()) {
            abort(403, 'Only company admins can access company management.');
        }

        $company = $user->managedCompany ?? $user->company;
        $company->load(['admin', 'employees', 'workGroups', 'discoveries']);

        return view('company.index', compact('company'));
    }

    public function show(Company $company)
    {
        $user = auth()->user();

        // Only company admin can view their company
        if (!$user->isCompanyAdmin() || $user->company_id !== $company->id) {
            abort(403, 'You can only view your own company details.');
        }

        $company->load(['admin', 'employees', 'workGroups', 'discoveries']);
        return view('company.show', compact('company'));
    }

    public function update(Request $request, Company $company)
    {
        $user = auth()->user();

        // Only company admin can update their company
        if (!$user->isCompanyAdmin() || $user->company_id !== $company->id) {
            abort(403, 'You can only update your own company details.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:1000',
            'phone' => 'nullable|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('companies')->ignore($company->id)
            ],
        ]);

        try {
            $company->update($validated);

            return redirect()
                ->route('company.show', $company)
                ->with('success', 'Company details updated successfully');

        } catch (\Exception $e) {
            \Log::error('Company update failed: ' . $e->getMessage());
            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to update company: ' . $e->getMessage()]);
        }
    }

    // Employee Management Methods
    public function createEmployee(Request $request)
    {
        $user = auth()->user();

        // Only company admins can create employees
        if (!$user->isCompanyAdmin()) {
            abort(403, 'Only company admins can create employees.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'work_group_ids' => 'nullable|array',
            'work_group_ids.*' => 'exists:work_groups,id',
        ]);

        try {
            // Create employee
            $employee = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'user_type' => User::TYPE_COMPANY_EMPLOYEE,
                'company_id' => $user->company_id,
            ]);

            // Assign to work groups if specified
            if (!empty($validated['work_group_ids'])) {
                // Validate work groups belong to the company
                $validWorkGroups = $user->company->workGroups()
                    ->whereIn('id', $validated['work_group_ids'])
                    ->pluck('id');

                $employee->workGroups()->attach($validWorkGroups);
            }

            return redirect()
                ->route('company.index')
                ->with('success', 'Employee created successfully');

        } catch (\Exception $e) {
            \Log::error('Employee creation failed: ' . $e->getMessage());
            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to create employee: ' . $e->getMessage()]);
        }
    }

    public function updateEmployee(Request $request, User $employee)
    {
        $user = auth()->user();

        // Only company admin can update employees from their company
        if (!$user->isCompanyAdmin() || $employee->company_id !== $user->company_id || !$employee->isCompanyEmployee()) {
            abort(403, 'You can only update employees from your company.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users')->ignore($employee->id)
            ],
            'password' => 'nullable|string|min:8|confirmed',
            'work_group_ids' => 'nullable|array',
            'work_group_ids.*' => 'exists:work_groups,id',
        ]);

        try {
            // Update employee details
            $updateData = [
                'name' => $validated['name'],
                'email' => $validated['email'],
            ];

            if (!empty($validated['password'])) {
                $updateData['password'] = Hash::make($validated['password']);
            }

            $employee->update($updateData);

            // Update work group assignments
            if (isset($validated['work_group_ids'])) {
                // Validate work groups belong to the company
                $validWorkGroups = $user->company->workGroups()
                    ->whereIn('id', $validated['work_group_ids'])
                    ->pluck('id');

                $employee->workGroups()->sync($validWorkGroups);
            }

            return redirect()
                ->route('company.index')
                ->with('success', 'Employee updated successfully');

        } catch (\Exception $e) {
            \Log::error('Employee update failed: ' . $e->getMessage());
            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to update employee: ' . $e->getMessage()]);
        }
    }

    public function deleteEmployee(User $employee)
    {
        $user = auth()->user();

        // Only company admin can delete employees from their company
        if (!$user->isCompanyAdmin() || $employee->company_id !== $user->company_id || !$employee->isCompanyEmployee()) {
            abort(403, 'You can only delete employees from your company.');
        }

        try {
            $employee->delete();
            return redirect()
                ->route('company.index')
                ->with('success', 'Employee deleted successfully');
        } catch (\Exception $e) {
            \Log::error('Employee deletion failed: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to delete employee: ' . $e->getMessage()]);
        }
    }

    // API Methods
    public function apiShow(): JsonResponse
    {
        try {
            $user = auth()->user();

            // Only company admin can view company details
            if (!$user->isCompanyAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only company admins can access company details.'
                ], 403);
            }

            $company = $user->managedCompany ?? $user->company;
            $company->load(['admin', 'employees', 'workGroups']);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $company->id,
                    'name' => $company->name,
                    'address' => $company->address,
                    'phone' => $company->phone,
                    'email' => $company->email,
                    'admin' => [
                        'id' => $company->admin->id,
                        'name' => $company->admin->name,
                        'email' => $company->admin->email,
                    ],
                    'employees' => $company->employees->map(function ($employee) {
                        return [
                            'id' => $employee->id,
                            'name' => $employee->name,
                            'email' => $employee->email,
                            'work_groups_count' => $employee->workGroups()->count(),
                            'assigned_discoveries_count' => $employee->assignedDiscoveries()->count(),
                            'created_at' => $employee->created_at,
                        ];
                    }),
                    'work_groups' => $company->workGroups->map(function ($workGroup) {
                        return [
                            'id' => $workGroup->id,
                            'name' => $workGroup->name,
                            'users_count' => $workGroup->users()->count(),
                            'discoveries_count' => $workGroup->discoveries()->count(),
                        ];
                    }),
                    'statistics' => [
                        'total_employees' => $company->employees()->count(),
                        'total_work_groups' => $company->workGroups()->count(),
                        'total_discoveries' => $company->discoveries()->count(),
                        'pending_discoveries' => $company->discoveries()->where('status', 'pending')->count(),
                        'in_progress_discoveries' => $company->discoveries()->where('status', 'in_progress')->count(),
                        'completed_discoveries' => $company->discoveries()->where('status', 'completed')->count(),
                    ],
                    'created_at' => $company->created_at,
                    'updated_at' => $company->updated_at,
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch company details',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function apiCreateEmployee(Request $request): JsonResponse
    {
        try {
            $user = auth()->user();

            // Only company admins can create employees
            if (!$user->isCompanyAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only company admins can create employees.'
                ], 403);
            }

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255|unique:users,email',
                'password' => 'required|string|min:8',
                'work_group_ids' => 'nullable|array',
                'work_group_ids.*' => 'exists:work_groups,id',
            ]);

            // Create employee
            $employee = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'user_type' => User::TYPE_COMPANY_EMPLOYEE,
                'company_id' => $user->company_id,
            ]);

            // Assign to work groups if specified
            if (!empty($validated['work_group_ids'])) {
                // Validate work groups belong to the company
                $validWorkGroups = $user->company->workGroups()
                    ->whereIn('id', $validated['work_group_ids'])
                    ->pluck('id');

                $employee->workGroups()->attach($validWorkGroups);
            }

            $employee->load('workGroups');

            return response()->json([
                'success' => true,
                'message' => 'Employee created successfully',
                'data' => [
                    'id' => $employee->id,
                    'name' => $employee->name,
                    'email' => $employee->email,
                    'user_type' => $employee->user_type,
                    'work_groups' => $employee->workGroups->map(function ($workGroup) {
                        return [
                            'id' => $workGroup->id,
                            'name' => $workGroup->name,
                        ];
                    }),
                    'created_at' => $employee->created_at,
                ]
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('API Employee creation failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create employee',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function apiUpdateEmployee(Request $request, User $employee): JsonResponse
    {
        try {
            $user = auth()->user();

            // Only company admin can update employees from their company
            if (!$user->isCompanyAdmin() || $employee->company_id !== $user->company_id || !$employee->isCompanyEmployee()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only update employees from your company.'
                ], 403);
            }

            $validated = $request->validate([
                'name' => 'sometimes|string|max:255',
                'email' => [
                    'sometimes',
                    'email',
                    'max:255',
                    Rule::unique('users')->ignore($employee->id)
                ],
                'password' => 'sometimes|string|min:8',
                'work_group_ids' => 'nullable|array',
                'work_group_ids.*' => 'exists:work_groups,id',
            ]);

            // Update employee details
            $updateData = [];
            if (isset($validated['name'])) {
                $updateData['name'] = $validated['name'];
            }
            if (isset($validated['email'])) {
                $updateData['email'] = $validated['email'];
            }
            if (isset($validated['password'])) {
                $updateData['password'] = Hash::make($validated['password']);
            }

            if (!empty($updateData)) {
                $employee->update($updateData);
            }

            // Update work group assignments
            if (isset($validated['work_group_ids'])) {
                // Validate work groups belong to the company
                $validWorkGroups = $user->company->workGroups()
                    ->whereIn('id', $validated['work_group_ids'])
                    ->pluck('id');

                $employee->workGroups()->sync($validWorkGroups);
            }

            $employee->refresh();
            $employee->load('workGroups');

            return response()->json([
                'success' => true,
                'message' => 'Employee updated successfully',
                'data' => [
                    'id' => $employee->id,
                    'name' => $employee->name,
                    'email' => $employee->email,
                    'user_type' => $employee->user_type,
                    'work_groups' => $employee->workGroups->map(function ($workGroup) {
                        return [
                            'id' => $workGroup->id,
                            'name' => $workGroup->name,
                        ];
                    }),
                    'updated_at' => $employee->updated_at,
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('API Employee update failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update employee',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function apiDeleteEmployee(User $employee): JsonResponse
    {
        try {
            $user = auth()->user();

            // Only company admin can delete employees from their company
            if (!$user->isCompanyAdmin() || $employee->company_id !== $user->company_id || !$employee->isCompanyEmployee()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only delete employees from your company.'
                ], 403);
            }

            $employeeName = $employee->name;
            $employee->delete();

            return response()->json([
                'success' => true,
                'message' => "Employee {$employeeName} deleted successfully"
            ]);

        } catch (\Exception $e) {
            \Log::error('API Employee deletion failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete employee',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Promote an existing employee to company admin
     */
    public function promoteToAdmin(User $employee)
    {
        $user = auth()->user();

        // Only primary admin can promote employees to admin
        if (!$user->isCompanyAdmin() || $user->company->admin_id !== $user->id) {
            abort(403, 'Only the primary company admin can promote employees to admin.');
        }

        // Verify employee belongs to this company
        if ($employee->company_id !== $user->company_id || !$employee->isCompanyEmployee()) {
            abort(403, 'You can only promote employees from your company.');
        }

        try {
            $employee->update(['user_type' => User::TYPE_COMPANY_ADMIN]);

            return redirect()
                ->route('company.index')
                ->with('success', $employee->name . ' has been promoted to company admin.');

        } catch (\Exception $e) {
            \Log::error('Employee promotion failed: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to promote employee: ' . $e->getMessage()]);
        }
    }

    /**
     * Demote a company admin to employee
     */
    public function demoteFromAdmin(User $admin)
    {
        $user = auth()->user();

        // Only primary admin can demote other admins
        if (!$user->isCompanyAdmin() || $user->company->admin_id !== $user->id) {
            abort(403, 'Only the primary company admin can demote other admins.');
        }

        // Cannot demote yourself (primary admin)
        if ($admin->id === $user->id) {
            abort(403, 'You cannot demote yourself. Transfer primary admin role first.');
        }

        // Verify admin belongs to this company
        if ($admin->company_id !== $user->company_id || !$admin->isCompanyAdmin()) {
            abort(403, 'You can only demote admins from your company.');
        }

        try {
            $admin->update(['user_type' => User::TYPE_COMPANY_EMPLOYEE]);

            return redirect()
                ->route('company.index')
                ->with('success', $admin->name . ' has been demoted to employee.');

        } catch (\Exception $e) {
            \Log::error('Admin demotion failed: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to demote admin: ' . $e->getMessage()]);
        }
    }

    /**
     * Create a new company admin directly
     */
    public function createAdmin(Request $request)
    {
        $user = auth()->user();

        // Only primary admin can create new admins
        if (!$user->isCompanyAdmin() || $user->company->admin_id !== $user->id) {
            abort(403, 'Only the primary company admin can create new admins.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'work_group_ids' => 'nullable|array',
            'work_group_ids.*' => 'exists:work_groups,id',
        ]);

        try {
            // Create admin
            $admin = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'user_type' => User::TYPE_COMPANY_ADMIN,
                'company_id' => $user->company_id,
            ]);

            // Assign to work groups if specified
            if (!empty($validated['work_group_ids'])) {
                // Validate work groups belong to the company
                $validWorkGroups = $user->company->workGroups()
                    ->whereIn('id', $validated['work_group_ids'])
                    ->pluck('id');

                $admin->workGroups()->attach($validWorkGroups);
            }

            return redirect()
                ->route('company.index')
                ->with('success', 'Company admin created successfully');

        } catch (\Exception $e) {
            \Log::error('Admin creation failed: ' . $e->getMessage());
            return back()
                ->withInput()
                ->withErrors(['error' => 'Failed to create admin: ' . $e->getMessage()]);
        }
    }

    /**
     * Transfer primary admin role to another admin
     */
    public function transferPrimaryAdmin(Request $request)
    {
        $user = auth()->user();

        // Only primary admin can transfer the role
        if (!$user->isCompanyAdmin() || $user->company->admin_id !== $user->id) {
            abort(403, 'Only the primary company admin can transfer the primary role.');
        }

        $validated = $request->validate([
            'new_admin_id' => 'required|exists:users,id'
        ]);

        $newAdmin = User::findOrFail($validated['new_admin_id']);

        // Verify new admin belongs to this company and is an admin
        if ($newAdmin->company_id !== $user->company_id || !$newAdmin->isCompanyAdmin()) {
            abort(403, 'New primary admin must be a company admin from your company.');
        }

        try {
            $user->company->update(['admin_id' => $newAdmin->id]);

            return redirect()
                ->route('company.index')
                ->with('success', 'Primary admin role transferred to ' . $newAdmin->name);

        } catch (\Exception $e) {
            \Log::error('Primary admin transfer failed: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to transfer primary admin role: ' . $e->getMessage()]);
        }
    }
}
