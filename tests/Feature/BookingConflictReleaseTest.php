<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Room;
use App\Models\RoomPrice;
use App\Models\User;
use App\Repositories\BookingRepository\BookingRepository;
use App\Services\ConflictChecker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class BookingConflictReleaseTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_cancelled_booking_does_not_block_new_reservation(): void
    {
        $room = Room::query()->firstOrFail();
        $user = User::query()->where('role', 'user')->firstOrFail();
        $price = RoomPrice::query()->where('room_id', $room->id)->first()
            ?? RoomPrice::query()->firstOrFail();

        Booking::query()->create([
            'room_id'     => $room->id,
            'user_id'     => $user->id,
            'price_id'    => $price->id,
            'start_date'  => '2099-06-10',
            'end_date'    => '2099-06-12',
            'status'      => BookingStatus::CANCELLED->value,
            'stay_status' => 'pending',
        ]);

        /** @var BookingRepository $repo */
        $repo = app(BookingRepository::class);

        $this->assertFalse($repo->checkRoomConflict($room->id, '2099-06-10', '2099-06-12'));
    }

    public function test_no_show_booking_does_not_block_new_reservation(): void
    {
        $room = Room::query()->firstOrFail();
        $user = User::query()->where('role', 'user')->firstOrFail();
        $price = RoomPrice::query()->where('room_id', $room->id)->first()
            ?? RoomPrice::query()->firstOrFail();

        Booking::query()->create([
            'room_id'     => $room->id,
            'user_id'     => $user->id,
            'price_id'    => $price->id,
            'start_date'  => '2099-07-10',
            'end_date'    => '2099-07-12',
            'status'      => BookingStatus::CONFIRMED->value,
            'stay_status' => 'no_show',
        ]);

        /** @var ConflictChecker $checker */
        $checker = app(ConflictChecker::class);
        $result = $checker->findConflicts($room->id, '2099-07-10', '2099-07-12');

        $this->assertFalse($result['hasConflict']);
        $this->assertTrue($result['bookings']->isEmpty());
    }

    public function test_confirmed_pending_booking_still_blocks_overlap(): void
    {
        $room = Room::query()->firstOrFail();
        $user = User::query()->where('role', 'user')->firstOrFail();
        $price = RoomPrice::query()->where('room_id', $room->id)->first()
            ?? RoomPrice::query()->firstOrFail();

        Booking::query()->create([
            'room_id'     => $room->id,
            'user_id'     => $user->id,
            'price_id'    => $price->id,
            'start_date'  => '2099-08-10',
            'end_date'    => '2099-08-12',
            'status'      => BookingStatus::CONFIRMED->value,
            'stay_status' => 'pending',
        ]);

        /** @var BookingRepository $repo */
        $repo = app(BookingRepository::class);

        $this->assertTrue($repo->checkRoomConflict($room->id, '2099-08-10', '2099-08-12'));
    }
}
