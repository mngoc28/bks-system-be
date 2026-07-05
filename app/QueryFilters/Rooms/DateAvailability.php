<?php

declare(strict_types=1);

namespace App\QueryFilters\Rooms;

use App\Enums\BookingStatus;
use Closure;
use Illuminate\Database\Eloquent\Builder;

/**
 * Loại phòng có booking hoặc room block trùng khoảng [start_date, end_date).
 * end_date exclusive — khớp ConflictChecker.
 */
final class DateAvailability
{
    /**
     * @param Builder $query
     */
    public function handle($query, Closure $next)
    {
        if (! request()->filled('start_date') || ! request()->filled('end_date')) {
            return $next($query);
        }

        $startDate = (string) request()->input('start_date');
        $endDate = (string) request()->input('end_date');

        $query->whereDoesntHave('bookings', static function (Builder $bookingQuery) use ($startDate, $endDate): void {
            $bookingQuery
                ->whereNotIn('status', [
                    BookingStatus::CANCELLED->value,
                    BookingStatus::COMPLETED->value,
                ])
                ->where('stay_status', '!=', 'no_show')
                ->where('start_date', '<', $endDate)
                ->where('end_date', '>', $startDate);
        })->whereDoesntHave('roomBlocks', static function (Builder $blockQuery) use ($startDate, $endDate): void {
            $blockQuery
                ->where('start_date', '<', $endDate)
                ->where('end_date', '>', $startDate);
        });

        return $next($query);
    }
}
