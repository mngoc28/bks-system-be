<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Contract;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Authorization rules for partner contract lifecycle endpoints (renewal
 * reminder, termination, expiring-soon listing).
 *
 * Ownership flows through `contract.booking.room.property.user_id`. Admin is
 * bypassed via `before()` mirroring the existing BookingPolicy/RoomBlockPolicy
 * style.
 */
final class ContractPolicy
{
    use HandlesAuthorization;

    public function before(User $user, string $ability): ?bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        return null;
    }

    /**
     * Partner can view a contract belonging to their property.
     */
    public function view(User $user, Contract $contract): bool
    {
        return $this->isOwner($user, $contract);
    }

    /**
     * Partner can set or clear the renewal reminder slot on their own
     * long-term contract.
     */
    public function manageRenewal(User $user, Contract $contract): bool
    {
        return $this->isOwner($user, $contract);
    }

    /**
     * Partner can terminate an active contract they own.
     */
    public function terminate(User $user, Contract $contract): bool
    {
        return $this->isOwner($user, $contract);
    }

    private function isOwner(User $user, Contract $contract): bool
    {
        if ($user->role !== 'partner') {
            return false;
        }

        $booking = $contract->relationLoaded('booking') ? $contract->booking : $contract->booking()->first();
        $room = $booking?->relationLoaded('room') ? $booking->room : $booking?->room()->first();
        $property = $room?->relationLoaded('property') ? $room->property : $room?->property()->first();
        if ($property === null) {
            return false;
        }

        return (int) $property->user_id === (int) $user->id;
    }
}
