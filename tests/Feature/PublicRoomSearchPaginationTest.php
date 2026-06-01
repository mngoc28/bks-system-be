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
}
