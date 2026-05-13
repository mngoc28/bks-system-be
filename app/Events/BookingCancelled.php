<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Booking;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BookingCancelled implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public Booking $booking;
    public int $partnerId;
    public int $propertyId;
    public ?int $actorId;
    public ?string $reason;

    public function __construct(
        Booking $booking,
        int $partnerId,
        int $propertyId,
        ?int $actorId = null,
        ?string $reason = null,
    ) {
        $this->booking    = $booking->withoutRelations();
        $this->partnerId  = $partnerId;
        $this->propertyId = $propertyId;
        $this->actorId    = $actorId;
        $this->reason     = $reason;
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
        return 'booking.cancelled';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'id'           => $this->booking->id,
            'status'       => $this->booking->status,
            'room_id'      => $this->booking->room_id,
            'partner_id'   => $this->partnerId,
            'property_id'  => $this->propertyId,
            'cancelled_at' => optional($this->booking->cancelled_at)->toIso8601String(),
            'actor_id'     => $this->actorId,
            // Lý do hủy có thể chứa thông tin nhạy cảm — chỉ trả khi không trống.
            'has_reason'   => $this->reason !== null && trim($this->reason) !== '',
        ];
    }
}
