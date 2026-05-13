<?php

declare(strict_types=1);

namespace App\Repositories\RoomBlockRepository;

use App\Models\RoomBlock;
use App\Repositories\RepositoryInterface;
use Illuminate\Support\Collection;

/**
 * Interface RoomBlockRepositoryInterface
 *
 * @package App\Repositories\RoomBlockRepository
 */
interface RoomBlockRepositoryInterface extends RepositoryInterface
{
    /**
     * Liệt kê các room block trong phạm vi ngày cho 1 hoặc nhiều phòng.
     *
     * Block "trùng" range nếu `start_date <= toDate` và `end_date >= fromDate`
     * (interval intersect).
     *
     * @param array<int, int> $roomIds
     * @param string $fromDate Y-m-d
     * @param string $toDate   Y-m-d
     * @return Collection<int, RoomBlock>
     */
    public function listForRoomsInRange(array $roomIds, string $fromDate, string $toDate): Collection;

    /**
     * Tìm các block xung đột với khoảng [$startDate, $endDate] cho 1 phòng.
     *
     * Hai khoảng [a,b] và [c,d] giao nhau khi a <= d AND b >= c.
     *
     * @param int $roomId
     * @param string $startDate Y-m-d
     * @param string $endDate   Y-m-d
     * @param int|null $excludeBlockId Bỏ qua block hiện tại khi update
     * @return Collection<int, RoomBlock>
     */
    public function findConflicting(
        int $roomId,
        string $startDate,
        string $endDate,
        ?int $excludeBlockId = null
    ): Collection;
}
