<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Building;

class BuildingPolicy
{
    /**
     * Determine whether the user can view any buildings.
     */
    public function viewAny(User $user)
    {
        return $user->role === 'admin' || $user->role === 'staff';
    }

    /**
     * Determine whether the user can view the building.
     */
    public function view(User $user, Building $building)
    {
        if ($user->role === 'admin') {
            return true;
        }

        if ($user->role === 'staff') {
            // Assuming staff has a relation 'buildings' they are responsible for
            return $user->buildings->contains($building->id);
        }

        return false;
    }

    /**
     * Determine whether the user can create buildings.
     */
    public function create(User $user)
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can update the building.
     */
    public function update(User $user, Building $building)
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can delete the building.
     */
    public function delete(User $user, Building $building)
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can assign staff to the building.
     */
    public function assignStaff(User $user, Building $building)
    {
        return $user->role === 'admin';
    }
}
