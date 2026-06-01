<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoomTouristSpotMapCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function signInAdmin(): string
    {
        $this->seed();

        $response = $this->postJson('/api/v1/admin/auth/login', [
            'email' => 'admin@gmail.com',
            'password' => '123456a!',
        ]);
        $response->assertStatus(200);
        $data = $response->json('data');
        $this->assertArrayHasKey('token', $data);
        return $data['token'];
    }

    public function test_admin_can_create_update_and_delete_room_tourist_spot_map()
    {
        $token = $this->signInAdmin();

        // pick an existing room and tourist spot seeded earlier
        $roomsResp = $this->getJson('/api/v1/home/rooms/getTopRatedRoom');
        $roomsResp->assertStatus(200);
        $roomsData = $roomsResp->json('data');
        $this->assertNotEmpty($roomsData);
        $roomId = $roomsData[0]['id'];

        // find a tourist spot in the same province as the room
        $roomProvinceId = \Illuminate\Support\Facades\DB::table('rooms')
            ->join('properties', 'properties.id', '=', 'rooms.property_id')
            ->where('rooms.id', $roomId)
            ->value('properties.province_id');

        $spotId = \Illuminate\Support\Facades\DB::table('tourist_spots')
            ->where('province_id', $roomProvinceId)
            ->where('is_active', true)
            ->value('id');

        $this->assertNotNull($spotId, "No tourist spot found in province ID: " . $roomProvinceId);

        // create mapping
        $payload = [
            'room_id' => $roomId,
            'tourist_spot_id' => $spotId,
            'distance_km' => 3.2,
            'travel_time_minutes' => 12,
            'priority_order' => 1,
            'is_primary' => true,
            'source_type' => 'manual',
        ];

        $createResp = $this->postJson('/api/v1/admin/room-tourist-spot-maps/', $payload, [
            'Authorization' => 'Bearer ' . $token,
        ]);
        $createResp->assertStatus(201);
        $created = $createResp->json('data');
        $this->assertEquals($roomId, $created['room_id']);

        $id = $created['id'] ?? null;
        $this->assertNotNull($id);

        // update
        $updateResp = $this->putJson('/api/v1/admin/room-tourist-spot-maps/' . $id, [
            'distance_km' => 5.5,
        ], [
            'Authorization' => 'Bearer ' . $token,
        ]);
        $updateResp->assertStatus(200);
        $this->assertEquals(5.5, $updateResp->json('data.distance_km'));

        // delete
        $delResp = $this->deleteJson('/api/v1/admin/room-tourist-spot-maps/' . $id, [], [
            'Authorization' => 'Bearer ' . $token,
        ]);
        $delResp->assertStatus(200);
    }

    public function test_bulk_propagation_across_same_property()
    {
        $token = $this->signInAdmin();

        // 1. Pick a room
        $roomsResp = $this->getJson('/api/v1/home/rooms/getTopRatedRoom');
        $roomsResp->assertStatus(200);
        $roomsData = $roomsResp->json('data');
        $this->assertNotEmpty($roomsData);
        $roomAId = $roomsData[0]['id'];

        // Get its property ID
        $propertyId = \Illuminate\Support\Facades\DB::table('rooms')
            ->where('id', $roomAId)
            ->value('property_id');

        // Create another room B in the same property to test propagation
        $roomBId = \Illuminate\Support\Facades\DB::table('rooms')->insertGetId([
            'property_id' => $propertyId,
            'title' => 'Room B for Propagation Test',
            'room_number' => 'PROP-B',
            'area' => 30.5,
            'floor_number' => 1,
            'people' => 2,
            'room_type' => 1,
            'status' => true,
            'created_by' => 1,
            'updated_by' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Find a tourist spot in the same province
        $roomProvinceId = \Illuminate\Support\Facades\DB::table('properties')
            ->where('id', $propertyId)
            ->value('province_id');

        $spotId = \Illuminate\Support\Facades\DB::table('tourist_spots')
            ->where('province_id', $roomProvinceId)
            ->where('is_active', true)
            ->value('id');

        $this->assertNotNull($spotId);

        // Clear any existing maps for this spot on Room B and Room A to start clean
        \Illuminate\Support\Facades\DB::table('room_tourist_spot_maps')
            ->whereIn('room_id', [$roomAId, $roomBId])
            ->where('tourist_spot_id', $spotId)
            ->delete();

        // 2. Create mapping on Room A with apply_to_all_rooms = true
        $payload = [
            'room_id' => $roomAId,
            'tourist_spot_id' => $spotId,
            'distance_km' => 4.5,
            'travel_time_minutes' => 15,
            'priority_order' => 2,
            'is_primary' => true,
            'source_type' => 'manual',
            'apply_to_all_rooms' => true,
        ];

        $createResp = $this->postJson('/api/v1/admin/room-tourist-spot-maps/', $payload, [
            'Authorization' => 'Bearer ' . $token,
        ]);
        $createResp->assertStatus(201);

        // Assert mapping is created on Room A
        $this->assertDatabaseHas('room_tourist_spot_maps', [
            'room_id' => $roomAId,
            'tourist_spot_id' => $spotId,
            'distance_km' => 4.5,
            'is_primary' => 1,
        ]);

        // Assert mapping is also propagated to Room B
        $this->assertDatabaseHas('room_tourist_spot_maps', [
            'room_id' => $roomBId,
            'tourist_spot_id' => $spotId,
            'distance_km' => 4.5,
            'is_primary' => 1,
        ]);

        // 3. Update mapping on Room A with apply_to_all_rooms = true
        $mapAId = $createResp->json('data.id');
        $updateResp = $this->putJson('/api/v1/admin/room-tourist-spot-maps/' . $mapAId, [
            'distance_km' => 7.2,
            'travel_time_minutes' => 25,
            'apply_to_all_rooms' => true,
        ], [
            'Authorization' => 'Bearer ' . $token,
        ]);
        $updateResp->assertStatus(200);

        // Assert Room A is updated
        $this->assertDatabaseHas('room_tourist_spot_maps', [
            'room_id' => $roomAId,
            'tourist_spot_id' => $spotId,
            'distance_km' => 7.2,
            'travel_time_minutes' => 25,
        ]);

        // Assert Room B is also updated
        $this->assertDatabaseHas('room_tourist_spot_maps', [
            'room_id' => $roomBId,
            'tourist_spot_id' => $spotId,
            'distance_km' => 7.2,
            'travel_time_minutes' => 25,
        ]);

        // 4. Delete mapping on Room A with apply_to_all_rooms = true
        $delResp = $this->deleteJson('/api/v1/admin/room-tourist-spot-maps/' . $mapAId . '?apply_to_all_rooms=true', [], [
            'Authorization' => 'Bearer ' . $token,
        ]);
        $delResp->assertStatus(200);

        // Assert mapping is deleted on Room A
        $this->assertDatabaseMissing('room_tourist_spot_maps', [
            'room_id' => $roomAId,
            'tourist_spot_id' => $spotId,
        ]);

        // Assert mapping is also deleted on Room B
        $this->assertDatabaseMissing('room_tourist_spot_maps', [
            'room_id' => $roomBId,
            'tourist_spot_id' => $spotId,
        ]);
    }
}
