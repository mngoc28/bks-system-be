<?php

namespace App\Repositories\RoomMaintenanceRepository;

use App\Repositories\RepositoryInterface;

interface RoomMaintenanceRepositoryInterface extends RepositoryInterface
{
    /**
     * Get room maintenance list with filters.
     *
     * @param array $filters
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator|\Illuminate\Support\Collection
     */
    /**
     * Get urgent maintenance requests for a specific partner
     *
     * @param int $partnerId
     * @param int $limit
     * @return \Illuminate\Support\Collection
     */
    public function getUrgentMaintenancesForPartner(int $partnerId, int $limit = 5);
}
