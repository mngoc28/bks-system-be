<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\BookingDeposit;
use App\Models\Contract;
use App\Models\Room;
use App\Models\RoomPrice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class DepositFlowTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Seed the database before each test.
     *
     * @var bool
     */
    protected bool $seed = true;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        \DB::table('booking_deposits')->truncate();
    }

    private function getPartnerToken(): string
    {
        $response = $this->postJson('/api/v1/partner/auth/login', [
            'email'    => 'partner@gmail.com',
            'password' => '123456a!',
        ]);

        $response->assertOk();
        return (string) $response->json('data.token');
    }

    private function getUserToken(): string
    {
        $response = $this->postJson('/api/v1/stay/auth/login', [
            'email'    => 'user@gmail.com',
            'password' => '123456a!',
        ]);

        $response->assertOk();
        return (string) $response->json('data.token');
    }

    public function test_check_in_gate_blocks_check_in_if_deposit_unconfirmed(): void
    {
        $token = $this->getPartnerToken();

        $room = Room::query()->firstOrFail();
        $user = User::query()->where('role', 'user')->firstOrFail();
        $price = RoomPrice::query()->where('room_id', $room->id)->first() ?? RoomPrice::query()->firstOrFail();

        $booking = Booking::create([
            'room_id'        => $room->id,
            'user_id'        => $user->id,
            'price_id'       => $price->id,
            'start_date'     => now()->addDays(2)->format('Y-m-d'),
            'end_date'       => now()->addDays(5)->format('Y-m-d'),
            'status'         => 1, // CONFIRMED
            'stay_status'    => 'pending',
            'deposit_amount' => 100000.00,
            'deposit_status' => 'pending',
        ]);

        BookingDeposit::create([
            'booking_id' => $booking->id,
            'amount'     => 100000.00,
            'status'     => 'pending',
        ]);

        $response = $this->putJson("/api/v1/partner/bookings/{$booking->id}/check-in", [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('code', 'CHECKIN_GATE_FAILED');
    }

    public function test_check_in_gate_allows_check_in_if_deposit_confirmed(): void
    {
        $token = $this->getPartnerToken();

        $room = Room::query()->firstOrFail();
        $user = User::query()->where('role', 'user')->firstOrFail();
        $price = RoomPrice::query()->where('room_id', $room->id)->first() ?? RoomPrice::query()->firstOrFail();

        $room->update(['status' => true]);

        $booking = Booking::create([
            'room_id'        => $room->id,
            'user_id'        => $user->id,
            'price_id'       => $price->id,
            'start_date'     => now()->addDays(2)->format('Y-m-d'),
            'end_date'       => now()->addDays(5)->format('Y-m-d'),
            'status'         => 1, // CONFIRMED
            'stay_status'    => 'pending',
            'deposit_amount' => 100000.00,
            'deposit_status' => 'confirmed_by_partner',
        ]);

        BookingDeposit::create([
            'booking_id' => $booking->id,
            'amount'     => 100000.00,
            'status'     => 'confirmed_by_partner',
        ]);

        $response = $this->putJson("/api/v1/partner/bookings/{$booking->id}/check-in", [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertOk();
        $this->assertSame('checked_in', $booking->fresh()->stay_status);
    }

    public function test_partner_can_manually_confirm_deposit(): void
    {
        $token = $this->getPartnerToken();

        $room = Room::query()->firstOrFail();
        $user = User::query()->where('role', 'user')->firstOrFail();
        $price = RoomPrice::query()->where('room_id', $room->id)->where('unit', 'night')->first() ?? RoomPrice::query()->where('unit', 'night')->firstOrFail();

        $booking = Booking::create([
            'room_id'        => $room->id,
            'user_id'        => $user->id,
            'price_id'       => $price->id,
            'start_date'     => now()->addDays(2)->format('Y-m-d'),
            'end_date'       => now()->addDays(5)->format('Y-m-d'),
            'status'         => 1,
            'stay_status'    => 'pending',
            'deposit_amount' => 150000.00,
            'deposit_status' => 'payment_submitted',
        ]);

        $deposit = BookingDeposit::create([
            'booking_id' => $booking->id,
            'amount'     => 150000.00,
            'status'     => 'payment_submitted',
            'receipt_path' => 'http://example.com/receipt.jpg',
        ]);

        $response = $this->postJson("/api/v1/partner/bookings/{$booking->id}/confirm-deposit", [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertOk();
        $this->assertSame('confirmed_by_partner', $booking->fresh()->deposit_status);
        $this->assertSame('confirmed_by_partner', $deposit->fresh()->status);
    }

    public function test_user_can_submit_deposit_receipt_stay_portal(): void
    {
        $token = $this->getUserToken();

        $room = Room::query()->firstOrFail();
        $user = User::query()->where('email', 'user@gmail.com')->firstOrFail();
        $price = RoomPrice::query()->where('room_id', $room->id)->where('unit', 'night')->first()
            ?? RoomPrice::query()->where('unit', 'night')->firstOrFail();

        $booking = Booking::create([
            'room_id'        => $room->id,
            'user_id'        => $user->id,
            'price_id'       => $price->id,
            'start_date'     => now()->addDays(2)->format('Y-m-d'),
            'end_date'       => now()->addDays(5)->format('Y-m-d'),
            'status'         => 1,
            'stay_status'    => 'pending',
            'deposit_amount' => 200000.00,
            'deposit_status' => 'pending',
        ]);

        $deposit = BookingDeposit::create([
            'booking_id' => $booking->id,
            'amount'     => 200000.00,
            'status'     => 'pending',
        ]);

        $response = $this->postJson("/api/v1/stay/bookings/{$booking->id}/submit-receipt", [
            'receipt_path' => 'https://cloudinary.com/receipt_123.jpg',
        ], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertOk();
        $this->assertSame('payment_submitted', $booking->fresh()->deposit_status);
        $this->assertSame('payment_submitted', $deposit->fresh()->status);
        $this->assertSame('https://cloudinary.com/receipt_123.jpg', $deposit->fresh()->receipt_path);
    }

    public function test_long_term_deposit_receipt_blocked_until_lease_signed(): void
    {
        $token = $this->getUserToken();
        $partner = User::query()->where('email', 'partner@gmail.com')->firstOrFail();

        $room = Room::query()->firstOrFail();
        $user = User::query()->where('email', 'user@gmail.com')->firstOrFail();
        $price = RoomPrice::query()->where('unit', 'month')->first()
            ?? RoomPrice::query()->firstOrFail();

        $booking = Booking::create([
            'room_id'        => $room->id,
            'user_id'        => $user->id,
            'price_id'       => $price->id,
            'start_date'     => now()->addDays(2)->format('Y-m-d'),
            'end_date'       => now()->addDays(62)->format('Y-m-d'),
            'status'         => 1,
            'stay_status'    => 'pending',
            'deposit_amount' => 5000000.00,
            'deposit_status' => 'pending',
        ]);

        BookingDeposit::create([
            'booking_id' => $booking->id,
            'amount'     => 5000000.00,
            'status'     => 'pending',
        ]);

        Contract::create([
            'booking_id'    => $booking->id,
            'title'         => 'Hợp đồng thuê dài hạn',
            'content'       => 'Nội dung hợp đồng',
            'contract_type' => 'LEASE_AGREEMENT',
            'status'        => 0,
            'created_by'    => $partner->id,
            'updated_by'    => $partner->id,
        ]);

        $response = $this->postJson("/api/v1/stay/bookings/{$booking->id}/submit-receipt", [
            'receipt_path' => 'https://cloudinary.com/receipt_blocked.jpg',
        ], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(400);
        $this->assertSame('pending', $booking->fresh()->deposit_status);
    }
}
