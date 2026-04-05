<?php

declare(strict_types=1);

namespace App\Repositories\RoomMaintenanceRepository;

use App\Repositories\RepositoryInterface;

/**
 * Interface RoomMaintenanceRepositoryInterface
 *
 * @package App\Repositories\RoomMaintenanceRepository
 */
interface RoomMaintenanceRepositoryInterface extends RepositoryInterface
{
    /**
     * Get urgent maintenance requests for a specific partner
     *
     * @param int $partnerId
     * @param int $limit
     * @return \Illuminate\Support\Collection
     */
    public function getUrgentMaintenancesForPartner(int $partnerId, int $limit = 5);
}
