<?php

declare(strict_types=1);

namespace App\Repositories\RoomImageRepository;

use App\Repositories\RepositoryInterface;
use Illuminate\Support\Collection;

/**
 * Interface RoomImageRepositoryInterface
 *
 * @package App\Repositories\RoomImageRepository
 */
interface RoomImageRepositoryInterface extends RepositoryInterface
{
    /**
     * Get images by room ID
     *
     * @param int $roomId
     * @return Collection
     */
    public function getByRoomId(int $roomId): Collection;

    /**
     * Get max sort value for a room
     *
     * @param int $roomId
     * @return int
     */
    public function getMaxSortByRoomId(int $roomId): int;

    /**
     * Update sort range for room images
     *
     * @param int $roomId
     * @param int $minSort
     * @param int $maxSort
     * @param int $increment
     * @return void
     */
    public function updateSortRange(int $roomId, int $minSort, int $maxSort, int $increment): void;

    /**
     * Update sort range with updatedBy for room images
     *
     * @param integer $roomId
     * @param integer $minSort
     * @param integer $maxSort
     * @param integer $userId
     * @return void
     */
    public function updateSortRangeWithUpdatedBy(int $roomId, int $minSort, int $maxSort, int $userId): void;
}
