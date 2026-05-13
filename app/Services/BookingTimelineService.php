<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\BookingTimelineEvent;
use App\Repositories\BookingTimelineRepository\BookingTimelineRepositoryInterface;
use Illuminate\Support\Facades\Auth;

/**
 * Records lifecycle events for bookings into an append-only audit timeline.
 *
 * Each method produces a single row in `booking_timeline_events`. Methods are
 * intentionally narrow so that callers (services or queued listeners) cannot
 * accidentally write a malformed event_type. Actor defaults to the
 * authenticated user but can be set to null for system-generated events such
 * as backfill or scheduler runs.
 */
final class BookingTimelineService
{
    public const EVENT_CREATED            = 'created';
    public const EVENT_CONFIRMED          = 'confirmed';
    public const EVENT_CANCELLED          = 'cancelled';
    public const EVENT_CHECKED_IN         = 'checked_in';
    public const EVENT_CHECKED_OUT        = 'checked_out';
    public const EVENT_NO_SHOW            = 'no_show';
    public const EVENT_CONFLICT_DETECTED  = 'conflict_detected';
    public const EVENT_BACKFILLED         = 'backfilled';

    public const STATUS_PENDING   = 'pending';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_COMPLETED = 'completed';

    public function __construct(
        private readonly BookingTimelineRepositoryInterface $timelineRepository,
    ) {
    }

    /**
     * Booking created (typically when an end user submits a booking).
     *
     * @param array<string, mixed> $metadata
     */
    public function recordCreated(int $bookingId, ?int $actorId = null, array $metadata = []): BookingTimelineEvent
    {
        return $this->append(
            $bookingId,
            self::EVENT_CREATED,
            null,
            self::STATUS_PENDING,
            null,
            $metadata,
            $actorId,
        );
    }

    /**
     * Partner confirmed a pending booking.
     *
     * @param array<string, mixed> $metadata
     */
    public function recordConfirmed(int $bookingId, ?int $actorId = null, array $metadata = []): BookingTimelineEvent
    {
        return $this->append(
            $bookingId,
            self::EVENT_CONFIRMED,
            self::STATUS_PENDING,
            self::STATUS_CONFIRMED,
            null,
            $metadata,
            $actorId,
        );
    }

    /**
     * Partner cancelled the booking. Reason is required and stored in `note`.
     *
     * @param array<string, mixed> $metadata
     */
    public function recordCancelled(
        int $bookingId,
        string $reason,
        ?string $fromStatus = null,
        ?int $actorId = null,
        array $metadata = [],
    ): BookingTimelineEvent {
        return $this->append(
            $bookingId,
            self::EVENT_CANCELLED,
            $fromStatus,
            self::STATUS_CANCELLED,
            $reason,
            $metadata,
            $actorId,
        );
    }

    /**
     * @param array<string, mixed> $metadata
     */
    public function recordCheckedIn(int $bookingId, ?int $actorId = null, array $metadata = []): BookingTimelineEvent
    {
        return $this->append(
            $bookingId,
            self::EVENT_CHECKED_IN,
            self::STATUS_CONFIRMED,
            self::STATUS_CONFIRMED,
            null,
            $metadata,
            $actorId,
        );
    }

    /**
     * @param array<string, mixed> $metadata
     */
    public function recordCheckedOut(int $bookingId, ?int $actorId = null, array $metadata = []): BookingTimelineEvent
    {
        return $this->append(
            $bookingId,
            self::EVENT_CHECKED_OUT,
            self::STATUS_CONFIRMED,
            self::STATUS_COMPLETED,
            null,
            $metadata,
            $actorId,
        );
    }

    /**
     * @param array<string, mixed> $metadata
     */
    public function recordNoShow(int $bookingId, ?int $actorId = null, array $metadata = []): BookingTimelineEvent
    {
        return $this->append(
            $bookingId,
            self::EVENT_NO_SHOW,
            self::STATUS_CONFIRMED,
            self::STATUS_CONFIRMED,
            null,
            $metadata,
            $actorId,
        );
    }

    /**
     * Records that a conflict was detected during a confirm/move attempt.
     *
     * @param array<string, mixed> $metadata Should include conflicting booking/block ids
     */
    public function recordConflictDetected(
        int $bookingId,
        ?int $actorId = null,
        array $metadata = [],
    ): BookingTimelineEvent {
        return $this->append(
            $bookingId,
            self::EVENT_CONFLICT_DETECTED,
            null,
            null,
            null,
            $metadata,
            $actorId,
        );
    }

    /**
     * Marker event for backfilled bookings so KPI calculations can exclude them.
     *
     * @param array<string, mixed> $metadata
     */
    public function recordBackfilled(int $bookingId, array $metadata = []): BookingTimelineEvent
    {
        return $this->append(
            $bookingId,
            self::EVENT_BACKFILLED,
            null,
            self::STATUS_CONFIRMED,
            null,
            array_merge(['backfilled' => true], $metadata),
            null,
        );
    }

    /**
     * Resolve the actor id, falling back to the authenticated user when not
     * provided explicitly.
     */
    private function resolveActor(?int $actorId): ?int
    {
        if ($actorId !== null) {
            return $actorId;
        }

        $authId = Auth::id();

        return $authId !== null ? (int) $authId : null;
    }

    /**
     * Centralised append so every event writes through the same payload shape.
     *
     * @param array<string, mixed> $metadata
     */
    private function append(
        int $bookingId,
        string $eventType,
        ?string $fromStatus,
        ?string $toStatus,
        ?string $note,
        array $metadata,
        ?int $actorId,
    ): BookingTimelineEvent {
        return $this->timelineRepository->append([
            'booking_id'  => $bookingId,
            'actor_id'    => $this->resolveActor($actorId),
            'event_type'  => $eventType,
            'from_status' => $fromStatus,
            'to_status'   => $toStatus,
            'note'        => $note,
            'metadata'    => $metadata !== [] ? $metadata : null,
        ]);
    }
}
