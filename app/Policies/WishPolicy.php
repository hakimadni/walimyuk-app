<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Wish;

class WishPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Wish $wish): bool
    {
        return $user->can('view', $wish->wedding);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Wish $wish): bool
    {
        return $user->can('update', $wish->wedding);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Wish $wish): bool
    {
        return $user->can('delete', $wish->wedding);
    }
}
