<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     * @param \App\Models\User $actor
     * @return bool
     */
    public function index(User $actor): bool
    {
        // Admin and Partner either can view user list
        return in_array($actor->role, ['admin', 'partner']);
    }

    /**
     * Determine whether the user can create models.
     * @param \App\Models\User $actor
     * @return bool
     */
    public function store(User $actor): bool
    {
        // Only admin can create user
        return $actor->role === 'admin';
    }

    /**
     * For admin to update other users
     * @param \App\Models\User $actor
     * @param \App\Models\User $target
     * @return bool
     *
     */
    public function update(User $actor, User $target): bool
    {
        // Only admin can update user
        return $actor->role === 'admin';
    }

    /**
     * Determine whether the user can delete the model.
     * @param \App\Models\User $actor
     * @param \App\Models\User $target
     * @return bool
     */
    public function destroy(User $actor, User $target): bool
    {
        // Only admin can delete user (cannot delete themselves)
        if ($actor->role === 'admin' && $actor->id === $target->id) {
            return false;
        }
        return $actor->role === 'admin';
    }

    /**
     * Determine whether the user can view the model.
     * @param \App\Models\User $actor
     * @param \App\Models\User $target
     * @return bool
     */
    public function view(User $actor, User $target): bool
    {
        // Admin and partner can view, or user can view themselves
        return in_array($actor->role, ['admin', 'partner'])
            || $actor->id === $target->id;
    }

    /**
     * Determine whether the user can update the model.
     * @param \App\Models\User $actor
     * @param \App\Models\User $target
     * @return bool
     * scalable
     */
    public function updateProfile(User $actor, User $target): bool
    {
        if ($actor->role === 'admin') {
            return true;
        }
        return $actor->id === $target->id;
    }

    /**
     * Determine whether the user can change the password.
     * @param \App\Models\User $actor
     * @param \App\Models\User $target
     * @return bool
     */
    public function changePassword(User $actor, User $target): bool
    {
        return $actor->role === 'admin' || $actor->id === $target->id;
    }

    /**
     * Determine whether the admin can reset password (without current password).
     * @param \App\Models\User $actor
     * @param \App\Models\User $target
     * @return bool
     */
    public function resetPassword(User $actor, User $target): bool
    {
        return $actor->role === 'admin';
    }
}
