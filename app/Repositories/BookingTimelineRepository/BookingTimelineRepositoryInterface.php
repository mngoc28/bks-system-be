<?php

declare(strict_types=1);

namespace App\Repositories\BookingTimelineRepository;

use App\Models\BookingTimelineEvent;
use App\Repositories\RepositoryInterface;
use Illuminate\Support\Collection;

/**
 * Interface BookingTimelineRepositoryInterface
 *
 * @package App\Repositories\BookingTimelineRepository
 */
interface BookingTimelineRepositoryInterface extends RepositoryInterface
{
    /**
     * Append a new timeline event for a booking.
     *
     * @param array<string, mixed> $data
     * @return BookingTimelineEvent
     */
    public function append(array $data): BookingTimelineEvent;

    /**
     * Retrieve all timeline events for a booking ordered chronologically.
     *
     * @param int $bookingId
     * @return Collection<int, BookingTimelineEvent>
     */
    public function forBooking(int $bookingId): Collection;
}
