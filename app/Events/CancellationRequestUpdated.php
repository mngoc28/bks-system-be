<?php

declare(strict_types=1);

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Partner/guest cancellation-request lifecycle (pending → approved/rejected).
 * Payload intentionally excludes guest PII (no name/email/phone/reason_text).
 */
final class CancellationRequestUpdated implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public int $requestId,
        public int $bookingId,
        public int $propertyId,
        public int $partnerId,
        public string $status,
    ) {
    }

    /**
     * @return array<int, PrivateChannel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('partner.' . $this->partnerId),
            new PrivateChannel('property.' . $this->propertyId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'cancellation_request.updated';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'request_id'  => $this->requestId,
            'booking_id'  => $this->bookingId,
            'property_id' => $this->propertyId,
            'partner_id'  => $this->partnerId,
            'status'      => $this->status,
        ];
    }
}
