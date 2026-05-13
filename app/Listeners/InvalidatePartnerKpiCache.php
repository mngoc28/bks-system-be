<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\BookingCancelled;
use App\Events\BookingConfirmed;
use App\Events\BookingCreated;
use App\Events\BookingNoShow;
use App\Events\RoomBlockChanged;
use App\Services\PartnerKpiService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Clears Partner KPI cache slots when booking/calendar operations change the
 * dashboard inputs. Explicit keys are used instead of Redis wildcards so the
 * listener works with array/file cache drivers in dev and test.
 */
final class InvalidatePartnerKpiCache
{
    public function handleBookingCreated(BookingCreated $event): void
    {
        $this->forgetForPartner($event->partnerId);
    }

    public function handleBookingConfirmed(BookingConfirmed $event): void
    {
        $this->forgetForPartner($event->partnerId);
    }

    public function handleBookingCancelled(BookingCancelled $event): void
    {
        $this->forgetForPartner($event->partnerId);
    }

    public function handleNoShow(BookingNoShow $event): void
    {
        $this->forgetForPartner($event->partnerId);
    }

    public function handleRoomBlockChanged(RoomBlockChanged $event): void
    {
        $this->forgetForPartner($event->partnerId);
    }

    private function forgetForPartner(int $partnerId): void
    {
        try {
            foreach (PartnerKpiService::cacheKeysForPartner($partnerId) as $key) {
                Cache::forget($key);
            }
        } catch (Throwable $e) {
            Log::warning('Partner KPI cache invalidation failed', [
                'partner_id' => $partnerId,
                'error'      => $e->getMessage(),
            ]);
        }
    }
}
