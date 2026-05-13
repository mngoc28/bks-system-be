<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\BookingCancelled;
use App\Events\BookingConfirmed;
use App\Events\BookingCreated;
use App\Models\BookingTimelineEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Listener phụ trợ cho realtime audit.
 *
 * Quan trọng: Phase 1 đã ghi timeline event chính (status transition) một
 * cách đồng bộ trong cùng transaction với booking để đảm bảo consistency.
 * Listener này KHÔNG ghi trùng. Mục đích duy nhất: ghi một marker
 * `broadcast_dispatched` (hoặc `broadcast_failed` khi catch exception) phục
 * vụ debug realtime — biết được event đã rời khỏi backend, dù nó đến FE hay
 * không.
 *
 * Queue: `default` (không cần queue riêng cho audit nhẹ). Có ShouldQueue để
 * không block HTTP response.
 */
class RecordBookingTimeline implements ShouldQueue
{
    use InteractsWithQueue;

    public int $tries = 3;
    public int $backoff = 5;

    public function handleCreated(BookingCreated $event): void
    {
        $this->writeMarker(
            (int) $event->booking->id,
            'created',
            [
                'partner_id'  => $event->partnerId,
                'property_id' => $event->propertyId,
            ],
        );
    }

    public function handleConfirmed(BookingConfirmed $event): void
    {
        $this->writeMarker(
            (int) $event->booking->id,
            'confirmed',
            [
                'partner_id'  => $event->partnerId,
                'property_id' => $event->propertyId,
                'actor_id'    => $event->actorId,
            ],
        );
    }

    public function handleCancelled(BookingCancelled $event): void
    {
        $this->writeMarker(
            (int) $event->booking->id,
            'cancelled',
            [
                'partner_id'  => $event->partnerId,
                'property_id' => $event->propertyId,
                'actor_id'    => $event->actorId,
            ],
        );
    }

    /**
     * @param array<string, mixed> $metadata
     */
    private function writeMarker(int $bookingId, string $sourceEvent, array $metadata): void
    {
        try {
            BookingTimelineEvent::create([
                'booking_id' => $bookingId,
                'actor_id'   => null,
                'event_type' => 'broadcast_dispatched',
                'note'       => sprintf('source=%s', $sourceEvent),
                'metadata'   => $metadata,
            ]);
        } catch (Throwable $e) {
            // Không re-throw — listener chỉ là audit phụ trợ.
            Log::warning('RecordBookingTimeline marker failed', [
                'booking_id' => $bookingId,
                'source'     => $sourceEvent,
                'error'      => $e->getMessage(),
            ]);
        }
    }
}
