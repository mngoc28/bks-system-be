<?php

declare(strict_types=1);

namespace App\Repositories\ContractRepository;

use App\Models\Contract;
use App\Repositories\BaseRepository;
use Carbon\Carbon;
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
        })->with(['booking.room.property'])->get();
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
            })->with(['booking.user', 'booking.room.property', 'booking.price'])->first();
    }

    /**
     * Get contracts for a partner
     *
     * @param int $partnerId
     * @return Collection
     */
    public function getContractsForPartner(int $partnerId): Collection
    {
        return $this->model->whereHas('booking.room.property', function ($query) use ($partnerId) {
            $query->where('user_id', $partnerId);
        })->with(['booking.user', 'booking.room.property'])->get();
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
            ->whereHas('booking.room.property', function ($query) use ($partnerId) {
                $query->where('user_id', $partnerId);
            })->with([
                'booking.user',
                'booking.room.property',
                'booking.price',
                'booking.room.utilityFees',
            ])->first();

        return $contract;
    }

    /**
     * Long-term contracts (LEASE_AGREEMENT) whose underlying booking ends in
     * the next `$daysAhead` days, not terminated, not yet reminded.
     *
     * @return Collection<int, Contract>
     */
    public function getLongTermContractsDueForReminder(int $daysAhead): Collection
    {
        $today    = Carbon::today();
        $deadline = $today->copy()->addDays($daysAhead);

        /** @var list<Contract> $contracts */
        $contracts = $this->model->query()
            ->where('contract_type', 'LEASE_AGREEMENT')
            ->whereNull('renewal_reminder_at')
            ->whereNull('terminated_at')
            ->whereHas('booking', function ($q) use ($today, $deadline) {
                $q->whereBetween('end_date', [$today->toDateString(), $deadline->toDateString()]);
            })
            ->with(['booking.room.property'])
            ->get()
            ->all();

        return new Collection($contracts);
    }

    /**
     * Active reminders (LEASE_AGREEMENT, reminder set, not terminated) scoped
     * to one partner.
     *
     * @return Collection<int, Contract>
     */
    public function getExpiringContractsForPartner(int $partnerId): Collection
    {
        /** @var list<Contract> $contracts */
        $contracts = $this->model->query()
            ->where('contract_type', 'LEASE_AGREEMENT')
            ->whereNotNull('renewal_reminder_at')
            ->whereNull('terminated_at')
            ->whereHas('booking.room.property', function ($q) use ($partnerId) {
                $q->where('user_id', $partnerId);
            })
            ->with(['booking.user', 'booking.room.property'])
            ->orderBy('renewal_reminder_at')
            ->get()
            ->all();

        return new Collection($contracts);
    }
}
