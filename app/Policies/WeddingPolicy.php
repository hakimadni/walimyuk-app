<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Wedding;

class WeddingPolicy
{
    private function isAdmin(User $user): bool
    {
        return in_array($user->role, ['super_admin', 'admin'], true);
    }

    /**
     * Determine whether the user can view any weddings.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the wedding.
     */
    public function view(User $user, Wedding $wedding): bool
    {
        return $this->isAdmin($user) || $user->id === $wedding->user_id;
    }

    /**
     * Determine whether the user can create weddings.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the wedding.
     */
    public function update(User $user, Wedding $wedding): bool
    {
        return $this->isAdmin($user) || $user->id === $wedding->user_id;
    }

    /**
     * Determine whether the user can delete the wedding.
     */
    public function delete(User $user, Wedding $wedding): bool
    {
        return $this->isAdmin($user) || $user->id === $wedding->user_id;
    }

    /**
     * Determine whether the user can restore the wedding.
     */
    public function restore(User $user, Wedding $wedding): bool
    {
        return $this->isAdmin($user) || $user->id === $wedding->user_id;
    }

    /**
     * Determine whether the user can permanently delete the wedding.
     */
    public function forceDelete(User $user, Wedding $wedding): bool
    {
        return $this->isAdmin($user) || $user->id === $wedding->user_id;
    }
}
