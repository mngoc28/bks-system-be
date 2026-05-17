<?php

declare(strict_types=1);

namespace App\Providers;

use App\Events\BookingCancelled;
use App\Events\CancellationRequestUpdated;
use App\Events\BookingConfirmed;
use App\Events\BookingCreated;
use App\Events\BookingNoShow;
use App\Events\ContractRenewalReminderQueued;
use App\Events\RoomBlockChanged;
use App\Listeners\InvalidateCalendarCache;
use App\Listeners\RecordCancellationRequestBroadcastMarker;
use App\Listeners\InvalidatePartnerKpiCache;
use App\Listeners\RecordBookingTimeline;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * Maps event classes to listener class names and/or [listener, method] tuples (see Laravel docs).
     *
     * @phpstan-ignore-next-line Child arrays include callable tuples; parent PHPDoc only lists class-string listeners.
     */
    protected $listen = [
        Registered::class => [SendEmailVerificationNotification::class],

        // Realtime audit markers (Partner Portal 360 Phase 2)
        // + calendar cache invalidation (Phase 3 T3.10).
        BookingCreated::class => [
            [RecordBookingTimeline::class, 'handleCreated'],
            [InvalidateCalendarCache::class, 'handleBookingCreated'],
            [InvalidatePartnerKpiCache::class, 'handleBookingCreated'],
        ],
        BookingConfirmed::class => [
            [RecordBookingTimeline::class, 'handleConfirmed'],
            [InvalidateCalendarCache::class, 'handleBookingConfirmed'],
            [InvalidatePartnerKpiCache::class, 'handleBookingConfirmed'],
        ],
        BookingCancelled::class => [
            [RecordBookingTimeline::class, 'handleCancelled'],
            [InvalidateCalendarCache::class, 'handleBookingCancelled'],
            [InvalidatePartnerKpiCache::class, 'handleBookingCancelled'],
        ],
        BookingNoShow::class => [
            [InvalidatePartnerKpiCache::class, 'handleNoShow'],
        ],
        RoomBlockChanged::class => [
            [InvalidateCalendarCache::class, 'handleRoomBlockChanged'],
            [InvalidatePartnerKpiCache::class, 'handleRoomBlockChanged'],
        ],
        // Phase 5: scheduler-driven event — no broadcast listener side-effects;
        // FE Alert Center subscribes directly to the broadcast channel.
        ContractRenewalReminderQueued::class => [],

        CancellationRequestUpdated::class => [
            [RecordCancellationRequestBroadcastMarker::class, 'handle'],
        ],
    ];

    public function boot(): void
    {
        //
    }

    /**
     * @return bool
     */
    public function shouldDiscoverEvents()
    {
        return false;
    }
}
