<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Property;
use App\Models\User;

final class PropertyPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === 'admin' || $user->role === 'staff';
    }

    public function view(User $user, Property $property): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        if ($user->role === 'staff') {
            return method_exists($user, 'properties') && $user->properties->contains($property->id);
        }

        return false;
    }

    public function create(User $user): bool
    {
        return $user->role === 'admin';
    }

    public function update(User $user, Property $property): bool
    {
        return $user->role === 'admin';
    }

    public function delete(User $user, Property $property): bool
    {
        return $user->role === 'admin';
    }

    public function assignStaff(User $user, Property $property): bool
    {
        return $user->role === 'admin';
    }
}
