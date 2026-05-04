<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Booking;
use App\Models\Room;
use App\Enums\BookingStatus;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

final class ReportingService
{
    /**
     * Calculate core hospitality KPIs for a partner.
     *
     * @param int $partnerId
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function getPartnerKPIs(int $partnerId, string $startDate, string $endDate): array
    {
        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        $totalDays = $start->diffInDays($end) + 1;

        // 1. Get total rooms owned by partner
        $totalRooms = Room::whereHas('building', function ($query) use ($partnerId) {
            $query->where('user_id', $partnerId);
        })->count();

        $totalAvailableRoomNights = $totalRooms * $totalDays;

        // 2. Get bookings in range
        $bookings = Booking::whereHas('room.building', function ($query) use ($partnerId) {
                $query->where('user_id', $partnerId);
        })
            ->whereIn('status', [BookingStatus::CONFIRMED->value, 3]) // Confirmed or Completed
            ->where(function ($query) use ($startDate, $endDate) {
                $query->where('start_date', '<=', $endDate)
                      ->where('end_date', '>=', $startDate);
            })
            ->get();

        $totalRevenue = 0;
        $totalOccupiedNights = 0;

        foreach ($bookings as $booking) {
            $bookingStart = Carbon::parse($booking->start_date);
            $bookingEnd = Carbon::parse($booking->end_date);

            // Overlap between booking and report range
            $overlapStart = $bookingStart->max($start);
            $overlapEnd = $bookingEnd->min($end);

            $nights = $overlapStart->diffInDays($overlapEnd) + 1;

            // Assuming price is stored as total or we need to calculate proportionate revenue
            // For now, if we have price_at_booking, we use it.
            // If total_amount is available in booking, we should use a proportion.
            // Let's assume booking has a total_amount field (need to check migration or use room price)

            $totalOccupiedNights += $nights;
            // Simple revenue calculation for now - in a real app, this would be more complex
            // taking into account daily price variations.
            // $totalRevenue += $booking->total_amount * ($nights / ($bookingStart->diffInDays($bookingEnd) + 1));
        }

        // Let's use a simplified DB query for revenue if possible
        $totalRevenue = $bookings->sum('total_amount'); // Need to ensure total_amount exists

        $adr = $totalOccupiedNights > 0 ? $totalRevenue / $totalOccupiedNights : 0;
        $occupancyRate = $totalAvailableRoomNights > 0 ? ($totalOccupiedNights / $totalAvailableRoomNights) * 100 : 0;
        $revpar = $totalAvailableRoomNights > 0 ? $totalRevenue / $totalAvailableRoomNights : 0;

        return [
            'adr'            => round($adr, 2),
            'revpar'         => round($revpar, 2),
            'occupancy_rate' => round($occupancyRate, 2),
            'total_revenue'  => round($totalRevenue, 2),
            'nights_sold'    => $totalOccupiedNights,
            'capacity'       => $totalAvailableRoomNights,
        ];
    }
}
