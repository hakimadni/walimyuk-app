<?php

namespace App\Policies;

use App\Models\Rsvp;
use App\Models\User;

class RsvpPolicy
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
    public function view(User $user, Rsvp $rsvp): bool
    {
        return $user->can('view', $rsvp->wedding);
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
    public function update(User $user, Rsvp $rsvp): bool
    {
        return $user->can('update', $rsvp->wedding);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Rsvp $rsvp): bool
    {
        return $user->can('delete', $rsvp->wedding);
    }
}
