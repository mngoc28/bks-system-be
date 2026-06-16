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

        $room->loadMissing('property.propertyType');
        $propertySlug = $room->property?->propertyType?->slug;
        $priceUnit = (string) ($roomPrice?->unit ?? 'night');
        $stayNights = StayClassificationService::countStayNights($startDate, $endDate);
        $isLongTerm = StayClassificationService::isLongTermLeaseBooking(
            $propertySlug,
            $stayNights,
            $priceUnit,
        );

        // Check if last-minute (< 24h from now to checkin time)
        $checkInDateTime = Carbon::parse($startDate)->setTime(14, 0, 0);
        $isLastMinute = !$isLongTerm && (Carbon::now()->diffInHours($checkInDateTime, false) <= 24);

        // Short term (daily): check if any date falls on weekend (Sat/Sun) or high season/holiday.
        // High season/holiday is indicated if there's a markup PriceRule active for that date.
        $hasWeekendOrHoliday = false;

        if (!$isLongTerm) {
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
        }

        $isRequired = $isLongTerm || $isLastMinute || $hasWeekendOrHoliday;

        if ($isRequired) {
            $roomStayTotal = BookingStayAmountCalculator::computeRoomStayTotal(
                $startDate,
                $endDate,
                (float) ($roomPrice ? $roomPrice->price : 0.0),
                (string) ($roomPrice ? $roomPrice->unit : 'night')
            );

            $amount = 0.0;
            if ($roomPrice && $roomPrice->deposit_amount !== null && (float) $roomPrice->deposit_amount > 0) {
                $amount = (float) $roomPrice->deposit_amount;
            } elseif ($room->deposit !== null && (float) $room->deposit > 0) {
                $amount = (float) $room->deposit;
            } else {
                if ($isLastMinute) {
                    $amount = $roomStayTotal;
                } else {
                    $amount = round($roomStayTotal * 0.5, 2);
                }
            }

            // Force minimums if required but amount is 0 or negative
            if ($amount <= 0.0) {
                if ($isLastMinute) {
                    $amount = $roomStayTotal;
                } else {
                    $amount = round($roomStayTotal * 0.5, 2);
                }
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
