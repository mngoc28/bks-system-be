<?php

declare(strict_types=1);

namespace App\Repositories\RoomImageRepository;

use App\Models\RoomImage;
use App\Repositories\BaseRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final class RoomImageRepository extends BaseRepository implements RoomImageRepositoryInterface
{
    /**
     * Get the model class name
     *
     * @return string
     */
    public function getModel(): string
    {
        return RoomImage::class;
    }

    /**
     * Get images by room ID
     *
     * @param int $roomId
     * @return Collection
     */
    public function getByRoomId(int $roomId): Collection
    {
        return $this->model
            ->where('room_id', $roomId)
            ->orderBy('sort', 'asc')
            ->get();
    }

    /**
     * Get max sort value for a room
     *
     * @param int $roomId
     * @return int
     */
    public function getMaxSortByRoomId(int $roomId): int
    {
        return $this->model
            ->where('room_id', $roomId)
            ->max('sort') ?? 0;
    }


    /**
     * Update sort range for room images
     *
     * @param int $roomId
     * @param int $minSort
     * @param int $maxSort
     * @param int $increment
     * @return void
     */
    public function updateSortRange(int $roomId, int $minSort, int $maxSort, int $increment): void
    {
        $this->model
            ->where('room_id', $roomId)
            ->whereBetween('sort', [$minSort, $maxSort])
            ->update(['sort' => DB::raw("sort + $increment")]);
    }

    /**
     * Summary of update sort range with updatedBy
     * @param int $roomId
     * @param int $minSort
     * @param int $maxSort
     * @param int $userId
     * @return void
     */
    public function updateSortRangeWithUpdatedBy(int $roomId, int $minSort, int $maxSort, int $userId): void
    {
        $this->model
            ->where('room_id', $roomId)
            ->whereBetween('sort', [$minSort, $maxSort])
            ->update(['updated_by' => $userId, 'updated_at' => now()]);
    }
}
