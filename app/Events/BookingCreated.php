<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Booking;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Sự kiện khi booking mới được tạo (từ End User booking flow).
 *
 * Payload broadcast cố tình KHÔNG chứa PII của khách (name/email/phone). FE
 * cần các field tối thiểu để invalidate cache + hiển thị toast/badge; chi tiết
 * sẽ refetch qua API có authorization.
 */
class BookingCreated implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public Booking $booking;
    public int $partnerId;
    public int $propertyId;

    public function __construct(Booking $booking, int $partnerId, int $propertyId)
    {
        $this->booking    = $booking->withoutRelations();
        $this->partnerId  = $partnerId;
        $this->propertyId = $propertyId;
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
        return 'booking.created';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'id'         => $this->booking->id,
            'status'     => $this->booking->status,
            'room_id'    => $this->booking->room_id,
            'partner_id' => $this->partnerId,
            'property_id'=> $this->propertyId,
            'start_date' => optional($this->booking->start_date)->toDateString(),
            'end_date'   => optional($this->booking->end_date)->toDateString(),
            'created_at' => optional($this->booking->created_at)->toIso8601String(),
        ];
    }
}
