<?php

namespace App\Policies;

use App\Models\Property;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PropertyPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Company employees and admins can view their company's properties
        return $user->isCompanyEmployee() || $user->isCompanyAdmin();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Property $property): bool
    {
        // Users can only view properties from their own company
        return $user->company_id === $property->company_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Company employees and admins can create properties
        return $user->isCompanyEmployee() || $user->isCompanyAdmin();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Property $property): bool
    {
        // Users can only update properties from their own company
        return $user->company_id === $property->company_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Property $property): bool
    {
        // Users can only delete properties from their own company
        return $user->company_id === $property->company_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Property $property): bool
    {
        // Users can only restore properties from their own company
        return $user->company_id === $property->company_id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Property $property): bool
    {
        // Only company admins can permanently delete properties
        return $user->isCompanyAdmin() && $user->company_id === $property->company_id;
    }
}
