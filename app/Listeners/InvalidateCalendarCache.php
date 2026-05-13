<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\BookingCancelled;
use App\Events\BookingConfirmed;
use App\Events\BookingCreated;
use App\Events\RoomBlockChanged;
use App\Services\PartnerCalendarService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Invalidate Partner Calendar cache khi booking/state thay đổi.
 *
 * Bumps version pointer per partner (xem `PartnerCalendarService`) — request
 * tiếp theo sẽ recompute. TTL 30s vẫn là safety net khi listener không
 * chạy được (queue down). Là `ShouldQueue` để không chặn HTTP/broadcast path.
 */
class InvalidateCalendarCache implements ShouldQueue
{
    use InteractsWithQueue;

    public int $tries = 3;
    public int $backoff = 5;

    public function __construct(
        private readonly PartnerCalendarService $calendarService,
    ) {
    }

    public function handleBookingCreated(BookingCreated $event): void
    {
        $this->bump((int) $event->partnerId, 'booking.created');
    }

    public function handleBookingConfirmed(BookingConfirmed $event): void
    {
        $this->bump((int) $event->partnerId, 'booking.confirmed');
    }

    public function handleBookingCancelled(BookingCancelled $event): void
    {
        $this->bump((int) $event->partnerId, 'booking.cancelled');
    }

    public function handleRoomBlockChanged(RoomBlockChanged $event): void
    {
        $this->bump((int) $event->partnerId, 'room_block.' . $event->action);
    }

    private function bump(int $partnerId, string $sourceEvent): void
    {
        try {
            $this->calendarService->bumpVersion($partnerId);
        } catch (Throwable $e) {
            Log::warning('InvalidateCalendarCache failed', [
                'partner_id' => $partnerId,
                'source'     => $sourceEvent,
                'error'      => $e->getMessage(),
            ]);
        }
    }
}
