<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Room;
use App\Models\RoomMaintenance;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

/**
 * Authorization rules cho phiếu bảo trì phòng của Partner.
 */
final class RoomMaintenancePolicy
{
    use HandlesAuthorization;

    public function before(User $user, string $ability): ?bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        return null;
    }

    public function view(User $user, RoomMaintenance $maintenance): bool
    {
        return $this->isOwnerOfMaintenance($user, $maintenance);
    }

    public function update(User $user, RoomMaintenance $maintenance): bool
    {
        return $this->isOwnerOfMaintenance($user, $maintenance);
    }

    public function createForRoom(User $user, Room $room): bool
    {
        return $this->isOwnerOfRoom($user, $room);
    }

    private function isOwnerOfMaintenance(User $user, RoomMaintenance $maintenance): bool
    {
        if ($user->role !== 'partner') {
            return false;
        }

        $property = $maintenance->relationLoaded('property')
            ? $maintenance->property
            : $maintenance->property()->first();

        if ($property === null) {
            return false;
        }

        return (int) $property->user_id === (int) $user->id;
    }

    private function isOwnerOfRoom(User $user, Room $room): bool
    {
        if ($user->role !== 'partner') {
            return false;
        }

        $property = $room->relationLoaded('property') ? $room->property : $room->property()->first();
        if ($property === null) {
            return false;
        }

        return (int) $property->user_id === (int) $user->id;
    }
}
