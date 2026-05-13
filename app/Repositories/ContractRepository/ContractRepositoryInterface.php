<?php

declare(strict_types=1);

namespace App\Repositories\ContractRepository;

use App\Repositories\RepositoryInterface;
use Illuminate\Support\Collection;
use App\Models\Contract;

interface ContractRepositoryInterface extends RepositoryInterface
{
    /**
     * Get contracts by user ID
     *
     * @param int $userId
     * @return Collection
     */
    public function getContractsByUserId(int $userId): Collection;

    /**
     * Get contract detail for a specific user
     *
     * @param int $id
     * @param int $userId
     * @return Contract|null
     */
    public function getContractDetail(int $id, int $userId): ?Contract;

    /**
     * Get contracts for a specific partner
     *
     * @param int $partnerId
     * @return Collection
     */
    public function getContractsForPartner(int $partnerId): Collection;

    /**
     * Get contract detail for a specific partner
     *
     * @param int $id
     * @param int $partnerId
     * @return Contract|null
     */
    public function getPartnerContractDetail(int $id, int $partnerId): ?Contract;

    /**
     * Get long-term contracts whose booking end_date is within `$daysAhead`
     * from today, that are not terminated and not yet reminded. Used by the
     * daily renewal-reminder scheduler.
     *
     * @return Collection<int, Contract>
     */
    public function getLongTermContractsDueForReminder(int $daysAhead): Collection;

    /**
     * Get contracts where renewal_reminder_at is set and the contract is not
     * terminated, scoped to a partner. Powers the Phase 5 Alert Center entry
     * "Contract sắp hết hạn".
     *
     * @return Collection<int, Contract>
     */
    public function getExpiringContractsForPartner(int $partnerId): Collection;
}
