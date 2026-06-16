<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use App\Models\Message;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public Message $message;

    /**
     * Create a new event instance.
     */
    public function __construct(Message $message)
    {
        $this->message = $message->load('sender');
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        $channels = [
            new PrivateChannel('conversation.' . $this->message->conversation_id),
        ];

        $conversation = $this->message->conversation;
        if ($conversation) {
            $recipientId = (int) $this->message->sender_id === (int) $conversation->user_id
                ? (int) $conversation->partner_id
                : (int) $conversation->user_id;

            $recipient = \App\Models\User::find($recipientId);
            if ($recipient) {
                if ($recipient->role === 'partner') {
                    $channels[] = new PrivateChannel('partner.' . $recipientId);
                } else {
                    $channels[] = new PrivateChannel('App.Models.User.' . $recipientId);
                }
            }
        }

        return $channels;
    }

    /**
     * Data to broadcast.
     */
    public function broadcastAs(): string
    {
        return 'MessageSent';
    }

    public function broadcastWith(): array
    {
        $conversation = $this->message->conversation;
        $companyName = null;
        $roomName = null;
        $propertyName = null;
        if ($conversation) {
            $partner = \App\Models\User::find($conversation->partner_id);
            if ($partner) {
                $partner->loadMissing('partnerInfo');
                $companyName = $partner->partnerInfo?->company_name;
            }
            $booking = $conversation->booking;
            if ($booking) {
                $booking->loadMissing('room.property');
                $room = $booking->room;
                if ($room) {
                    $roomName = $room->title;
                    $propertyName = $room->property?->name;
                }
            }
        }

        return [
            'id'              => $this->message->id,
            'content'         => $this->message->content,
            'sender_id'       => $this->message->sender_id,
            'sender_name'     => $this->message->sender->name,
            'conversation_id' => $this->message->conversation_id,
            'company_name'    => $companyName,
            'room_name'       => $roomName,
            'property_name'   => $propertyName,
            'metadata'        => $this->message->metadata,
            'created_at'      => $this->message->created_at->toDateTimeString(),
            'is_read'         => $this->message->read_at !== null,
            'user'            => [
                'name'   => $this->message->sender->name,
                'avatar' => $this->message->sender->avatar ?? null,
            ],
        ];
    }
}
