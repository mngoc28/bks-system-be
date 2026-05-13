<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Booking;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class BookingNoShow
{
    use Dispatchable;
    use SerializesModels;

    public Booking $booking;
    public int $partnerId;
    public int $propertyId;
    public ?int $actorId;

    public function __construct(Booking $booking, int $partnerId, int $propertyId, ?int $actorId = null)
    {
        $this->booking    = $booking->withoutRelations();
        $this->partnerId  = $partnerId;
        $this->propertyId = $propertyId;
        $this->actorId    = $actorId;
    }
}
