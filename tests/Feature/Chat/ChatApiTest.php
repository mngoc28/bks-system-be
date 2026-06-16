<?php

declare(strict_types=1);

namespace Tests\Feature\Chat;

use App\Models\Booking;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\Room;
use App\Models\User;
use App\Services\ChatService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

final class ChatApiTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    private function partnerToken(): string
    {
        $response = $this->postJson('/api/v1/partner/auth/login', [
            'email'    => 'partner@gmail.com',
            'password' => '123456a!',
        ]);

        $response->assertOk();

        return (string) $response->json('data.token');
    }

    private function stayToken(): string
    {
        $response = $this->postJson('/api/v1/stay/auth/login', [
            'email'    => 'user@gmail.com',
            'password' => '123456a!',
        ]);

        $response->assertOk();

        return (string) $response->json('data.token');
    }

    private function createConversation(): Conversation
    {
        $guest = User::query()->where('email', 'user@gmail.com')->firstOrFail();
        $partner = User::query()->where('email', 'partner@gmail.com')->firstOrFail();
        $room = Room::query()->firstOrFail();
        $booking = Booking::query()->create([
            'user_id'    => $guest->id,
            'room_id'    => $room->id,
            'price_id'   => $room->prices()->value('id') ?? 1,
            'start_date' => now()->addDay()->toDateString(),
            'end_date'   => now()->addDays(2)->toDateString(),
            'status'     => 0,
            'created_by' => $guest->id,
        ]);

        return app(ChatService::class)->bootstrapFromBooking($booking);
    }

    public function test_partner_can_list_and_send_messages_in_own_conversation(): void
    {
        $conversation = $this->createConversation();
        $token = $this->partnerToken();

        $listResponse = $this->getJson('/api/v1/partner/chat', [
            'Authorization' => 'Bearer ' . $token,
        ]);
        $listResponse->assertOk();
        $listResponse->assertJsonFragment(['id' => $conversation->id]);

        $sendResponse = $this->postJson('/api/v1/partner/chat', [
            'conversation_id' => $conversation->id,
            'content'         => 'Xin chào khách',
        ], [
            'Authorization' => 'Bearer ' . $token,
        ]);
        $sendResponse->assertOk();
        $sendResponse->assertJsonPath('data.content', 'Xin chào khách');

        $this->assertDatabaseHas('messages', [
            'conversation_id' => $conversation->id,
            'content'         => 'Xin chào khách',
        ]);
    }

    public function test_stay_user_can_list_and_send_messages_in_own_conversation(): void
    {
        $conversation = $this->createConversation();
        $token = $this->stayToken();

        $listResponse = $this->getJson('/api/v1/stay/chat', [
            'Authorization' => 'Bearer ' . $token,
        ]);
        $listResponse->assertOk();
        $listResponse->assertJsonFragment(['id' => $conversation->id]);

        $sendResponse = $this->postJson('/api/v1/stay/chat', [
            'conversation_id' => $conversation->id,
            'content'         => 'Chào chủ nhà',
        ], [
            'Authorization' => 'Bearer ' . $token,
        ]);
        $sendResponse->assertOk();
        $sendResponse->assertJsonPath('data.content', 'Chào chủ nhà');
    }

    public function test_partner_cannot_read_foreign_conversation(): void
    {
        $conversation = $this->createConversation();
        $otherPartner = User::query()->where('role', 'partner')->where('email', '!=', 'partner@gmail.com')->first();
        if ($otherPartner === null) {
            $otherPartner = User::query()->create([
                'name'              => 'Other Partner',
                'email'             => 'other-partner-chat@test.local',
                'password'          => Hash::make('123456a!'),
                'role'              => 'partner',
                'is_email_verified' => true,
                'status'            => '1',
            ]);
        }

        $conversation->update(['partner_id' => $otherPartner->id]);
        $token = $this->partnerToken();

        $response = $this->getJson('/api/v1/partner/chat/' . $conversation->id, [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertForbidden();
    }

    public function test_stay_user_cannot_send_message_to_foreign_conversation(): void
    {
        $guest = User::query()->where('email', 'user@gmail.com')->firstOrFail();
        $partner = User::query()->where('email', 'partner@gmail.com')->firstOrFail();
        $otherGuest = User::query()->where('role', 'user')->where('email', '!=', 'user@gmail.com')->first();
        if ($otherGuest === null) {
            $otherGuest = User::query()->create([
                'name'              => 'Other Guest',
                'email'             => 'other-guest-chat@test.local',
                'password'          => Hash::make('123456a!'),
                'role'              => 'user',
                'is_email_verified' => true,
                'status'            => '1',
            ]);
        }

        $conversation = Conversation::query()->create([
            'user_id'    => $otherGuest->id,
            'partner_id' => $partner->id,
            'booking_id' => null,
        ]);

        Message::query()->create([
            'conversation_id' => $conversation->id,
            'sender_id'       => $partner->id,
            'content'         => 'seed',
        ]);

        $token = $this->stayToken();

        $response = $this->postJson('/api/v1/stay/chat', [
            'conversation_id' => $conversation->id,
            'content'         => 'Tin nhắn trái phép',
        ], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('messages', [
            'conversation_id' => $conversation->id,
            'content'         => 'Tin nhắn trái phép',
        ]);
    }

    public function test_global_conversation_is_reused_for_same_user_partner_pair(): void
    {
        $guest = User::query()->where('email', 'user@gmail.com')->firstOrFail();
        $partner = User::query()->where('email', 'partner@gmail.com')->firstOrFail();
        $room = Room::query()->firstOrFail();
        $priceId = $room->prices()->value('id') ?? 1;

        $bookingA = Booking::query()->create([
            'user_id'    => $guest->id,
            'room_id'    => $room->id,
            'price_id'   => $priceId,
            'start_date' => now()->addDay()->toDateString(),
            'end_date'   => now()->addDays(2)->toDateString(),
            'status'     => 0,
            'created_by' => $guest->id,
        ]);
        $bookingB = Booking::query()->create([
            'user_id'    => $guest->id,
            'room_id'    => $room->id,
            'price_id'   => $priceId,
            'start_date' => now()->addDays(3)->toDateString(),
            'end_date'   => now()->addDays(4)->toDateString(),
            'status'     => 0,
            'created_by' => $guest->id,
        ]);

        $chatService = app(ChatService::class);
        $first = $chatService->bootstrapFromBooking($bookingA);
        $second = $chatService->bootstrapFromBooking($bookingB);

        $this->assertNotNull($first);
        $this->assertSame($first?->id, $second?->id);
        $this->assertSame(1, Conversation::query()->where('user_id', $guest->id)->where('partner_id', $partner->id)->count());
    }

    public function test_empty_message_is_rejected(): void
    {
        $conversation = $this->createConversation();
        $token = $this->stayToken();

        $response = $this->postJson('/api/v1/stay/chat', [
            'conversation_id' => $conversation->id,
            'content'         => '   ',
        ], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(422);
    }
}
