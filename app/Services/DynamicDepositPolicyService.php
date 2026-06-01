<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Booking;
use App\Models\Room;
use App\Models\RoomPrice;
use App\Models\PriceRule;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

final class DynamicDepositPolicyService
{
    /**
     * Determine if deposit is required and compute the required amount.
     *
     * @param Room $room
     * @param RoomPrice|null $roomPrice
     * @param string $startDate
     * @param string $endDate
     * @return array{required: bool, amount: float}
     */
    public function calculateRequiredDeposit(
        Room $room,
        ?RoomPrice $roomPrice,
        string $startDate,
        string $endDate
    ): array {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        $period = CarbonPeriod::create($start, $end);

        // Determine if long term
        $isLongTerm = false;
        if ($roomPrice) {
            $isLongTerm = $roomPrice->unit === 'month';
        }

        // Long term always requires a deposit
        if ($isLongTerm) {
            $amount = 0.0;
            if ($roomPrice && $roomPrice->deposit_amount !== null) {
                $amount = (float) $roomPrice->deposit_amount;
            } elseif ($room->deposit !== null) {
                $amount = (float) $room->deposit;
            }
            return [
                'required' => true,
                'amount' => $amount,
            ];
        }

        // Short term (daily): check if any date falls on weekend (Sat/Sun) or high season/holiday.
        // High season/holiday is indicated if there's a markup PriceRule active for that date.
        $hasWeekendOrHoliday = false;

        // Fetch active markup price rules
        $markupRules = PriceRule::where(function ($query) use ($room) {
            $query->where('room_id', $room->id)
                  ->orWhere(function ($q) use ($room) {
                      $q->where('property_id', $room->property_id)
                        ->whereNull('room_id');
                  });
        })
        ->where('is_active', true)
        ->where('type', 'markup')
        ->where(function ($query) use ($startDate, $endDate) {
            $query->where('start_date', '<=', $endDate)
                  ->where('end_date', '>=', $startDate);
        })
        ->get();

        foreach ($period as $date) {
            // Check weekend (0 = Sunday, 6 = Saturday)
            if (in_array($date->dayOfWeek, [0, 6], true)) {
                $hasWeekendOrHoliday = true;
                break;
            }

            // Check if matches a markup rule
            $hasMarkup = $markupRules->first(function ($rule) use ($date) {
                $isInDateRange = $date->between($rule->start_date, $rule->end_date);
                if (!$isInDateRange) {
                    return false;
                }
                if ($rule->days_of_week) {
                    return in_array($date->dayOfWeek, $rule->days_of_week);
                }
                return true;
            });

            if ($hasMarkup) {
                $hasWeekendOrHoliday = true;
                break;
            }
        }

        if ($hasWeekendOrHoliday) {
            $amount = 0.0;
            if ($roomPrice && $roomPrice->deposit_amount !== null) {
                $amount = (float) $roomPrice->deposit_amount;
            } elseif ($room->deposit !== null) {
                $amount = (float) $room->deposit;
            } else {
                // Default fallback: 50% of base price if no deposit amount specified
                $basePrice = $roomPrice ? (float) $roomPrice->price : 0.0;
                $amount = $basePrice * 0.5;
            }

            return [
                'required' => true,
                'amount' => $amount,
            ];
        }

        // Weekdays / Low season -> no deposit required
        return [
            'required' => false,
            'amount' => 0.0,
        ];
    }
}
