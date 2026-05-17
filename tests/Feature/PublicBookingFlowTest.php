<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Jobs\SendBooking;
use App\Models\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

final class PublicBookingFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_user_create_returns_booking_payload(): void
    {
        Queue::fake();

        $room = Room::query()->first();
        $this->assertNotNull($room);

        $start = now()->addMonths(6)->startOfMonth()->addDays(5)->format('Y-m-d');
        $end = now()->addMonths(6)->startOfMonth()->addDays(8)->format('Y-m-d');
        $email = 'public-flow-'.uniqid('', true).'@example.com';

        $response = $this->postJson("/api/v1/bookings/{$room->id}/user-create", [
            'name'        => 'Nguyen Van Test',
            'email'       => $email,
            'phone'       => '0909123456',
            'start_date'  => $start,
            'end_date'    => $end,
            'note'        => 'PHPUnit',
            'service_ids' => [],
        ]);

        $response->assertStatus(201);
        $response->assertJsonPath('status', 'success');
        $response->assertJsonStructure([
            'data' => [
                'booking_id',
                'booking_code',
                'user_id',
                'status',
                'start_date',
                'end_date',
                'room_id',
                'total_amount',
                'room_title',
                'property_address',
            ],
        ]);

        $this->assertIsInt($response->json('data.booking_id'));
        $this->assertMatchesRegularExpression('/^RM-\d{4}-\d{6}$/', (string) $response->json('data.booking_code'));

        Queue::assertPushed(SendBooking::class);
    }

    public function test_public_lookup_returns_booking_when_email_and_code_match(): void
    {
        Queue::fake();

        $room = Room::query()->first();
        $this->assertNotNull($room);

        $start = now()->addMonths(7)->startOfMonth()->addDays(5)->format('Y-m-d');
        $end = now()->addMonths(7)->startOfMonth()->addDays(8)->format('Y-m-d');
        $email = 'lookup-flow-'.uniqid('', true).'@example.com';

        $create = $this->postJson("/api/v1/bookings/{$room->id}/user-create", [
            'name'        => 'Tran Thi Lookup',
            'email'       => $email,
            'phone'       => '0909988776',
            'start_date'  => $start,
            'end_date'    => $end,
            'note'        => 'PHPUnit lookup',
            'service_ids' => [],
        ]);

        $create->assertStatus(201);
        $code = (string) $create->json('data.booking_code');

        $lookup = $this->postJson('/api/v1/bookings/lookup', [
            'email'         => $email,
            'booking_code'  => $code,
        ]);

        $lookup->assertStatus(200);
        $lookup->assertJsonPath('status', 'success');
        $lookup->assertJsonPath('data.booking_code', $code);
        $lookup->assertJsonPath('data.room_id', $room->id);
    }

    public function test_public_lookup_returns_404_when_email_wrong(): void
    {
        Queue::fake();

        $room = Room::query()->first();
        $this->assertNotNull($room);

        $start = now()->addMonths(8)->startOfMonth()->addDays(5)->format('Y-m-d');
        $end = now()->addMonths(8)->startOfMonth()->addDays(8)->format('Y-m-d');
        $email = 'lookup-other-'.uniqid('', true).'@example.com';

        $create = $this->postJson("/api/v1/bookings/{$room->id}/user-create", [
            'name'        => 'Le Van Other',
            'email'       => $email,
            'phone'       => '0909111222',
            'start_date'  => $start,
            'end_date'    => $end,
            'note'        => 'PHPUnit',
            'service_ids' => [],
        ]);
        $create->assertStatus(201);
        $code = (string) $create->json('data.booking_code');

        $lookup = $this->postJson('/api/v1/bookings/lookup', [
            'email'         => 'wrong-email@example.com',
            'booking_code'  => $code,
        ]);

        $lookup->assertStatus(404);
        $lookup->assertJsonPath('status', 'error');
    }
}
