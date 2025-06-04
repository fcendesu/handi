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
        // Solo handymen, company employees and admins can view their properties
        return $user->isSoloHandyman() || $user->isCompanyEmployee() || $user->isCompanyAdmin();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Property $property): bool
    {
        // Solo handymen can view their own properties, company users can view company properties
        if ($user->isSoloHandyman()) {
            return $property->user_id === $user->id;
        }

        return $user->company_id === $property->company_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Solo handymen, company employees and admins can create properties
        return $user->isSoloHandyman() || $user->isCompanyEmployee() || $user->isCompanyAdmin();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Property $property): bool
    {
        // Solo handymen can update their own properties, company users can update company properties
        if ($user->isSoloHandyman()) {
            return $property->user_id === $user->id;
        }

        return $user->company_id === $property->company_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Property $property): bool
    {
        // Solo handymen can delete their own properties, company users can delete company properties
        if ($user->isSoloHandyman()) {
            return $property->user_id === $user->id;
        }

        return $user->company_id === $property->company_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Property $property): bool
    {
        // Solo handymen can restore their own properties, company users can restore company properties
        if ($user->isSoloHandyman()) {
            return $property->user_id === $user->id;
        }

        return $user->company_id === $property->company_id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Property $property): bool
    {
        // Only company admins and solo handymen can permanently delete properties
        if ($user->isSoloHandyman()) {
            return $property->user_id === $user->id;
        }

        return $user->isCompanyAdmin() && $user->company_id === $property->company_id;
    }
}
