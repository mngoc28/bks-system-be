<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\CancellationRequestUpdated;
use App\Models\BookingTimelineEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Lightweight audit marker: request broadcast left the backend (debug parity với
 * {@see RecordBookingTimeline} cho booking lifecycle).
 */
final class RecordCancellationRequestBroadcastMarker implements ShouldQueue
{
    use InteractsWithQueue;

    public int $tries = 3;

    public int $backoff = 5;

    public function handle(CancellationRequestUpdated $event): void
    {
        try {
            BookingTimelineEvent::query()->create([
                'booking_id'  => $event->bookingId,
                'actor_id'    => null,
                'event_type'  => 'broadcast_dispatched',
                'from_status' => null,
                'to_status'   => null,
                'note'        => 'source=cancellation_request.updated',
                'metadata'    => [
                    'request_id'  => $event->requestId,
                    'status'      => $event->status,
                    'partner_id'  => $event->partnerId,
                    'property_id' => $event->propertyId,
                ],
            ]);
        } catch (Throwable $e) {
            Log::warning('RecordCancellationRequestBroadcastMarker failed', [
                'booking_id' => $event->bookingId,
                'request_id' => $event->requestId,
                'error'      => $e->getMessage(),
            ]);
        }
    }
}
