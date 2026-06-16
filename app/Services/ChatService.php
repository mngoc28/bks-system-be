<?php

declare(strict_types=1);

namespace App\Services;

use App\Events\MessageSent;
use App\Models\Booking;
use App\Models\Conversation;
use App\Models\Message;
use Exception;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class ChatService
{
    /**
     * @deprecated Use getOrCreateGlobalConversation() for new code.
     */
    public function getOrCreateConversation(int $userId, int $partnerId, ?int $bookingId = null): Conversation
    {
        return $this->getOrCreateGlobalConversation($userId, $partnerId, $bookingId);
    }

    public function getOrCreateGlobalConversation(int $userId, int $partnerId, ?int $bookingId = null): Conversation
    {
        $conversation = Conversation::query()
            ->where('user_id', $userId)
            ->where('partner_id', $partnerId)
            ->first();

        if ($conversation !== null) {
            if ($bookingId !== null && $conversation->booking_id === null) {
                $conversation->update(['booking_id' => $bookingId]);
            }

            return $conversation->fresh();
        }

        return Conversation::query()->create([
            'user_id'    => $userId,
            'partner_id' => $partnerId,
            'booking_id' => $bookingId,
        ]);
    }

    public function bootstrapFromBooking(Booking $booking): ?Conversation
    {
        $booking->loadMissing(['user', 'room.property']);

        $userId = (int) ($booking->user_id ?? 0);
        $partnerId = (int) ($booking->room?->property?->user_id ?? 0);

        if ($userId <= 0 || $partnerId <= 0) {
            return null;
        }

        return $this->getOrCreateGlobalConversation($userId, $partnerId, (int) $booking->id);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getConversationsForParticipant(int $actorId): array
    {
        $conversations = Conversation::query()
            ->where(static function ($query) use ($actorId): void {
                $query->where('user_id', $actorId)
                    ->orWhere('partner_id', $actorId);
            })
            ->with([
                'guest',
                'partner.partnerInfo',
                'booking.room.property',
                'messages' => static function ($query): void {
                    $query->latest()->limit(1);
                },
            ])
            ->orderByDesc('last_message_at')
            ->orderByDesc('updated_at')
            ->get();

        return $conversations
            ->map(fn (Conversation $conversation): array => $this->formatConversation($conversation, $actorId))
            ->values()
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getMessagesForParticipant(int $conversationId, int $actorId, int $limit = 50): array
    {
        $conversation = $this->findConversationForParticipant($conversationId, $actorId);

        Message::query()
            ->where('conversation_id', $conversation->id)
            ->where('sender_id', '!=', $actorId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        $messages = Message::query()
            ->where('conversation_id', $conversation->id)
            ->with('sender')
            ->orderBy('created_at', 'asc')
            ->take($limit)
            ->get();

        return $messages
            ->map(fn (Message $message): array => $this->formatMessage($message))
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function sendMessage(int $conversationId, int $senderId, string $content, ?array $metadata = null): array
    {
        $this->findConversationForParticipant($conversationId, $senderId);

        DB::beginTransaction();

        try {
            $message = Message::query()->create([
                'conversation_id' => $conversationId,
                'sender_id'       => $senderId,
                'content'         => trim($content),
                'metadata'        => $metadata,
            ]);

            Conversation::query()
                ->whereKey($conversationId)
                ->update(['last_message_at' => now()]);

            DB::commit();

            $message->load('sender');
            broadcast(new MessageSent($message))->toOthers();

            return $this->formatMessage($message);
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function findConversationForParticipant(int $conversationId, int $actorId): Conversation
    {
        $conversation = Conversation::query()->find($conversationId);

        if ($conversation === null) {
            throw new NotFoundHttpException('Conversation not found');
        }

        if (! $this->isParticipant($conversation, $actorId)) {
            throw new AccessDeniedHttpException('Unauthorized conversation access');
        }

        return $conversation;
    }

    private function isParticipant(Conversation $conversation, int $actorId): bool
    {
        return (int) $conversation->user_id === $actorId
            || (int) $conversation->partner_id === $actorId;
    }

    /**
     * @return array<string, mixed>
     */
    private function formatConversation(Conversation $conversation, int $actorId): array
    {
        $lastMessage = $conversation->messages->first();
        $isPartnerView = (int) $conversation->partner_id === $actorId;
        $counterparty = $isPartnerView ? $conversation->guest : $conversation->partner;
        $companyName = $conversation->partner?->partnerInfo?->company_name;

        $room = $conversation->booking?->room;
        $roomName = $room?->title;
        $propertyName = $room?->property?->name;

        return [
            'id'            => $conversation->id,
            'booking_id'    => $conversation->booking_id,
            'last_message'  => $lastMessage?->content,
            'updated_at'    => ($conversation->last_message_at ?? $conversation->updated_at)?->toDateTimeString(),
            'unread_count'  => $this->countUnreadMessages($conversation->id, $actorId),
            'company_name'  => $companyName,
            'room_name'     => $roomName,
            'property_name' => $propertyName,
            'booking'       => $conversation->booking ? [
                'id'   => $conversation->booking->id,
                'room' => $conversation->booking->room ? [
                    'name' => $conversation->booking->room->title ?? '',
                ] : null,
                'user' => $conversation->guest ? [
                    'id'     => $conversation->guest->id,
                    'name'   => $conversation->guest->name,
                    'avatar' => $conversation->guest->avatar ?? null,
                ] : null,
            ] : null,
            'counterparty' => $counterparty ? [
                'id'     => $counterparty->id,
                'name'   => $counterparty->name,
                'avatar' => $counterparty->avatar ?? null,
                'role'   => $isPartnerView ? 'guest' : 'partner',
            ] : null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function formatMessage(Message $message): array
    {
        return [
            'id'              => $message->id,
            'conversation_id' => $message->conversation_id,
            'user_id'         => $message->sender_id,
            'sender_id'       => $message->sender_id,
            'content'         => $message->content,
            'is_read'         => $message->read_at !== null,
            'read_at'         => $message->read_at?->toDateTimeString(),
            'metadata'        => $message->metadata,
            'created_at'      => $message->created_at?->toDateTimeString(),
            'user'            => $message->sender ? [
                'name'   => $message->sender->name,
                'avatar' => $message->sender->avatar ?? null,
            ] : null,
        ];
    }

    private function countUnreadMessages(int $conversationId, int $actorId): int
    {
        return Message::query()
            ->where('conversation_id', $conversationId)
            ->whereNull('read_at')
            ->where('sender_id', '!=', $actorId)
            ->count();
    }
}
