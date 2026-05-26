<?php

namespace Tests\Feature\Admin;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class TouristSpotCrudTest extends TestCase
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

    public function test_admin_can_create_update_and_delete_tourist_spot()
    {
        $token = $this->signInAdmin();

        // create
        $payload = [
            'name' => 'Test Spot',
            'slug' => 'test-spot',
            'category' => 'landmark',
            'region_label' => 'Test Region',
            'is_featured' => true,
            'sort_order' => 10,
            'is_active' => true,
        ];

        $createResp = $this->postJson('/api/v1/admin/tourist-spots/', $payload, [
            'Authorization' => 'Bearer ' . $token,
        ]);
        $createResp->assertStatus(201);
        $created = $createResp->json('data');
        $this->assertEquals('Test Spot', $created['name']);

        $id = $created['id'] ?? null;
        $this->assertNotNull($id);

        // update
        $updateResp = $this->putJson('/api/v1/admin/tourist-spots/' . $id, [
            'name' => 'Test Spot Updated',
        ], [
            'Authorization' => 'Bearer ' . $token,
        ]);
        $updateResp->assertStatus(200);
        $this->assertEquals('Test Spot Updated', $updateResp->json('data.name'));

        // delete
        $delResp = $this->deleteJson('/api/v1/admin/tourist-spots/' . $id, [], [
            'Authorization' => 'Bearer ' . $token,
        ]);
        $delResp->assertStatus(200);
    }
}
