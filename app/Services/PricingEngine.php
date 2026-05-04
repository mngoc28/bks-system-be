<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\PriceRule;
use App\Models\Room;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

final class PricingEngine
{
    /**
     * Calculate the total price for a stay period considering dynamic price rules.
     *
     * @param Room $room
     * @param string $startDate
     * @param string $endDate
     * @param float $basePrice
     * @return array{total_amount: float, breakdown: array}
     */
    public function calculateStayPrice(Room $room, string $startDate, string $endDate, float $basePrice): array
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        $period = CarbonPeriod::create($start, $end);

        $totalAmount = 0;
        $breakdown = [];

        // Fetch active rules for this room or its building
        $rules = PriceRule::where(function ($query) use ($room) {
                $query->where('room_id', $room->id)
                      ->orWhere(function ($q) use ($room) {
                          $q->where('building_id', $room->building_id)
                            ->whereNull('room_id');
                      });
        })
            ->where('is_active', true)
            ->where(function ($query) use ($startDate, $endDate) {
                $query->where('start_date', '<=', $endDate)
                      ->where('end_date', '>=', $startDate);
            })
            ->get();

        foreach ($period as $date) {
            $dayPrice = $basePrice;
            $appliedRule = null;

            // Find the most specific rule for this day
            // Priority: Room specific > Building specific, then latest created
            $matchedRule = $rules->filter(function ($rule) use ($date) {
                $isInDateRange = $date->between($rule->start_date, $rule->end_date);
                if (!$isInDateRange) {
                    return false;
                }

                if ($rule->days_of_week) {
                    return in_array($date->dayOfWeek, $rule->days_of_week);
                }

                return true;
            })
            ->sortByDesc(function ($rule) {
                return ($rule->room_id ? 100 : 0) + $rule->id;
            })
            ->first();

            if ($matchedRule) {
                if ($matchedRule->type === 'markup') {
                    if ($matchedRule->value_type === 'percentage') {
                        $dayPrice += ($basePrice * ($matchedRule->value / 100));
                    } else {
                        $dayPrice += $matchedRule->value;
                    }
                } else { // discount
                    if ($matchedRule->value_type === 'percentage') {
                        $dayPrice -= ($basePrice * ($matchedRule->value / 100));
                    } else {
                        $dayPrice -= $matchedRule->value;
                    }
                }
                $appliedRule = $matchedRule->name;
            }

            $totalAmount += $dayPrice;
            $breakdown[] = [
                'date'         => $date->format('Y-m-d'),
                'base_price'   => $basePrice,
                'final_price'  => $dayPrice,
                'applied_rule' => $appliedRule,
            ];
        }

        return [
            'total_amount' => round($totalAmount, 2),
            'breakdown'    => $breakdown,
        ];
    }
}
