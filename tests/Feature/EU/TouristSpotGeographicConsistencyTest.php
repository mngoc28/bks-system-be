<?php

declare(strict_types=1);

namespace Tests\Feature\EU;

use App\Enums\RoomStatus;
use App\Models\Room;
use App\Models\TouristSpot;
use App\Services\RoomTouristSummaryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class TouristSpotGeographicConsistencyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_tourist_summary_ignores_cross_province_primary_map(): void
    {
        $hcmSpot = TouristSpot::query()->where('region_label', 'Hồ Chí Minh')->where('is_active', true)->first();
        $nhaTrangSpot = TouristSpot::query()->where('slug', 'vinwonders-nha-trang')->where('is_active', true)->first();

        $this->assertNotNull($hcmSpot);
        $this->assertNotNull($nhaTrangSpot);
        $this->assertNotSame($hcmSpot->province_id, $nhaTrangSpot->province_id);

        $hcmRoomId = (int) DB::table('rooms')
            ->join('properties', 'properties.id', '=', 'rooms.property_id')
            ->where('rooms.status', RoomStatus::PUBLIC)
            ->where('properties.province_id', $hcmSpot->province_id)
            ->value('rooms.id');

        $this->assertGreaterThan(0, $hcmRoomId);

        DB::table('room_tourist_spot_maps')->where('room_id', $hcmRoomId)->delete();

        DB::table('room_tourist_spot_maps')->insert([
            'room_id' => $hcmRoomId,
            'tourist_spot_id' => $nhaTrangSpot->id,
            'distance_km' => 5,
            'travel_time_minutes' => 14,
            'priority_order' => 1,
            'is_primary' => true,
            'source_type' => 'manual',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('room_tourist_spot_maps')->insert([
            'room_id' => $hcmRoomId,
            'tourist_spot_id' => $hcmSpot->id,
            'distance_km' => 8,
            'travel_time_minutes' => 25,
            'priority_order' => 2,
            'is_primary' => false,
            'source_type' => 'manual',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $room = Room::query()->find($hcmRoomId);
        $summaryService = app(RoomTouristSummaryService::class);
        $enriched = $summaryService->enrichRoom($room);

        $this->assertTrue($enriched->tourist_summary['has_tourist_mapping']);
        $this->assertSame($hcmSpot->name, $enriched->tourist_summary['tourist_spot_name']);
        $this->assertNotSame($nhaTrangSpot->name, $enriched->tourist_summary['tourist_spot_name']);
    }

    public function test_homepage_spot_group_excludes_cross_province_rooms(): void
    {
        $spot = TouristSpot::query()->where('slug', 'vinwonders-nha-trang')->where('is_active', true)->first();
        $this->assertNotNull($spot);

        $validRoomIds = DB::table('rooms')
            ->join('properties', 'properties.id', '=', 'rooms.property_id')
            ->where('rooms.status', RoomStatus::PUBLIC)
            ->where('properties.province_id', $spot->province_id)
            ->orderBy('rooms.id')
            ->limit(4)
            ->pluck('rooms.id')
            ->all();

        $this->assertGreaterThanOrEqual(4, count($validRoomIds));

        DB::table('room_tourist_spot_maps')
            ->where('tourist_spot_id', $spot->id)
            ->delete();

        foreach ($validRoomIds as $index => $roomId) {
            DB::table('room_tourist_spot_maps')->insert([
                'room_id' => $roomId,
                'tourist_spot_id' => $spot->id,
                'distance_km' => 5,
                'travel_time_minutes' => 20 + $index,
                'priority_order' => $index + 1,
                'is_primary' => $index === 0,
                'source_type' => 'manual',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $hcmRoomId = (int) DB::table('rooms')
            ->join('properties', 'properties.id', '=', 'rooms.property_id')
            ->where('rooms.status', RoomStatus::PUBLIC)
            ->where('properties.province_id', '!=', $spot->province_id)
            ->value('rooms.id');

        if ($hcmRoomId > 0) {
            DB::table('room_tourist_spot_maps')->insert([
                'room_id' => $hcmRoomId,
                'tourist_spot_id' => $spot->id,
                'distance_km' => 3,
                'travel_time_minutes' => 14,
                'priority_order' => 99,
                'is_primary' => true,
                'source_type' => 'manual',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $response = $this->getJson('/api/v1/home/rooms/rooms-by-tourist-spot?tourist_spot_slugs[]=vinwonders-nha-trang&limit=12');

        $response->assertOk();
        $groups = $response->json('data');
        $this->assertCount(1, $groups);

        $returnedIds = collect($groups[0]['rooms'])->pluck('id')->map(static fn ($id) => (int) $id);
        $this->assertTrue($returnedIds->every(static fn (int $id) => in_array($id, $validRoomIds, true)));
        if ($hcmRoomId > 0) {
            $this->assertFalse($returnedIds->contains($hcmRoomId));
        }
    }
}
