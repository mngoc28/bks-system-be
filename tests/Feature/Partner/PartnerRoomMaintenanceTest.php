<?php

declare(strict_types=1);

namespace Tests\Feature\Partner;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Models\Property;
use App\Models\Room;
use App\Models\RoomPrice;
use App\Models\RoomMaintenance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class PartnerRoomMaintenanceTest extends TestCase
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

    public function test_partner_can_list_maintenances_with_pagination(): void
    {
        $token = $this->partnerToken();

        $response = $this->getJson('/api/v1/partner/room-maintenances?page=1&per_page=5', [
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
    }

    public function test_partner_can_create_and_update_maintenance_lifecycle(): void
    {
        $token = $this->partnerToken();
        $partner = User::query()->where('email', 'partner@gmail.com')->firstOrFail();
        $room = Room::query()
            ->whereHas('property', static fn ($q) => $q->where('user_id', $partner->id))
            ->first();

        if ($room === null) {
            $this->markTestSkipped('No partner room in seed data.');
        }

        $start = Carbon::now()->addDay()->startOfDay();
        $end = (clone $start)->addDays(2);

        $createResponse = $this->postJson('/api/v1/partner/room-maintenances', [
            'room_id'          => $room->id,
            'title'            => 'Test bảo trì PHPUnit',
            'description'      => 'Kiểm thử lifecycle',
            'maintenance_type' => 'emergency',
            'start_time'       => $start->toDateTimeString(),
            'end_time'         => $end->toDateTimeString(),
            'block_calendar'   => false,
        ], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $createResponse->assertOk();
        $maintenanceId = (int) $createResponse->json('data.id');
        $this->assertGreaterThan(0, $maintenanceId);
        $this->assertSame('planned', $createResponse->json('data.status'));

        $acceptResponse = $this->patchJson('/api/v1/partner/room-maintenances/' . $maintenanceId, [
            'status' => 'in_progress',
        ], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $acceptResponse->assertOk();
        $this->assertSame('in_progress', $acceptResponse->json('data.status'));

        $completeResponse = $this->patchJson('/api/v1/partner/room-maintenances/' . $maintenanceId, [
            'status' => 'completed',
        ], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $completeResponse->assertOk();
        $this->assertSame('completed', $completeResponse->json('data.status'));

        $this->assertDatabaseHas('room_maintenances', [
            'id'     => $maintenanceId,
            'status' => RoomMaintenance::STATUS_COMPLETED,
        ]);
    }

    public function test_partner_cannot_view_other_partner_maintenance(): void
    {
        $this->seed();

        $owner = User::query()->where('email', 'partner@gmail.com')->firstOrFail();
        $otherPartner = User::query()->create([
            'name'     => 'Other Partner',
            'role'     => 'partner',
            'email'    => 'other-partner-maintenance@test.local',
            'password' => bcrypt('123456a!'),
            'phone'    => '0900000099',
        ]);

        $property = Property::query()->where('user_id', $otherPartner->id)->first();
        if ($property === null) {
            $property = Property::query()->create([
                'user_id'          => $otherPartner->id,
                'name'             => 'Other Property',
                'address_detail'   => 'Test',
                'province_id'      => 1,
                'ward_id'          => 1,
                'property_type_id' => 1,
                'rent_category'    => 1,
            ]);
        }

        $room = Room::query()->where('property_id', $property->id)->first();
        if ($room === null) {
            $room = Room::query()->create([
                'property_id' => $property->id,
                'title'       => 'Other Room',
                'room_number' => 'OR-1',
                'area'        => 20,
                'status'      => 1,
            ]);
        }

        $maintenance = RoomMaintenance::query()->create([
            'room_id'          => $room->id,
            'property_id'      => $property->id,
            'title'            => 'Private maintenance',
            'maintenance_type' => RoomMaintenance::TYPE_SCHEDULED,
            'start_time'       => Carbon::now(),
            'end_time'         => Carbon::now()->addDay(),
            'status'           => RoomMaintenance::STATUS_PLANNED,
            'created_by'       => $otherPartner->id,
            'block_calendar'   => false,
            'source'           => RoomMaintenance::SOURCE_PARTNER,
        ]);

        $login = $this->postJson('/api/v1/partner/auth/login', [
            'email'    => 'partner@gmail.com',
            'password' => '123456a!',
        ]);
        $token = (string) $login->json('data.token');

        $response = $this->getJson('/api/v1/partner/room-maintenances/' . $maintenance->id, [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertNotFound();
        $this->assertSame('MAINTENANCE_NOT_FOUND', $response->json('code'));
    }

    public function test_conflict_preview_returns_overlapping_booking(): void
    {
        $token = $this->partnerToken();
        $partner = User::query()->where('email', 'partner@gmail.com')->firstOrFail();
        $room = Room::query()
            ->whereHas('property', static fn ($q) => $q->where('user_id', $partner->id))
            ->first();

        if ($room === null) {
            $this->markTestSkipped('No partner room in seed data.');
        }

        $user = User::query()->where('role', 'user')->firstOrFail();
        $price = RoomPrice::query()->where('room_id', $room->id)->first()
            ?? RoomPrice::query()->firstOrFail();

        Booking::query()->create([
            'room_id'     => $room->id,
            'user_id'     => $user->id,
            'price_id'    => $price->id,
            'start_date'  => '2099-09-10',
            'end_date'    => '2099-09-14',
            'status'      => BookingStatus::CONFIRMED->value,
            'stay_status' => 'pending',
        ]);

        $response = $this->getJson(
            '/api/v1/partner/room-maintenances/conflict-preview?'
            . http_build_query([
                'room_id'    => $room->id,
                'start_date' => '2099-09-11',
                'end_date'   => '2099-09-13',
            ]),
            ['Authorization' => 'Bearer ' . $token],
        );

        $response->assertOk();
        $response->assertJsonPath('data.has_conflict', true);
        $response->assertJsonStructure([
            'data' => [
                'has_conflict',
                'bookings',
                'blocks',
                'current_stay',
            ],
        ]);
        $this->assertNotEmpty($response->json('data.bookings'));
    }

    public function test_conflict_preview_returns_current_stay_when_checked_in(): void
    {
        $token = $this->partnerToken();
        $partner = User::query()->where('email', 'partner@gmail.com')->firstOrFail();
        $room = Room::query()
            ->whereHas('property', static fn ($q) => $q->where('user_id', $partner->id))
            ->first();

        if ($room === null) {
            $this->markTestSkipped('No partner room in seed data.');
        }

        $user = User::query()->where('role', 'user')->firstOrFail();
        $price = RoomPrice::query()->where('room_id', $room->id)->first()
            ?? RoomPrice::query()->firstOrFail();

        $today = Carbon::now()->toDateString();
        $checkout = Carbon::now()->addDays(2)->toDateString();

        Booking::query()->create([
            'room_id'     => $room->id,
            'user_id'     => $user->id,
            'price_id'    => $price->id,
            'start_date'  => $today,
            'end_date'    => $checkout,
            'status'      => BookingStatus::CONFIRMED->value,
            'stay_status' => 'checked_in',
        ]);

        $response = $this->getJson(
            '/api/v1/partner/room-maintenances/conflict-preview?'
            . http_build_query([
                'room_id'    => $room->id,
                'start_date' => Carbon::now()->addDays(10)->toDateString(),
                'end_date'   => Carbon::now()->addDays(12)->toDateString(),
            ]),
            ['Authorization' => 'Bearer ' . $token],
        );

        $response->assertOk();
        $response->assertJsonPath('data.current_stay.stay_status', 'checked_in');
        $this->assertNotNull($response->json('data.current_stay.booking_id'));
    }

    public function test_conflict_preview_denies_other_partner_room(): void
    {
        $this->seed();

        $otherPartner = User::query()->create([
            'name'     => 'Preview Other Partner',
            'role'     => 'partner',
            'email'    => 'preview-other-partner@test.local',
            'password' => bcrypt('123456a!'),
            'phone'    => '0900000088',
        ]);

        $property = Property::query()->create([
            'user_id'          => $otherPartner->id,
            'name'             => 'Preview Other Property',
            'address_detail'   => 'Test',
            'province_id'      => 1,
            'ward_id'          => 1,
            'property_type_id' => 1,
            'rent_category'    => 1,
        ]);

        $room = Room::query()->create([
            'property_id' => $property->id,
            'title'       => 'Preview Other Room',
            'room_number' => 'POR-1',
            'area'        => 20,
            'status'      => 1,
        ]);

        $login = $this->postJson('/api/v1/partner/auth/login', [
            'email'    => 'partner@gmail.com',
            'password' => '123456a!',
        ]);
        $token = (string) $login->json('data.token');

        $response = $this->getJson(
            '/api/v1/partner/room-maintenances/conflict-preview?'
            . http_build_query([
                'room_id'    => $room->id,
                'start_date' => '2099-10-01',
                'end_date'   => '2099-10-03',
            ]),
            ['Authorization' => 'Bearer ' . $token],
        );

        $response->assertNotFound();
        $this->assertSame('MAINTENANCE_NOT_FOUND', $response->json('code'));
    }
}
