<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\RoomBlock;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Sự kiện khi room block được tạo/xoá (Partner Portal 360 Phase 3).
 *
 * Phát đến `private-partner.{partnerId}` và `private-property.{propertyId}`
 * để FE Calendar invalidate cache. `action` là `created` hoặc `deleted` để
 * FE phân biệt UX (toast/optimistic).
 *
 * Payload không chứa PII — chỉ id, room_id, block_type, dates.
 */
class RoomBlockChanged implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public RoomBlock $block;
    public int $partnerId;
    public int $propertyId;
    public string $action;
    public ?int $actorId;

    public function __construct(
        RoomBlock $block,
        int $partnerId,
        int $propertyId,
        string $action,
        ?int $actorId = null,
    ) {
        $this->block       = $block->withoutRelations();
        $this->partnerId   = $partnerId;
        $this->propertyId  = $propertyId;
        $this->action      = $action;
        $this->actorId     = $actorId;
    }

    /**
     * @return array<int, PrivateChannel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('partner.' . $this->partnerId),
            new PrivateChannel('property.' . $this->propertyId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'room_block.changed';
    }

    /**
     * @return array<string, mixed>
     */
    public function broadcastWith(): array
    {
        return [
            'id'          => $this->block->id,
            'room_id'     => $this->block->room_id,
            'partner_id'  => $this->partnerId,
            'property_id' => $this->propertyId,
            'block_type'  => $this->block->block_type,
            'start_date'  => optional($this->block->start_date)->toDateString(),
            'end_date'    => optional($this->block->end_date)->toDateString(),
            'action'      => $this->action,
            'actor_id'    => $this->actorId,
        ];
    }
}
