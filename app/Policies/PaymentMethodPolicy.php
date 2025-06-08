<?php

namespace App\Policies;

use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PaymentMethodPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        // Solo handymen, company employees and admins can view their payment methods
        return $user->isSoloHandyman() || $user->isCompanyEmployee() || $user->isCompanyAdmin();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, PaymentMethod $paymentMethod): bool
    {
        // Solo handymen can view their own payment methods, company users can view company payment methods
        if ($user->isSoloHandyman()) {
            return $paymentMethod->user_id === $user->id;
        }

        return $user->company_id === $paymentMethod->company_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // Solo handymen, company employees and admins can create payment methods
        return $user->isSoloHandyman() || $user->isCompanyEmployee() || $user->isCompanyAdmin();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, PaymentMethod $paymentMethod): bool
    {
        // Solo handymen can update their own payment methods, company users can update company payment methods
        if ($user->isSoloHandyman()) {
            return $paymentMethod->user_id === $user->id;
        }

        return $user->company_id === $paymentMethod->company_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, PaymentMethod $paymentMethod): bool
    {
        // Solo handymen can delete their own payment methods, company users can delete company payment methods
        if ($user->isSoloHandyman()) {
            return $paymentMethod->user_id === $user->id;
        }

        return $user->company_id === $paymentMethod->company_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, PaymentMethod $paymentMethod): bool
    {
        // Solo handymen can restore their own payment methods, company users can restore company payment methods
        if ($user->isSoloHandyman()) {
            return $paymentMethod->user_id === $user->id;
        }

        return $user->company_id === $paymentMethod->company_id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, PaymentMethod $paymentMethod): bool
    {
        // Only company admins and solo handymen can permanently delete payment methods
        if ($user->isSoloHandyman()) {
            return $paymentMethod->user_id === $user->id;
        }

        return $user->isCompanyAdmin() && $user->company_id === $paymentMethod->company_id;
    }
}
