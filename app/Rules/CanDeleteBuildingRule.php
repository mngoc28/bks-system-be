<?php

declare(strict_types=1);

namespace App\Rules;

use App\Models\Building;
use Illuminate\Contracts\Validation\Rule;

/**
 * Can Delete Building Rule
 *
 * Validates if a building can be deleted by checking:
 * - Building has no rooms
 * - Building has no bookings
 */
final class CanDeleteBuildingRule implements Rule
{
    private ?string $errorMessage = null;

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        $building = Building::with('rooms.bookings')->find($value);

        if (! $building) {
            return true; // Let exists rule handle this case
        }

        // Check if building has rooms
        if ($building->rooms->isNotEmpty()) {
            $this->errorMessage = __('building.validation.id.has_rooms');
            return false;
        }

        // Check if any room has bookings
        $hasBookings = $building->rooms->contains(function ($room) {
            return $room->bookings->isNotEmpty();
        });

        if ($hasBookings) {
            $this->errorMessage = __('building.validation.id.has_bookings');
            return false;
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return $this->errorMessage ?? __('building.validation.id.exists');
    }
}
