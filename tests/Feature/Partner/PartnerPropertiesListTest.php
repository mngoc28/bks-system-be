<?php

declare(strict_types=1);

namespace Tests\Feature\Partner;

use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class PartnerPropertiesListTest extends TestCase
{
    use RefreshDatabase;

    private function partnerToken(): string
    {
        $this->seed();

        $response = $this->postJson('/api/v1/partner/auth/login', [
            'email'    => 'partner@gmail.com',
            'password' => '123456a!',
        ]);

        $response->assertOk();

        return (string) $response->json('data.token');
    }

    public function test_partner_properties_list_without_rooms_returns_paginated_metadata(): void
    {
        $token = $this->partnerToken();

        $response = $this->getJson('/api/v1/partner/properties/searchAll?page=1&per_page=5&with_rooms=0', [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertOk();
        $response->assertJsonPath('status', 'success');
        $response->assertJsonStructure([
            'data' => [
                'current_page',
                'data',
                'last_page',
                'per_page',
                'total',
            ],
        ]);

        $first = $response->json('data.data.0');
        if (is_array($first)) {
            $this->assertArrayNotHasKey('rooms', $first);
            $this->assertArrayHasKey('rooms_count', $first);
        }
    }

    public function test_partner_properties_preview_mode_limits_rooms_when_filtered_by_id(): void
    {
        $token = $this->partnerToken();

        $partner = User::query()->where('email', 'partner@gmail.com')->firstOrFail();
        $property = Property::query()
            ->where('user_id', $partner->id)
            ->withCount('rooms')
            ->orderByDesc('rooms_count')
            ->whereHas('rooms')
            ->first();

        if ($property === null) {
            $this->markTestSkipped('No partner property with rooms in seed data.');
        }

        $response = $this->getJson(
            '/api/v1/partner/properties/searchAll?id=' . $property->id . '&with_rooms=preview&rooms_limit=6&per_page=1',
            ['Authorization' => 'Bearer ' . $token]
        );

        $response->assertOk();
        $rooms = $response->json('data.data.0.rooms') ?? [];
        $this->assertIsArray($rooms);
        $this->assertLessThanOrEqual(6, count($rooms));
    }

    public function test_partner_cannot_list_other_partner_property_by_id(): void
    {
        $token = $this->partnerToken();

        $otherProperty = Property::query()
            ->whereHas('user', static fn ($q) => $q->where('email', '!=', 'partner@gmail.com'))
            ->first();

        if ($otherProperty === null) {
            $this->markTestSkipped('No property owned by another user in seed data.');
        }

        $response = $this->getJson(
            '/api/v1/partner/properties/searchAll?id=' . $otherProperty->id,
            ['Authorization' => 'Bearer ' . $token]
        );

        $response->assertOk();
        $this->assertSame([], $response->json('data.data'));
    }

    public function test_legacy_with_rooms_flag_still_returns_rooms(): void
    {
        $token = $this->partnerToken();

        $partner = User::query()->where('email', 'partner@gmail.com')->firstOrFail();
        $property = Property::query()->where('user_id', $partner->id)->firstOrFail();

        $response = $this->getJson(
            '/api/v1/partner/properties/searchAll?id=' . $property->id . '&with_rooms=1&per_page=1',
            ['Authorization' => 'Bearer ' . $token]
        );

        $response->assertOk();
        $rooms = $response->json('data.data.0.rooms') ?? [];
        $this->assertIsArray($rooms);
        $this->assertNotEmpty($rooms);
    }

    public function test_partner_property_room_preview_endpoint_returns_limited_rooms(): void
    {
        $token = $this->partnerToken();

        $partner = User::query()->where('email', 'partner@gmail.com')->firstOrFail();
        $property = Property::query()
            ->where('user_id', $partner->id)
            ->withCount('rooms')
            ->orderByDesc('rooms_count')
            ->firstOrFail();

        $response = $this->getJson(
            '/api/v1/partner/properties/' . $property->id . '/rooms/preview?limit=6',
            ['Authorization' => 'Bearer ' . $token]
        );

        $response->assertOk();
        $response->assertJsonPath('data.property_id', $property->id);
        $response->assertJsonPath('data.preview_limit', 6);
        $response->assertJsonStructure([
            'data' => [
                'property_id',
                'total_rooms',
                'preview_limit',
                'rooms',
            ],
        ]);

        $rooms = $response->json('data.rooms') ?? [];
        $this->assertIsArray($rooms);
        $this->assertLessThanOrEqual(6, count($rooms));
        $this->assertSame((int) $property->rooms_count, (int) $response->json('data.total_rooms'));
    }

    public function test_partner_property_room_preview_returns_not_found_for_other_partner(): void
    {
        $token = $this->partnerToken();

        $otherProperty = Property::query()
            ->whereHas('user', static fn ($q) => $q->where('email', '!=', 'partner@gmail.com'))
            ->first();

        if ($otherProperty === null) {
            $this->markTestSkipped('No property owned by another user in seed data.');
        }

        $response = $this->getJson(
            '/api/v1/partner/properties/' . $otherProperty->id . '/rooms/preview',
            ['Authorization' => 'Bearer ' . $token]
        );

        $response->assertNotFound();
    }
}
