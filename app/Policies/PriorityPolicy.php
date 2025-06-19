<?php

namespace App\Policies;

use App\Models\Priority;
use App\Models\User;

class PriorityPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->isSoloHandyman() || $user->isCompanyAdmin();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Priority $priority): bool
    {
        // Solo handymen can only view their own priorities
        if ($user->isSoloHandyman()) {
            return $priority->user_id === $user->id;
        }

        // Company admins can only view their company's priorities
        if ($user->isCompanyAdmin()) {
            return $priority->company_id === $user->company_id;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->isSoloHandyman() || $user->isCompanyAdmin();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Priority $priority): bool
    {
        // Solo handymen can only update their own priorities
        if ($user->isSoloHandyman()) {
            return $priority->user_id === $user->id;
        }

        // Company admins can only update their company's priorities
        if ($user->isCompanyAdmin()) {
            return $priority->company_id === $user->company_id;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Priority $priority): bool
    {
        // Don't allow deletion of default priorities if they're being used
        if ($priority->is_default && $priority->discoveries()->count() > 0) {
            return false;
        }

        // Solo handymen can only delete their own priorities
        if ($user->isSoloHandyman()) {
            return $priority->user_id === $user->id;
        }

        // Company admins can only delete their company's priorities
        if ($user->isCompanyAdmin()) {
            return $priority->company_id === $user->company_id;
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Priority $priority): bool
    {
        return $this->update($user, $priority);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Priority $priority): bool
    {
        return $this->delete($user, $priority);
    }
}
