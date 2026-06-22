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

    public function test_partner_properties_keyword_matches_address_detail(): void
    {
        $token = $this->partnerToken();

        $partner = User::query()->where('email', 'partner@gmail.com')->firstOrFail();
        $property = Property::query()->where('user_id', $partner->id)->firstOrFail();

        $uniqueAddress = 'PP015_KEYWORD_ADDR_' . uniqid();
        $property->update([
            'name'           => 'PP015 Generic Property Name',
            'address_detail' => $uniqueAddress,
        ]);

        $response = $this->getJson(
            '/api/v1/partner/properties/searchAll?keyword=' . urlencode($uniqueAddress) . '&per_page=50&with_rooms=0',
            ['Authorization' => 'Bearer ' . $token],
        );

        $response->assertOk();
        $ids = collect($response->json('data.data'))->pluck('id')->map(static fn ($id) => (int) $id)->all();
        $this->assertContains((int) $property->id, $ids);
    }

    public function test_partner_properties_name_filter_does_not_match_address_detail_only(): void
    {
        $token = $this->partnerToken();

        $partner = User::query()->where('email', 'partner@gmail.com')->firstOrFail();
        $property = Property::query()->where('user_id', $partner->id)->firstOrFail();

        $uniqueAddress = 'PP015_NAME_ONLY_ADDR_' . uniqid();
        $property->update([
            'name'           => 'PP015 Distinct Property Title',
            'address_detail' => $uniqueAddress,
        ]);

        $response = $this->getJson(
            '/api/v1/partner/properties/searchAll?name=' . urlencode($uniqueAddress) . '&per_page=50&with_rooms=0',
            ['Authorization' => 'Bearer ' . $token],
        );

        $response->assertOk();
        $ids = collect($response->json('data.data'))->pluck('id')->map(static fn ($id) => (int) $id)->all();
        $this->assertNotContains((int) $property->id, $ids);
    }

    public function test_partner_properties_keyword_with_rent_category_filter(): void
    {
        $token = $this->partnerToken();

        $partner = User::query()->where('email', 'partner@gmail.com')->firstOrFail();
        $property = Property::query()->where('user_id', $partner->id)->firstOrFail();

        $uniqueToken = 'PP015_COMBO_' . uniqid();
        $rentCategory = (int) ($property->rent_category ?: 1);
        $property->update([
            'name'           => $uniqueToken . ' Combo Property',
            'address_detail' => 'Some address',
            'rent_category'  => $rentCategory,
        ]);

        $response = $this->getJson(
            '/api/v1/partner/properties/searchAll?keyword=' . urlencode($uniqueToken)
            . '&rent_category=' . $rentCategory
            . '&per_page=50&with_rooms=0',
            ['Authorization' => 'Bearer ' . $token],
        );

        $response->assertOk();
        $ids = collect($response->json('data.data'))->pluck('id')->map(static fn ($id) => (int) $id)->all();
        $this->assertContains((int) $property->id, $ids);

        $wrongCategory = $rentCategory === 1 ? 2 : 1;
        $excluded = $this->getJson(
            '/api/v1/partner/properties/searchAll?keyword=' . urlencode($uniqueToken)
            . '&rent_category=' . $wrongCategory
            . '&per_page=50&with_rooms=0',
            ['Authorization' => 'Bearer ' . $token],
        );

        $excluded->assertOk();
        $excludedIds = collect($excluded->json('data.data'))->pluck('id')->map(static fn ($id) => (int) $id)->all();
        $this->assertNotContains((int) $property->id, $excludedIds);
    }

    public function test_partner_properties_keyword_validation_rejects_too_long_value(): void
    {
        $token = $this->partnerToken();

        $response = $this->getJson(
            '/api/v1/partner/properties/searchAll?keyword=' . str_repeat('a', 256),
            ['Authorization' => 'Bearer ' . $token],
        );

        $response->assertStatus(422);
    }

    public function test_partner_properties_list_includes_cover_when_requested(): void
    {
        $token = $this->partnerToken();

        $response = $this->getJson(
            '/api/v1/partner/properties/searchAll?page=1&per_page=5&with_rooms=0&include=cover',
            ['Authorization' => 'Bearer ' . $token],
        );

        $response->assertOk();
        $first = $response->json('data.data.0');
        if (is_array($first)) {
            $this->assertArrayHasKey('cover_image_url', $first);
        }
    }

    public function test_partner_properties_list_sort_by_reviews_avg_rating_desc(): void
    {
        $token = $this->partnerToken();

        $response = $this->getJson(
            '/api/v1/partner/properties/searchAll?per_page=20&with_rooms=0&sort[0][field]=reviews_avg_rating&sort[0][order]=desc',
            ['Authorization' => 'Bearer ' . $token],
        );

        $response->assertOk();
    }

    public function test_partner_properties_keyword_does_not_leak_other_partner_properties(): void
    {
        $token = $this->partnerToken();

        $otherProperty = Property::query()
            ->whereHas('user', static fn ($q) => $q->where('email', '!=', 'partner@gmail.com'))
            ->first();

        if ($otherProperty === null) {
            $this->markTestSkipped('No property owned by another user in seed data.');
        }

        $uniqueAddress = 'PP015_OTHER_PARTNER_' . uniqid();
        $otherProperty->update([
            'name'           => 'Other Partner Property',
            'address_detail' => $uniqueAddress,
        ]);

        $response = $this->getJson(
            '/api/v1/partner/properties/searchAll?keyword=' . urlencode($uniqueAddress) . '&per_page=50',
            ['Authorization' => 'Bearer ' . $token],
        );

        $response->assertOk();
        $ids = collect($response->json('data.data'))->pluck('id')->map(static fn ($id) => (int) $id)->all();
        $this->assertNotContains((int) $otherProperty->id, $ids);
    }

    public function test_partner_properties_occupancy_filter_vacant(): void
    {
        $token = $this->partnerToken();

        $partner = User::query()->where('email', 'partner@gmail.com')->firstOrFail();
        $property = Property::query()
            ->where('user_id', $partner->id)
            ->whereHas('rooms', static fn ($q) => $q->where('status', '!=', 0))
            ->first();

        if ($property === null) {
            $this->markTestSkipped('No partner property with active rooms in seed data.');
        }

        $response = $this->getJson(
            '/api/v1/partner/properties/searchAll?occupancy_filter=vacant&id=' . $property->id . '&per_page=10&with_rooms=0',
            ['Authorization' => 'Bearer ' . $token],
        );

        $response->assertOk();
        $ids = collect($response->json('data.data'))->pluck('id')->map(static fn ($id) => (int) $id)->all();
        $this->assertContains((int) $property->id, $ids);
    }

    public function test_partner_properties_occupancy_filter_validation_rejects_invalid(): void
    {
        $token = $this->partnerToken();

        $response = $this->getJson(
            '/api/v1/partner/properties/searchAll?occupancy_filter=invalid_status',
            ['Authorization' => 'Bearer ' . $token],
        );

        $response->assertStatus(422);
    }

    public function test_partner_properties_has_rooms_zero_filter(): void
    {
        $token = $this->partnerToken();

        $partner = User::query()->where('email', 'partner@gmail.com')->firstOrFail();
        $template = Property::query()->where('user_id', $partner->id)->firstOrFail();

        $property = Property::query()->create([
            'user_id'          => $partner->id,
            'province_id'      => $template->province_id,
            'ward_id'          => $template->ward_id,
            'name'             => 'PP015 Empty Property ' . uniqid(),
            'address_detail'   => 'PP015 empty address',
            'property_type_id' => $template->property_type_id,
            'rent_category'    => $template->rent_category ?: 1,
        ]);

        $response = $this->getJson(
            '/api/v1/partner/properties/searchAll?has_rooms=0&id=' . $property->id . '&per_page=10&with_rooms=0',
            ['Authorization' => 'Bearer ' . $token],
        );

        $response->assertOk();
        $ids = collect($response->json('data.data'))->pluck('id')->map(static fn ($id) => (int) $id)->all();
        $this->assertContains((int) $property->id, $ids);

        $excluded = $this->getJson(
            '/api/v1/partner/properties/searchAll?has_rooms=1&id=' . $property->id . '&per_page=10&with_rooms=0',
            ['Authorization' => 'Bearer ' . $token],
        );

        $excluded->assertOk();
        $excludedIds = collect($excluded->json('data.data'))->pluck('id')->map(static fn ($id) => (int) $id)->all();
        $this->assertNotContains((int) $property->id, $excludedIds);
    }

    public function test_partner_properties_min_rating_zero_filters_properties_without_reviews(): void
    {
        $token = $this->partnerToken();

        $partner = User::query()->where('email', 'partner@gmail.com')->firstOrFail();
        $template = Property::query()->where('user_id', $partner->id)->firstOrFail();

        $property = Property::query()->create([
            'user_id'          => $partner->id,
            'province_id'      => $template->province_id,
            'ward_id'          => $template->ward_id,
            'name'             => 'PP015 No Review Property ' . uniqid(),
            'address_detail'   => 'PP015 no review address',
            'property_type_id' => $template->property_type_id,
            'rent_category'    => $template->rent_category ?: 1,
        ]);

        \App\Models\Room::query()->create([
            'property_id' => $property->id,
            'room_number' => 'NR-' . uniqid(),
            'title'       => 'No Review Room',
            'room_type'   => 1,
            'status'      => 1,
            'area'        => 20,
            'people'      => 2,
        ]);

        $response = $this->getJson(
            '/api/v1/partner/properties/searchAll?min_rating=0&id=' . $property->id . '&per_page=10&with_rooms=0',
            ['Authorization' => 'Bearer ' . $token],
        );

        $response->assertOk();
        $ids = collect($response->json('data.data'))->pluck('id')->map(static fn ($id) => (int) $id)->all();
        $this->assertContains((int) $property->id, $ids);
    }

    public function test_partner_properties_occupancy_filter_occupied(): void
    {
        $token = $this->partnerToken();

        $partner = User::query()->where('email', 'partner@gmail.com')->firstOrFail();
        $room = \App\Models\Room::query()
            ->whereHas('property', static fn ($q) => $q->where('user_id', $partner->id))
            ->where('status', '!=', 0)
            ->first();

        if ($room === null) {
            $this->markTestSkipped('No partner room in seed data.');
        }

        $user = User::query()->where('role', 'user')->firstOrFail();
        $price = \App\Models\RoomPrice::query()->where('room_id', $room->id)->first()
            ?? \App\Models\RoomPrice::query()->firstOrFail();

        $today = now()->toDateString();
        \App\Models\Booking::query()->create([
            'room_id'     => $room->id,
            'user_id'     => $user->id,
            'price_id'    => $price->id,
            'start_date'  => $today,
            'end_date'    => now()->addDays(3)->toDateString(),
            'status'      => \App\Enums\BookingStatus::CONFIRMED->value,
            'stay_status' => 'checked_in',
        ]);

        $response = $this->getJson(
            '/api/v1/partner/properties/searchAll?occupancy_filter=occupied&id=' . $room->property_id . '&per_page=10&with_rooms=0',
            ['Authorization' => 'Bearer ' . $token],
        );

        $response->assertOk();
        $ids = collect($response->json('data.data'))->pluck('id')->map(static fn ($id) => (int) $id)->all();
        $this->assertContains((int) $room->property_id, $ids);
    }

    public function test_partner_properties_list_includes_vacancy_fields(): void
    {
        $token = $this->partnerToken();

        $response = $this->getJson(
            '/api/v1/partner/properties/searchAll?page=1&per_page=5&with_rooms=0&include=cover',
            ['Authorization' => 'Bearer ' . $token],
        );

        $response->assertOk();
        $first = $response->json('data.data.0');
        if (! is_array($first)) {
            $this->markTestSkipped('No partner properties in seed data.');
        }

        $this->assertArrayHasKey('vacant_rooms_count', $first);
        $this->assertArrayHasKey('vacancy_rate', $first);
        $this->assertArrayHasKey('province_name', $first);
        $this->assertIsInt($first['vacant_rooms_count']);
        $this->assertGreaterThanOrEqual(0, $first['vacant_rooms_count']);
        $this->assertLessThanOrEqual((int) $first['rooms_count'], $first['vacant_rooms_count']);
    }
}
