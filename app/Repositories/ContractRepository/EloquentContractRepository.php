<?php

declare(strict_types=1);

namespace App\Repositories\ContractRepository;

use App\Models\Contract;
use App\Repositories\BaseRepository;
use Illuminate\Support\Collection;

final class EloquentContractRepository extends BaseRepository implements ContractRepositoryInterface
{
    /**
     * Get model
     *
     * @return string
     */
    public function getModel(): string
    {
        return Contract::class;
    }

    /**
     * Get contracts by user ID
     *
     * @param int $userId
     * @return Collection
     */
    public function getContractsByUserId(int $userId): Collection
    {
        return $this->model->whereHas('booking', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->with(['booking.room.building'])->get();
    }

    /**
     * Get contract detail for a specific user
     *
     * @param int $id
     * @param int $userId
     * @return Contract|null
     */
    public function getContractDetail(int $id, int $userId): ?Contract
    {
        return $this->model->where('id', $id)
            ->whereHas('booking', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })->with(['booking.user', 'booking.room.building', 'booking.price'])->first();
    }
}
