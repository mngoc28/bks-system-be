<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Enums\PaymentStatus;
use App\Models\Booking;
use App\Models\Room;
use App\Models\RoomPrice;
use App\Models\User;
use App\Services\BookingPaymentStatusService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class BookingPaymentStatusServiceTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    public function test_confirm_booking_with_deposit_stays_unpaid_until_deposit_confirmed(): void
    {
        $room = Room::query()->firstOrFail();
        $user = User::query()->where('role', 'user')->firstOrFail();
        $price = RoomPrice::query()->where('unit', 'night')->firstOrFail();
        $price->update(['price' => 810_000]);

        $booking = Booking::query()->create([
            'room_id'         => $room->id,
            'user_id'         => $user->id,
            'price_id'        => $price->id,
            'start_date'      => now()->addDays(3)->format('Y-m-d'),
            'end_date'        => now()->addDays(4)->format('Y-m-d'),
            'status'          => 1,
            'payment_method'  => 'online',
            'deposit_amount'  => 405_000,
            'deposit_status'  => 'pending',
            'payment_status'  => PaymentStatus::PAID->value,
        ]);

        $resolved = BookingPaymentStatusService::resolve($booking->fresh());

        $this->assertSame(PaymentStatus::UNPAID->value, $resolved);
    }

    public function test_confirmed_deposit_sets_partially_paid_when_total_higher(): void
    {
        $room = Room::query()->firstOrFail();
        $user = User::query()->where('role', 'user')->firstOrFail();
        $price = RoomPrice::query()->where('unit', 'night')->firstOrFail();
        $price->update(['price' => 810_000]);

        $booking = Booking::query()->create([
            'room_id'         => $room->id,
            'user_id'         => $user->id,
            'price_id'        => $price->id,
            'start_date'      => now()->addDays(3)->format('Y-m-d'),
            'end_date'        => now()->addDays(4)->format('Y-m-d'),
            'status'          => 1,
            'payment_method'  => 'online',
            'deposit_amount'  => 405_000,
            'deposit_status'  => 'confirmed_by_partner',
            'payment_status'  => PaymentStatus::UNPAID->value,
        ]);

        $booking->load(['price', 'services']);
        $resolved = BookingPaymentStatusService::resolve($booking);

        $this->assertSame(PaymentStatus::PARTIALLY_PAID->value, $resolved);

        BookingPaymentStatusService::sync($booking);
        $this->assertSame(PaymentStatus::PARTIALLY_PAID->value, $booking->fresh()->payment_status);
    }

    public function test_paid_status_with_confirmed_deposit_stays_paid(): void
    {
        $room = Room::query()->firstOrFail();
        $user = User::query()->where('role', 'user')->firstOrFail();
        $price = RoomPrice::query()->where('unit', 'night')->firstOrFail();
        $price->update(['price' => 810_000]);

        $booking = Booking::query()->create([
            'room_id'         => $room->id,
            'user_id'         => $user->id,
            'price_id'        => $price->id,
            'start_date'      => now()->addDays(3)->format('Y-m-d'),
            'end_date'        => now()->addDays(4)->format('Y-m-d'),
            'status'          => 1,
            'payment_method'  => 'online',
            'deposit_amount'  => 405_000,
            'deposit_status'  => 'confirmed_by_partner',
            'payment_status'  => PaymentStatus::PAID->value,
        ]);

        $booking->load(['price', 'services']);

        $this->assertSame(PaymentStatus::PAID->value, BookingPaymentStatusService::resolve($booking));
        $this->assertSame(0.0, BookingPaymentStatusService::getAmountRemaining($booking));
    }

    public function test_mark_fully_paid_keeps_paid_status_with_deposit(): void
    {
        $room = Room::query()->firstOrFail();
        $user = User::query()->where('role', 'user')->firstOrFail();
        $price = RoomPrice::query()->where('unit', 'night')->firstOrFail();
        $price->update(['price' => 810_000]);

        $booking = Booking::query()->create([
            'room_id'         => $room->id,
            'user_id'         => $user->id,
            'price_id'        => $price->id,
            'start_date'      => now()->addDays(3)->format('Y-m-d'),
            'end_date'        => now()->addDays(4)->format('Y-m-d'),
            'status'          => 1,
            'payment_method'  => 'online',
            'deposit_amount'  => 405_000,
            'deposit_status'  => 'confirmed_by_partner',
            'payment_status'  => PaymentStatus::PARTIALLY_PAID->value,
        ]);

        BookingPaymentStatusService::markFullyPaid($booking->fresh());
        $booking = $booking->fresh()->load(['price', 'services']);

        $this->assertSame(PaymentStatus::PAID->value, $booking->payment_status);
        $this->assertSame(0.0, BookingPaymentStatusService::getAmountRemaining($booking));
    }
}
