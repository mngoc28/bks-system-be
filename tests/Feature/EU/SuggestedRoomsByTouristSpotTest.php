<?php

declare(strict_types=1);

namespace Tests\Feature\EU;

use App\Enums\RoomStatus;
use App\Models\Room;
use App\Models\TouristSpot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class SuggestedRoomsByTouristSpotTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_homepage_suggested_rooms_by_tourist_spot_returns_grouped_payload(): void
    {
        $spot = TouristSpot::query()->where('slug', 'ba-na-hill')->where('is_active', true)->first();
        $this->assertNotNull($spot);

        $roomIds = Room::query()
            ->join('properties', 'properties.id', '=', 'rooms.property_id')
            ->where('rooms.status', RoomStatus::PUBLIC)
            ->where('properties.province_id', $spot->province_id)
            ->orderBy('rooms.id')
            ->limit(4)
            ->pluck('rooms.id')
            ->all();

        $this->assertGreaterThanOrEqual(4, count($roomIds));

        foreach ($roomIds as $index => $roomId) {
            DB::table('room_tourist_spot_maps')->insert([
                'room_id' => $roomId,
                'tourist_spot_id' => $spot->id,
                'distance_km' => 5,
                'travel_time_minutes' => 30 + $index,
                'priority_order' => $index + 1,
                'is_primary' => $index === 0,
                'source_type' => 'manual',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $response = $this->getJson('/api/v1/home/rooms/rooms-by-tourist-spot?tourist_spot_slugs[]=ba-na-hill&limit=12');

        $response->assertOk();
        $response->assertJsonPath('status', 'success');
        $response->assertJsonStructure([
            'data' => [
                [
                    'tourist_spot_id',
                    'tourist_spot_name',
                    'tourist_spot_slug',
                    'region_label',
                    'rooms',
                ],
            ],
        ]);

        $groups = $response->json('data');
        $this->assertCount(1, $groups);
        $this->assertSame('ba-na-hill', $groups[0]['tourist_spot_slug']);
        $this->assertGreaterThanOrEqual(4, count($groups[0]['rooms']));
    }

    public function test_public_room_search_filters_by_tourist_spot_slug(): void
    {
        $spot = TouristSpot::query()->where('slug', 'sa-pa')->where('is_active', true)->first();
        $this->assertNotNull($spot);

        $expectedRoomIds = DB::table('rooms')
            ->join('properties', 'properties.id', '=', 'rooms.property_id')
            ->join('room_tourist_spot_maps', 'room_tourist_spot_maps.room_id', '=', 'rooms.id')
            ->join('tourist_spots', 'tourist_spots.id', '=', 'room_tourist_spot_maps.tourist_spot_id')
            ->where('tourist_spots.slug', 'sa-pa')
            ->where('tourist_spots.is_active', true)
            ->where('rooms.status', RoomStatus::PUBLIC)
            ->whereColumn('properties.province_id', 'tourist_spots.province_id')
            ->distinct()
            ->pluck('rooms.id')
            ->map(static fn ($id) => (int) $id);

        $response = $this->getJson('/api/v1/rooms/search?tourist_spot_slug=sa-pa&page=1&per_page=100');

        $response->assertOk();
        $response->assertJsonPath('status', 'success');

        $ids = collect($response->json('data.data'))->pluck('id')->map(static fn ($id) => (int) $id);
        $this->assertGreaterThan(0, $ids->count());
        $this->assertGreaterThanOrEqual(1, $expectedRoomIds->count());
        $this->assertTrue(
            $ids->every(static fn (int $id) => $expectedRoomIds->contains($id)),
            'Mọi phòng trả về phải có mapping tới Sa Pa',
        );
        $this->assertSame($expectedRoomIds->count(), (int) $response->json('data.total'));
    }

    public function test_public_tourist_spots_list_returns_active_spots(): void
    {
        $response = $this->getJson('/api/v1/home/tourist-spots?featured_only=true&limit=10');

        $response->assertOk();
        $response->assertJsonPath('status', 'success');

        $spots = $response->json('data');
        $this->assertIsArray($spots);
        $this->assertGreaterThan(0, count($spots));
        $this->assertArrayHasKey('slug', $spots[0]);
        $this->assertArrayHasKey('name', $spots[0]);
    }
}
