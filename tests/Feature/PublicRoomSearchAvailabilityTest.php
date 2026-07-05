<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Enums\RoomStatus;
use App\Models\Booking;
use App\Models\Room;
use App\Models\RoomBlock;
use App\Models\RoomPrice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class PublicRoomSearchAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_search_excludes_room_with_conflicting_booking_when_dates_provided(): void
    {
        $room = Room::query()
            ->with('property')
            ->where('status', RoomStatus::PUBLIC)
            ->whereHas('property', static function ($query): void {
                $query->whereNotNull('ward_id');
            })
            ->firstOrFail();
        $user = User::query()->where('role', 'user')->firstOrFail();
        $price = RoomPrice::query()->where('room_id', $room->id)->first()
            ?? RoomPrice::query()->firstOrFail();

        Booking::query()->create([
            'room_id'     => $room->id,
            'user_id'     => $user->id,
            'price_id'    => $price->id,
            'start_date'  => '2099-11-07',
            'end_date'    => '2099-11-12',
            'status'      => BookingStatus::CONFIRMED->value,
            'stay_status' => 'pending',
        ]);

        $baseParams = [
            'ward_id'  => (int) $room->property->ward_id,
            'page'     => 1,
            'per_page' => 100,
        ];

        $withoutDates = $this->getJson('/api/v1/rooms/search?' . http_build_query($baseParams));
        $withoutDates->assertOk();
        $this->assertContains(
            $room->id,
            collect($withoutDates->json('data.data'))->pluck('id')->all(),
        );

        $withDates = $this->getJson(
            '/api/v1/rooms/search?'
            . http_build_query(array_merge($baseParams, [
                'start_date' => '2099-11-07',
                'end_date'   => '2099-11-12',
            ])),
        );

        $withDates->assertOk();
        $this->assertNotContains(
            $room->id,
            collect($withDates->json('data.data'))->pluck('id')->all(),
        );
    }

    public function test_search_allows_back_to_back_stay_after_existing_checkout(): void
    {
        $room = Room::query()
            ->with('property')
            ->where('status', RoomStatus::PUBLIC)
            ->whereHas('property', static function ($query): void {
                $query->whereNotNull('ward_id');
            })
            ->firstOrFail();
        $user = User::query()->where('role', 'user')->firstOrFail();
        $price = RoomPrice::query()->where('room_id', $room->id)->first()
            ?? RoomPrice::query()->firstOrFail();

        Booking::query()->create([
            'room_id'     => $room->id,
            'user_id'     => $user->id,
            'price_id'    => $price->id,
            'start_date'  => '2099-11-07',
            'end_date'    => '2099-11-12',
            'status'      => BookingStatus::CONFIRMED->value,
            'stay_status' => 'pending',
        ]);

        $response = $this->getJson(
            '/api/v1/rooms/search?'
            . http_build_query([
                'ward_id'    => (int) $room->property->ward_id,
                'start_date' => '2099-11-12',
                'end_date'   => '2099-11-14',
                'page'       => 1,
                'per_page'   => 100,
            ]),
        );

        $response->assertOk();
        $this->assertContains(
            $room->id,
            collect($response->json('data.data'))->pluck('id')->all(),
        );
    }

    public function test_search_excludes_room_with_conflicting_room_block(): void
    {
        $room = Room::query()
            ->with('property')
            ->where('status', RoomStatus::PUBLIC)
            ->whereHas('property', static function ($query): void {
                $query->whereNotNull('ward_id');
            })
            ->firstOrFail();

        RoomBlock::query()->create([
            'room_id'    => $room->id,
            'start_date' => '2099-12-01',
            'end_date'   => '2099-12-05',
            'block_type' => RoomBlock::BLOCK_TYPE_MAINTENANCE,
            'reason'     => 'Test maintenance block',
        ]);

        $response = $this->getJson(
            '/api/v1/rooms/search?'
            . http_build_query([
                'ward_id'    => (int) $room->property->ward_id,
                'start_date' => '2099-12-02',
                'end_date'   => '2099-12-04',
                'page'       => 1,
                'per_page'   => 100,
            ]),
        );

        $response->assertOk();
        $this->assertNotContains(
            $room->id,
            collect($response->json('data.data'))->pluck('id')->all(),
        );
    }

    public function test_search_rejects_incomplete_date_range(): void
    {
        $response = $this->getJson('/api/v1/rooms/search?start_date=2099-11-07');

        $response->assertStatus(422);
    }
}
