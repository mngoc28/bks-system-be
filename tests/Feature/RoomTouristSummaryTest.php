<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoomTouristSummaryTest extends TestCase
{
    use RefreshDatabase;

    public function test_latest_rooms_include_tourist_summary()
    {
        // seed base data including rooms and our new tourist spot seeders
        $this->seed();

        // call the public endpoint that returns latest rooms
        $response = $this->getJson('/api/v1/home/rooms/getTopRatedRoom?include_tourist_summary=1');

        $response->assertStatus(200);

        $json = $response->json();

        // expect data array with at least one room and tourist_summary present (may be null for some)
        $this->assertArrayHasKey('data', $json);
        $data = $json['data'];
        $this->assertIsArray($data);
        if (!empty($data)) {
            $first = $data[0];
            $this->assertArrayHasKey('tourist_summary', $first);
        }
    }
}
