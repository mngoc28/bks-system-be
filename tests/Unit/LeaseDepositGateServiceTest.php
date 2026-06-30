<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Booking;
use App\Models\Contract;
use App\Models\Room;
use App\Models\RoomPrice;
use App\Models\User;
use App\Services\LeaseDepositGateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class LeaseDepositGateServiceTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    public function test_short_term_booking_does_not_require_signed_lease(): void
    {
        $room = Room::query()->firstOrFail();
        $user = User::query()->where('role', 'user')->firstOrFail();
        $price = RoomPrice::query()->where('room_id', $room->id)->where('unit', 'night')->first()
            ?? RoomPrice::query()->where('unit', 'night')->firstOrFail();

        $booking = Booking::query()->create([
            'room_id'        => $room->id,
            'user_id'        => $user->id,
            'price_id'       => $price->id,
            'start_date'     => now()->addDays(2)->format('Y-m-d'),
            'end_date'       => now()->addDays(5)->format('Y-m-d'),
            'status'         => 1,
            'deposit_amount' => 500000.00,
            'deposit_status' => 'pending',
        ]);

        $this->assertTrue(LeaseDepositGateService::canPayDeposit($booking));
    }

    public function test_long_term_booking_blocks_deposit_until_lease_signed(): void
    {
        $partner = User::query()->where('email', 'partner@gmail.com')->firstOrFail();
        $room = Room::query()->firstOrFail();
        $user = User::query()->where('role', 'user')->firstOrFail();
        $price = RoomPrice::query()->where('unit', 'month')->first()
            ?? RoomPrice::query()->firstOrFail();

        $booking = Booking::query()->create([
            'room_id'        => $room->id,
            'user_id'        => $user->id,
            'price_id'       => $price->id,
            'start_date'     => now()->addDays(2)->format('Y-m-d'),
            'end_date'       => now()->addDays(62)->format('Y-m-d'),
            'status'         => 1,
            'deposit_amount' => 5000000.00,
            'deposit_status' => 'pending',
        ]);

        Contract::query()->create([
            'booking_id'    => $booking->id,
            'title'         => 'Hợp đồng thuê',
            'content'       => 'Nội dung hợp đồng',
            'contract_type' => 'LEASE_AGREEMENT',
            'status'        => 0,
            'created_by'    => $partner->id,
            'updated_by'    => $partner->id,
        ]);

        $this->assertFalse(LeaseDepositGateService::canPayDeposit($booking->fresh()));

        Contract::query()
            ->where('booking_id', $booking->id)
            ->update(['status' => 1]);

        $this->assertTrue(LeaseDepositGateService::canPayDeposit($booking->fresh()));
    }
}
