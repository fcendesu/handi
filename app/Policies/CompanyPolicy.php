<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Company;

class CompanyPolicy
{
    /**
     * Determine whether the user can view any companies.
     */
    public function viewAny(User $user): bool
    {
        // Only company admins can view company data
        return $user->isCompanyAdmin();
    }

    /**
     * Determine whether the user can view the company.
     */
    public function view(User $user, Company $company): bool
    {
        // Only company admin can view their company
        return $user->isCompanyAdmin() && $user->company_id === $company->id;
    }

    /**
     * Determine whether the user can create companies.
     */
    public function create(User $user): bool
    {
        // Companies are typically created during registration
        // This could be restricted further based on business logic
        return true;
    }

    /**
     * Determine whether the user can update the company.
     */
    public function update(User $user, Company $company): bool
    {
        // Only company admin can update their company
        return $user->isCompanyAdmin() && $user->company_id === $company->id;
    }

    /**
     * Determine whether the user can delete the company.
     */
    public function delete(User $user, Company $company): bool
    {
        // Only the primary admin (founder) can delete the company
        return $user->isCompanyAdmin() && $company->admin_id === $user->id;
    }

    /**
     * Determine whether the user can restore the company.
     */
    public function restore(User $user, Company $company): bool
    {
        return $this->delete($user, $company);
    }

    /**
     * Determine whether the user can permanently delete the company.
     */
    public function forceDelete(User $user, Company $company): bool
    {
        return $this->delete($user, $company);
    }

    /**
     * Determine whether the user can manage employees.
     */
    public function manageEmployees(User $user, Company $company): bool
    {
        // Only company admin can manage employees
        return $user->isCompanyAdmin() && $user->company_id === $company->id;
    }
}
