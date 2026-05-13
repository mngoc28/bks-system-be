<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Room;
use App\Models\RoomBlock;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Authorization rules cho thao tác room block của Partner.
 *
 * Ownership đi qua `room_blocks.room.building.user_id`. Admin được bypass
 * (giống `BookingPolicy`) để hỗ trợ thao tác audit.
 */
final class RoomBlockPolicy
{
    use HandlesAuthorization;

    public function before(User $user, string $ability): ?bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        return null;
    }

    /**
     * Partner xem danh sách block trên các phòng mình sở hữu (kiểm theo room).
     */
    public function viewForRoom(User $user, Room $room): bool
    {
        return $this->isOwnerOfRoom($user, $room);
    }

    /**
     * Partner tạo block trên phòng của mình.
     */
    public function createForRoom(User $user, Room $room): bool
    {
        return $this->isOwnerOfRoom($user, $room);
    }

    /**
     * Partner xoá block thuộc phòng của mình.
     */
    public function delete(User $user, RoomBlock $block): bool
    {
        $room = $block->relationLoaded('room') ? $block->room : $block->room()->first();
        if ($room === null) {
            return false;
        }

        return $this->isOwnerOfRoom($user, $room);
    }

    private function isOwnerOfRoom(User $user, Room $room): bool
    {
        if ($user->role !== 'partner') {
            return false;
        }

        $building = $room->relationLoaded('building') ? $room->building : $room->building()->first();
        if ($building === null) {
            return false;
        }

        return (int) $building->user_id === (int) $user->id;
    }
}
