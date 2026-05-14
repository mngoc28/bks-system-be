<?php

declare(strict_types=1);

namespace App\Rules;

use App\Models\Property;
use Illuminate\Contracts\Validation\Rule;

/**
 * Validates if a property can be deleted (no rooms / no bookings on rooms).
 */
final class CanDeletePropertyRule implements Rule
{
    private ?string $errorMessage = null;

    /**
     * @param string $attribute
     * @param mixed  $value
     */
    public function passes($attribute, $value): bool
    {
        $property = Property::with('rooms.bookings')->find($value);

        if (! $property) {
            return true;
        }

        if ($property->rooms->isNotEmpty()) {
            $this->errorMessage = __('property.validation.id.has_rooms');

            return false;
        }

        $hasBookings = $property->rooms->contains(function ($room) {
            return $room->bookings->isNotEmpty();
        });

        if ($hasBookings) {
            $this->errorMessage = __('property.validation.id.has_bookings');

            return false;
        }

        return true;
    }

    public function message(): string
    {
        return $this->errorMessage ?? __('property.validation.id.exists');
    }
}
