<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Room;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class StayLocalBookingSyncTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    private function loginStayUserToken(): string
    {
        $response = $this->postJson('/api/v1/stay/auth/login', [
            'email'    => 'user@gmail.com',
            'password' => '123456a!',
        ]);
        $response->assertStatus(200);

        return (string) $response->json('data.token');
    }

    private static function fingerprintFor(User $user, int $roomId, string $start, string $end): string
    {
        $email = strtolower(trim((string) $user->email));
        $payload = $roomId . '|' . $start . '|' . $end . '|' . $email;

        return hash('sha256', $payload);
    }

    public function test_sync_local_links_existing_booking_by_slot_and_sets_fingerprint(): void
    {
        $token = $this->loginStayUserToken();
        $user  = User::query()->where('email', 'user@gmail.com')->firstOrFail();
        $room  = Room::query()->firstOrFail();

        $start = Carbon::now()->addYear()->startOfMonth()->addDays(10)->format('Y-m-d');
        $end   = Carbon::now()->addYear()->startOfMonth()->addDays(14)->format('Y-m-d');

        $priceId = (int) (Booking::query()->where('room_id', $room->id)->value('price_id')
            ?? DB::table('room_prices')->where('room_id', $room->id)->value('id'));

        $this->assertGreaterThan(0, $priceId);

        $existing = Booking::query()->create([
            'user_id'    => $user->id,
            'room_id'    => $room->id,
            'price_id'   => $priceId,
            'start_date' => $start,
            'end_date'   => $end,
            'status'     => 0,
            'note'       => 'PHPUnit slot seed',
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $fp = self::fingerprintFor($user, (int) $room->id, $start, $end);

        $payload = [
            'items' => [
                [
                    'local_id'    => 'local-phpunit-1',
                    'fingerprint' => $fp,
                    'room_id'     => $room->id,
                    'start_date'  => $start,
                    'end_date'    => $end,
                    'email'       => $user->email,
                ],
            ],
        ];

        $sync = $this->postJson('/api/v1/stay/bookings/sync-local', $payload, [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $sync->assertStatus(200);
        $sync->assertJsonPath('status', 'success');
        $sync->assertJsonPath('data.mapped.0.action', 'linked');
        $sync->assertJsonPath('data.mapped.0.server_booking_id', $existing->id);

        $existing->refresh();
        $this->assertSame($fp, (string) $existing->client_fingerprint);
        $this->assertSame('local-phpunit-1', (string) $existing->client_local_id);
    }

    public function test_sync_local_second_item_same_fingerprint_in_batch_is_linked(): void
    {
        $token = $this->loginStayUserToken();
        $user  = User::query()->where('email', 'user@gmail.com')->firstOrFail();
        $room  = Room::query()->firstOrFail();

        $start = Carbon::now()->addYears(2)->startOfMonth()->addDays(3)->format('Y-m-d');
        $end   = Carbon::now()->addYears(2)->startOfMonth()->addDays(7)->format('Y-m-d');

        $fp = self::fingerprintFor($user, (int) $room->id, $start, $end);

        $payload = [
            'items' => [
                [
                    'local_id'    => 'dup-a',
                    'fingerprint' => $fp,
                    'room_id'     => $room->id,
                    'start_date'  => $start,
                    'end_date'    => $end,
                ],
                [
                    'local_id'    => 'dup-b',
                    'fingerprint' => $fp,
                    'room_id'     => $room->id,
                    'start_date'  => $start,
                    'end_date'    => $end,
                ],
            ],
        ];

        $sync = $this->postJson('/api/v1/stay/bookings/sync-local', $payload, [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $sync->assertStatus(200);
        $this->assertSame('created', $sync->json('data.mapped.0.action'));
        $this->assertSame('linked', $sync->json('data.mapped.1.action'));
        $this->assertSame($sync->json('data.mapped.0.server_booking_id'), $sync->json('data.mapped.1.server_booking_id'));

        $count = Booking::query()
            ->where('user_id', $user->id)
            ->where('room_id', $room->id)
            ->whereDate('start_date', $start)
            ->whereDate('end_date', $end)
            ->count();
        $this->assertSame(1, $count);
    }
}
