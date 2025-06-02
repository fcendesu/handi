<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WorkGroup;

class WorkGroupPolicy
{
    /**
     * Determine whether the user can view any work groups.
     */
    public function viewAny(User $user): bool
    {
        // All authenticated users can view work groups (scoped in controller)
        return true;
    }

    /**
     * Determine whether the user can view the work group.
     */
    public function view(User $user, WorkGroup $workGroup): bool
    {
        // Solo handyman can view their own work groups
        if ($user->isSoloHandyman()) {
            return $workGroup->creator_id === $user->id;
        }

        // Company admin can view company work groups
        if ($user->isCompanyAdmin()) {
            return $workGroup->company_id === $user->company_id;
        }

        // Company employee can view work groups they belong to
        if ($user->isCompanyEmployee()) {
            return $user->workGroups()->where('work_groups.id', $workGroup->id)->exists();
        }

        return false;
    }

    /**
     * Determine whether the user can create work groups.
     */
    public function create(User $user): bool
    {
        // Only solo handymen and company admins can create work groups
        return $user->isSoloHandyman() || $user->isCompanyAdmin();
    }

    /**
     * Determine whether the user can update the work group.
     */
    public function update(User $user, WorkGroup $workGroup): bool
    {
        // Solo handyman can update their own work groups
        if ($user->isSoloHandyman()) {
            return $workGroup->creator_id === $user->id;
        }

        // Company admin can update company work groups
        if ($user->isCompanyAdmin()) {
            return $workGroup->company_id === $user->company_id;
        }

        // Company employees cannot update work groups
        return false;
    }

    /**
     * Determine whether the user can delete the work group.
     */
    public function delete(User $user, WorkGroup $workGroup): bool
    {
        // Solo handyman can delete their own work groups
        if ($user->isSoloHandyman()) {
            return $workGroup->creator_id === $user->id;
        }

        // Company admin can delete company work groups
        if ($user->isCompanyAdmin()) {
            return $workGroup->company_id === $user->company_id;
        }

        // Company employees cannot delete work groups
        return false;
    }

    /**
     * Determine whether the user can restore the work group.
     */
    public function restore(User $user, WorkGroup $workGroup): bool
    {
        return $this->delete($user, $workGroup);
    }

    /**
     * Determine whether the user can permanently delete the work group.
     */
    public function forceDelete(User $user, WorkGroup $workGroup): bool
    {
        return $this->delete($user, $workGroup);
    }
}
