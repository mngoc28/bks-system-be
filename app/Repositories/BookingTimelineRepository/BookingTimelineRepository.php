<?php

declare(strict_types=1);

namespace App\Repositories\BookingTimelineRepository;

use App\Models\BookingTimelineEvent;
use App\Repositories\BaseRepository;
use Illuminate\Support\Collection;

/**
 * Class BookingTimelineRepository
 *
 * @package App\Repositories\BookingTimelineRepository
 */
final class BookingTimelineRepository extends BaseRepository implements BookingTimelineRepositoryInterface
{
    /**
     * @return string
     */
    public function getModel(): string
    {
        return BookingTimelineEvent::class;
    }

    /**
     * @inheritDoc
     */
    public function append(array $data): BookingTimelineEvent
    {
        /** @var BookingTimelineEvent $event */
        $event = $this->model->create($data);

        return $event;
    }

    /**
     * @inheritDoc
     */
    public function forBooking(int $bookingId): Collection
    {
        /** @var list<BookingTimelineEvent> $events */
        $events = $this->model
            ->where('booking_id', $bookingId)
            ->orderBy('created_at')
            ->orderBy('id')
            ->get()
            ->all();

        return new Collection($events);
    }
}
