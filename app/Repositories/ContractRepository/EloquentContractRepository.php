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

    /**
     * Get contracts for a partner
     *
     * @param int $partnerId
     * @return Collection
     */
    public function getContractsForPartner(int $partnerId): Collection
    {
        return $this->model->whereHas('booking.room.building', function ($query) use ($partnerId) {
            $query->where('user_id', $partnerId);
        })->with(['booking.user', 'booking.room.building'])->get();
    }

    /**
     * Get contract detail for a specific partner
     *
     * @param int $id
     * @param int $partnerId
     * @return Contract|null
     */
    public function getPartnerContractDetail(int $id, int $partnerId): ?Contract
    {
        /** @var Contract|null $contract */
        $contract = $this->model->where('id', $id)
            ->whereHas('booking.room.building', function ($query) use ($partnerId) {
                $query->where('user_id', $partnerId);
            })->with(['booking.user', 'booking.room.building', 'booking.price'])->first();

        return $contract;
    }
}
