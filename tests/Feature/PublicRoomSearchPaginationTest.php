<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\RoomStatus;
use App\Models\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class PublicRoomSearchPaginationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_public_room_search_returns_paginated_payload_and_applies_ward_filter(): void
    {
        $room = Room::query()
            ->with('property')
            ->where('status', RoomStatus::PUBLIC)
            ->whereHas('property', static function ($query): void {
                $query->whereNotNull('ward_id');
            })
            ->first();

        $this->assertNotNull($room);
        $this->assertNotNull($room->property);

        $wardId = (int) $room->property->ward_id;
        $expectedTotal = Room::query()
            ->where('status', RoomStatus::PUBLIC)
            ->whereHas('property', static function ($query) use ($wardId): void {
                $query->where('ward_id', $wardId);
            })
            ->count();

        $response = $this->getJson("/api/v1/rooms/search?ward_id={$wardId}&page=1&per_page=5");

        $response->assertOk();
        $response->assertJsonPath('status', 'success');
        $response->assertJsonPath('data.current_page', 1);
        $response->assertJsonPath('data.per_page', 5);
        $response->assertJsonPath('data.total', $expectedTotal);
        $response->assertJsonStructure([
            'status',
            'message',
            'data' => [
                'current_page',
                'data',
                'last_page',
                'per_page',
                'total',
            ],
        ]);

        $this->assertCount(min($expectedTotal, 5), $response->json('data.data'));
    }

    public function test_public_room_search_keeps_legacy_collection_shape_without_pagination_params(): void
    {
        $response = $this->getJson('/api/v1/rooms/search');

        $response->assertOk();
        $response->assertJsonPath('status', 'success');

        $data = $response->json('data');

        $this->assertIsArray($data);
        $this->assertArrayNotHasKey('current_page', $data);
        $this->assertTrue(array_is_list($data));
    }

    public function test_public_room_search_applies_price_filters(): void
    {
        $responseAll = $this->getJson('/api/v1/rooms/search');
        $responseAll->assertOk();
        $allRooms = $responseAll->json('data');

        if (count($allRooms) > 0) {
            $prices = array_map(function ($r) {
                return (float) ($r['cheapest_daily_price'] ?? 0);
            }, $allRooms);

            $minPrice = min($prices);
            $maxPrice = max($prices);

            if ($minPrice < $maxPrice) {
                $midPrice = ($minPrice + $maxPrice) / 2;

                $responseFiltered = $this->getJson("/api/v1/rooms/search?price_max={$midPrice}");
                $responseFiltered->assertOk();
                $filteredRooms = $responseFiltered->json('data');

                foreach ($filteredRooms as $r) {
                    $this->assertLessThanOrEqual(
                        $midPrice,
                        (float) ($r['cheapest_daily_price'] ?? 0),
                        sprintf(
                            "Room ID %d has cheapest_daily_price %s which is > midPrice %s. All rooms in search: %s. Filtered rooms: %s",
                            $r['id'] ?? 0,
                            $r['cheapest_daily_price'] ?? 'null',
                            $midPrice,
                            json_encode($allRooms),
                            json_encode($filteredRooms)
                        )
                    );
                }

                $responseFilteredMin = $this->getJson("/api/v1/rooms/search?price_min={$midPrice}");
                $responseFilteredMin->assertOk();
                $filteredRoomsMin = $responseFilteredMin->json('data');

                foreach ($filteredRoomsMin as $r) {
                    $this->assertGreaterThanOrEqual($midPrice, (float) ($r['cheapest_daily_price'] ?? 0));
                }
            }
        }
    }

    public function test_public_room_search_applies_guests_and_rent_type_filters(): void
    {
        $responseAll = $this->getJson('/api/v1/rooms/search');
        $responseAll->assertOk();
        $allRooms = $responseAll->json('data');

        if (count($allRooms) > 0) {
            $peopleCounts = array_map(function ($r) {
                return (int) ($r['people'] ?? 0);
            }, $allRooms);

            $maxPeople = max($peopleCounts);

            if ($maxPeople > 1) {
                $responseFiltered = $this->getJson("/api/v1/rooms/search?guests={$maxPeople}");
                $responseFiltered->assertOk();
                $filteredRooms = $responseFiltered->json('data');

                foreach ($filteredRooms as $r) {
                    $this->assertGreaterThanOrEqual($maxPeople, (int) ($r['people'] ?? 0));
                }
            }

            $responseDaily = $this->getJson('/api/v1/rooms/search?rent_type=daily');
            $responseDaily->assertOk();
            foreach ($responseDaily->json('data') as $r) {
                $this->assertNotEmpty($r['cheapest_daily_price']);
                $this->assertGreaterThan(0, (float) $r['cheapest_daily_price']);
            }
        }
    }
}
