<?php

declare(strict_types=1);

namespace App\Repositories\RoomBlockRepository;

use App\Models\RoomBlock;
use App\Repositories\BaseRepository;
use Illuminate\Support\Collection;

/**
 * Class RoomBlockRepository
 *
 * @package App\Repositories\RoomBlockRepository
 */
final class RoomBlockRepository extends BaseRepository implements RoomBlockRepositoryInterface
{
    public function getModel(): string
    {
        return RoomBlock::class;
    }

    public function listForRoomsInRange(array $roomIds, string $fromDate, string $toDate): Collection
    {
        if ($roomIds === []) {
            return new Collection();
        }

        /** @var list<RoomBlock> $blocks */
        $blocks = $this->model
            ->whereIn('room_id', $roomIds)
            ->where('start_date', '<=', $toDate)
            ->where('end_date', '>=', $fromDate)
            ->orderBy('start_date')
            ->orderBy('id')
            ->get()
            ->all();

        return new Collection($blocks);
    }

    public function findConflicting(
        int $roomId,
        string $startDate,
        string $endDate,
        ?int $excludeBlockId = null
    ): Collection {
        $query = $this->model
            ->where('room_id', $roomId)
            ->where('start_date', '<', $endDate)
            ->where('end_date', '>', $startDate);

        if ($excludeBlockId !== null) {
            $query->where('id', '!=', $excludeBlockId);
        }

        /** @var list<RoomBlock> $blocks */
        $blocks = $query->get()->all();

        return new Collection($blocks);
    }
}
