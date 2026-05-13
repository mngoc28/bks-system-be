<?php

declare(strict_types=1);

namespace App\Policies;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Authorization rules for partner operations on bookings.
 *
 * Ownership is resolved through `bookings.room.building.user_id`. Admin keeps
 * unrestricted access to support audit at the partner level.
 */
final class BookingPolicy
{
    use HandlesAuthorization;

    /**
     * Allow admin to bypass all checks.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        return null;
    }

    /**
     * Partner may view bookings of rooms they own.
     */
    public function view(User $user, Booking $booking): bool
    {
        return $this->isOwnerOf($user, $booking);
    }

    /**
     * Partner may confirm only pending bookings of rooms they own.
     */
    public function confirm(User $user, Booking $booking): bool
    {
        if (! $this->isOwnerOf($user, $booking)) {
            return false;
        }

        return (int) $booking->status === BookingStatus::PENDING->value;
    }

    /**
     * Partner may cancel pending or confirmed bookings of rooms they own.
     */
    public function cancel(User $user, Booking $booking): bool
    {
        if (! $this->isOwnerOf($user, $booking)) {
            return false;
        }

        return in_array(
            (int) $booking->status,
            [BookingStatus::PENDING->value, BookingStatus::CONFIRMED->value],
            true,
        );
    }

    /**
     * Partner may mark no-show only on confirmed bookings of rooms they own.
     */
    public function noShow(User $user, Booking $booking): bool
    {
        if (! $this->isOwnerOf($user, $booking)) {
            return false;
        }

        return (int) $booking->status === BookingStatus::CONFIRMED->value;
    }

    /**
     * Partner may update bookings of rooms they own (e.g. drag-drop in calendar
     * which arrives in later phases). Granular state checks live in the
     * service layer.
     */
    public function update(User $user, Booking $booking): bool
    {
        return $this->isOwnerOf($user, $booking);
    }

    /**
     * Walks the relation chain to determine ownership without N+1 surprises.
     */
    private function isOwnerOf(User $user, Booking $booking): bool
    {
        if ($user->role !== 'partner') {
            return false;
        }

        $room = $booking->relationLoaded('room') ? $booking->room : $booking->room()->first();
        if ($room === null) {
            return false;
        }

        $building = $room->relationLoaded('building') ? $room->building : $room->building()->first();
        if ($building === null) {
            return false;
        }

        return (int) $building->user_id === (int) $user->id;
    }
}
