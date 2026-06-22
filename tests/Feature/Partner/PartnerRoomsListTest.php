<?php

declare(strict_types=1);

namespace Tests\Feature\Partner;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Room;
use App\Models\RoomPrice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class PartnerRoomsListTest extends TestCase
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

    public function test_partner_rooms_list_includes_occupancy_status(): void
    {
        $token = $this->partnerToken();

        $response = $this->getJson('/api/v1/partner/rooms/search?page=1&per_page=5', [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertOk();
        $first = $response->json('data.data.0');
        if (! is_array($first)) {
            $this->markTestSkipped('No partner rooms in seed data.');
        }

        $this->assertArrayHasKey('occupancy_status', $first);
        $this->assertContains($first['occupancy_status'], ['vacant', 'occupied', 'maintenance', 'hidden']);
    }

    public function test_partner_rooms_list_occupancy_filter_returns_confirmed_stay_today(): void
    {
        $token = $this->partnerToken();

        $partner = User::query()->where('email', 'partner@gmail.com')->firstOrFail();
        $room = Room::query()
            ->whereHas('property', static fn ($q) => $q->where('user_id', $partner->id))
            ->where('status', '!=', 0)
            ->first();

        if ($room === null) {
            $this->markTestSkipped('No partner room in seed data.');
        }

        $user = User::query()->where('role', 'user')->firstOrFail();
        $price = RoomPrice::query()->where('room_id', $room->id)->first()
            ?? RoomPrice::query()->firstOrFail();

        $today = now()->toDateString();
        Booking::query()->create([
            'room_id'     => $room->id,
            'user_id'     => $user->id,
            'price_id'    => $price->id,
            'start_date'  => $today,
            'end_date'    => now()->addDays(3)->toDateString(),
            'status'      => BookingStatus::CONFIRMED->value,
            'stay_status' => 'pending',
        ]);

        $response = $this->getJson(
            '/api/v1/partner/rooms/search?occupancy=occupied&property_id=' . $room->property_id . '&per_page=50',
            ['Authorization' => 'Bearer ' . $token],
        );

        $response->assertOk();
        $ids = collect($response->json('data.data'))->pluck('id')->map(static fn ($id) => (int) $id)->all();
        $this->assertContains((int) $room->id, $ids);

        $matched = collect($response->json('data.data'))->firstWhere('id', $room->id);
        $this->assertIsArray($matched);
        $this->assertSame('occupied', $matched['occupancy_status']);
    }

    public function test_partner_rooms_occupied_without_check_in_counts_as_occupied_inventory(): void
    {
        $token = $this->partnerToken();

        $partner = User::query()->where('email', 'partner@gmail.com')->firstOrFail();
        $room = Room::query()
            ->whereHas('property', static fn ($q) => $q->where('user_id', $partner->id))
            ->where('status', '!=', 0)
            ->whereDoesntHave('bookings', static function ($q): void {
                $today = now()->toDateString();
                $q->where('status', BookingStatus::CONFIRMED->value)
                    ->where('start_date', '<=', $today)
                    ->where('end_date', '>=', $today);
            })
            ->first();

        if ($room === null) {
            $this->markTestSkipped('No vacant partner room in seed data.');
        }

        $user = User::query()->where('role', 'user')->firstOrFail();
        $price = RoomPrice::query()->where('room_id', $room->id)->first()
            ?? RoomPrice::query()->firstOrFail();

        $today = now()->toDateString();
        Booking::query()->create([
            'room_id'     => $room->id,
            'user_id'     => $user->id,
            'price_id'    => $price->id,
            'start_date'  => $today,
            'end_date'    => now()->addDays(2)->toDateString(),
            'status'      => BookingStatus::CONFIRMED->value,
            'stay_status' => 'pending',
        ]);

        $detail = $this->getJson('/api/v1/partner/rooms/search?property_id=' . $room->property_id . '&per_page=50', [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $detail->assertOk();
        $row = collect($detail->json('data.data'))->firstWhere('id', $room->id);
        $this->assertIsArray($row);
        $this->assertSame('occupied', $row['occupancy_status']);
    }

    public function test_partner_room_detail_includes_contact_fields(): void
    {
        $token = $this->partnerToken();
        $partner = User::query()->where('email', 'partner@gmail.com')->firstOrFail();
        $room = Room::query()
            ->whereHas('property', static fn ($q) => $q->where('user_id', $partner->id))
            ->firstOrFail();

        config([
            'app.support_phone' => '19001234',
            'app.support_email' => 'support@bks.test',
        ]);

        $response = $this->getJson("/api/v1/partner/rooms/{$room->id}", [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertOk();
        $response->assertJsonPath('data.partner_phone', $partner->phone);
        $response->assertJsonPath('data.partner_email', $partner->email);
        $response->assertJsonPath('data.support_phone', '19001234');
        $response->assertJsonPath('data.support_email', 'support@bks.test');
    }

    public function test_partner_can_update_housekeeping_status(): void
    {
        $token = $this->partnerToken();
        $partner = User::query()->where('email', 'partner@gmail.com')->firstOrFail();
        $room = Room::query()
            ->whereHas('property', static fn ($q) => $q->where('user_id', $partner->id))
            ->firstOrFail();

        $response = $this->patchJson("/api/v1/partner/rooms/{$room->id}/housekeeping", [
            'housekeeping_status' => 'dirty',
        ], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertOk();
        $this->assertSame('dirty', $response->json('data.housekeeping_status'));

        $this->assertDatabaseHas('rooms', [
            'id' => $room->id,
            'housekeeping_status' => 'dirty',
        ]);
    }

    public function test_partner_checkout_sets_room_housekeeping_dirty(): void
    {
        $token = $this->partnerToken();
        $partner = User::query()->where('email', 'partner@gmail.com')->firstOrFail();
        $room = Room::query()
            ->whereHas('property', static fn ($q) => $q->where('user_id', $partner->id))
            ->firstOrFail();

        $room->update(['housekeeping_status' => 'clean']);

        $user = User::query()->where('role', 'user')->firstOrFail();
        $price = RoomPrice::query()->where('room_id', $room->id)->first()
            ?? RoomPrice::query()->firstOrFail();

        $booking = Booking::query()->create([
            'room_id'     => $room->id,
            'user_id'     => $user->id,
            'price_id'    => $price->id,
            'start_date'  => now()->subDays(2)->toDateString(),
            'end_date'    => now()->addDay()->toDateString(),
            'status'      => BookingStatus::CONFIRMED->value,
            'stay_status' => 'checked_in',
        ]);

        $response = $this->putJson("/api/v1/partner/bookings/{$booking->id}/check-out", [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('rooms', [
            'id' => $room->id,
            'housekeeping_status' => 'dirty',
        ]);
    }

    public function test_partner_room_config_sync_to_same_type(): void
    {
        $token = $this->partnerToken();
        $partner = User::query()->where('email', 'partner@gmail.com')->firstOrFail();

        $property = \App\Models\Property::query()
            ->where('user_id', $partner->id)
            ->firstOrFail();

        // Let's create two brand new rooms of the same type and property
        $roomA = Room::query()->create([
            'property_id' => $property->id,
            'room_type' => 1,
            'room_number' => 'SYNC-TEST-A',
            'title' => 'Sync Test Room A',
            'status' => true,
            'area' => 30.0,
            'created_by' => $partner->id,
            'updated_by' => $partner->id,
        ]);

        $roomB = Room::query()->create([
            'property_id' => $property->id,
            'room_type' => 1,
            'room_number' => 'SYNC-TEST-B',
            'title' => 'Sync Test Room B',
            'status' => true,
            'area' => 30.0,
            'created_by' => $partner->id,
            'updated_by' => $partner->id,
        ]);

        // Sync a specific price package and amenity to roomA and check if it syncs to roomB
        $payload = [
            'title' => $roomA->title,
            'room_number' => $roomA->room_number,
            'property_id' => $roomA->property_id,
            'room_type' => $roomA->room_type,
            'amenities' => [1, 2], // assuming amenities 1 and 2 exist in seed data
            'services' => [1],    // assuming service 1 exists in seed data
            'prices' => [
                [
                    'price_package_id' => 1,
                    'unit' => 'month',
                    'unit_price' => 5000000,
                    'deposit_amount' => 1000000,
                    'minimum_stay' => 3,
                ]
            ],
            'sync_to_same_type' => true,
        ];

        $response = $this->putJson("/api/v1/partner/rooms/{$roomA->id}", $payload, [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertOk();

        // Verify that roomB now has the same amenities, services, and prices
        $this->assertDatabaseHas('room_amenities', [
            'room_id' => $roomB->id,
            'amenity_id' => 1,
        ]);
        $this->assertDatabaseHas('room_amenities', [
            'room_id' => $roomB->id,
            'amenity_id' => 2,
        ]);
        $this->assertDatabaseHas('room_services', [
            'room_id' => $roomB->id,
            'service_id' => 1,
        ]);
        $this->assertDatabaseHas('room_prices', [
            'room_id' => $roomB->id,
            'price' => 5000000,
        ]);
    }

    public function test_partner_booking_list_includes_contract_id(): void
    {
        $token = $this->partnerToken();
        $partner = User::query()->where('email', 'partner@gmail.com')->firstOrFail();
        $room = Room::query()
            ->whereHas('property', static fn ($q) => $q->where('user_id', $partner->id))
            ->firstOrFail();

        $user = User::query()->where('role', 'user')->firstOrFail();
        $price = RoomPrice::query()->where('room_id', $room->id)->first()
            ?? RoomPrice::query()->firstOrFail();

        $booking = Booking::query()->create([
            'room_id'     => $room->id,
            'user_id'     => $user->id,
            'price_id'    => $price->id,
            'start_date'  => now()->toDateString(),
            'end_date'    => now()->addDays(3)->toDateString(),
            'status'      => BookingStatus::CONFIRMED->value,
            'stay_status' => 'pending',
        ]);

        $contract = \App\Models\Contract::query()->create([
            'booking_id' => $booking->id,
            'title' => 'Test lease contract',
            'content' => 'Contract body for testing',
            'contract_type' => 'LEASE_AGREEMENT',
            'status' => 0,
            'created_by' => $partner->id,
            'updated_by' => $partner->id,
        ]);

        $response = $this->getJson('/api/v1/partner/bookings?room_id=' . $room->id . '&per_page=50', [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertOk();
        $row = collect($response->json('data.data'))->firstWhere('id', $booking->id);
        $this->assertIsArray($row);
        $this->assertSame($contract->id, (int) $row['contract_id']);
    }

    public function test_partner_can_ensure_contract_for_confirmed_booking_without_one(): void
    {
        $token = $this->partnerToken();
        $partner = User::query()->where('email', 'partner@gmail.com')->firstOrFail();
        $room = Room::query()
            ->whereHas('property', static fn ($q) => $q->where('user_id', $partner->id))
            ->firstOrFail();

        $user = User::query()->where('role', 'user')->firstOrFail();
        $price = RoomPrice::query()->where('room_id', $room->id)->first()
            ?? RoomPrice::query()->firstOrFail();

        $booking = Booking::query()->create([
            'room_id'     => $room->id,
            'user_id'     => $user->id,
            'price_id'    => $price->id,
            'start_date'  => now()->toDateString(),
            'end_date'    => now()->addDays(23)->toDateString(),
            'status'      => BookingStatus::CONFIRMED->value,
            'stay_status' => 'pending',
        ]);

        $this->assertSame(0, $booking->contracts()->count());

        $response = $this->postJson('/api/v1/partner/bookings/' . $booking->id . '/ensure-contract', [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertOk();
        $contractId = (int) $response->json('data.id');
        $this->assertGreaterThan(0, $contractId);
        $this->assertSame(1, $booking->fresh()?->contracts()->count());
    }

    public function test_partner_room_apply_to_all_rooms_syncs_amenities(): void
    {
        $token = $this->partnerToken();
        $partner = User::query()->where('email', 'partner@gmail.com')->firstOrFail();

        $property = \App\Models\Property::query()
            ->where('user_id', $partner->id)
            ->firstOrFail();

        $roomA = Room::query()->create([
            'property_id' => $property->id,
            'room_type' => 1,
            'room_number' => 'APPLY-ALL-A',
            'title' => 'Apply All Room A',
            'status' => true,
            'area' => 30.0,
            'created_by' => $partner->id,
            'updated_by' => $partner->id,
        ]);

        $roomB = Room::query()->create([
            'property_id' => $property->id,
            'room_type' => 2,
            'room_number' => 'APPLY-ALL-B',
            'title' => 'Apply All Room B',
            'status' => true,
            'area' => 35.0,
            'created_by' => $partner->id,
            'updated_by' => $partner->id,
        ]);

        $payload = [
            'title' => $roomA->title,
            'room_number' => $roomA->room_number,
            'property_id' => $roomA->property_id,
            'room_type' => $roomA->room_type,
            'amenities' => [1, 2],
            'services' => [1],
            'prices' => [
                [
                    'price_package_id' => 1,
                    'unit' => 'month',
                    'unit_price' => 4500000,
                    'deposit_amount' => 500000,
                    'minimum_stay' => 1,
                ],
            ],
            'apply_to_all_rooms' => true,
        ];

        $response = $this->putJson("/api/v1/partner/rooms/{$roomA->id}", $payload, [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertOk();

        $this->assertDatabaseHas('room_amenities', [
            'room_id' => $roomB->id,
            'amenity_id' => 1,
        ]);
        $this->assertDatabaseHas('room_services', [
            'room_id' => $roomB->id,
            'service_id' => 1,
        ]);
    }
}
