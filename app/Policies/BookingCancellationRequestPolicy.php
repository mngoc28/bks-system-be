<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\BookingCancellationRequest;
use App\Models\User;

final class BookingCancellationRequestPolicy
{
    /**
     * Partner owns the property on the booking linked to this cancellation request.
     */
    public function view(User $user, BookingCancellationRequest $request): bool
    {
        if ($user->role !== 'partner') {
            return false;
        }

        $request->loadMissing('booking.room.property');
        $property = $request->booking?->room?->property;

        if ($property === null) {
            return false;
        }

        return (int) $property->user_id === (int) $user->id;
    }
}
