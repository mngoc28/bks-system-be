<?php

declare(strict_types=1);

namespace App\Services;

use App\Events\MessageSent;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Exception;

final class ChatService
{
    /**
     * Start or get an existing conversation between two users.
     *
     * @param int $userId
     * @param int $partnerId
     * @param int|null $bookingId
     * @return Conversation
     */
    public function getOrCreateConversation(int $userId, int $partnerId, ?int $bookingId = null): Conversation
    {
        return Conversation::firstOrCreate([
            'user_id'    => $userId,
            'partner_id' => $partnerId,
            'booking_id' => $bookingId,
        ]);
    }

    /**
     * Send a message in a conversation.
     *
     * @param int $conversationId
     * @param int $senderId
     * @param string $content
     * @param array|null $metadata
     * @return Message
     * @throws Exception
     */
    public function sendMessage(int $conversationId, int $senderId, string $content, ?array $metadata = null): Message
    {
        DB::beginTransaction();
        try {
            $message = Message::create([
                'conversation_id' => $conversationId,
                'sender_id'       => $senderId,
                'content'         => $content,
                'metadata'        => $metadata,
            ]);

            Conversation::where('id', $conversationId)->update([
                'last_message_at' => now(),
            ]);

            DB::commit();

            // Broadcast the message
            broadcast(new MessageSent($message))->toOthers();

            return $message;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get all conversations for a user (either guest or partner).
     *
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUserConversations(int $userId)
    {
        return Conversation::where('user_id', $userId)
            ->orWhere('partner_id', $userId)
            ->with(['guest', 'partner', 'booking.room'])
            ->orderByDesc('last_message_at')
            ->get();
    }

    /**
     * Get messages in a conversation.
     *
     * @param int $conversationId
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getMessages(int $conversationId, int $limit = 50)
    {
        return Message::where('conversation_id', $conversationId)
            ->with('sender')
            ->orderBy('created_at', 'asc')
            ->take($limit)
            ->get();
    }
}
