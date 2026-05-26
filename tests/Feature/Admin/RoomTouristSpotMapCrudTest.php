<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class RoomTouristSpotMapCrudTest extends TestCase
{
    use DatabaseMigrations;

    protected function signInAdmin()
    {
        $this->artisan('migrate:refresh');
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
        $roomsResp = $this->getJson('/api/v1/home/rooms/getLatest');
        $roomsResp->assertStatus(200);
        $roomsData = $roomsResp->json('data');
        $this->assertNotEmpty($roomsData);
        $roomId = $roomsData[0]['id'];

        // ensure a tourist spot exists
        $tsResp = $this->getJson('/api/v1/admin/tourist-spots/', [
            'Authorization' => 'Bearer ' . $token,
        ]);
        $tsResp->assertStatus(200);
        $spots = $tsResp->json('data.data') ?? $tsResp->json('data');
        $this->assertNotEmpty($spots);
        $spotId = $spots[0]['id'];

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
}
