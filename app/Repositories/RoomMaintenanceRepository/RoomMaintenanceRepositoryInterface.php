<?php

declare(strict_types=1);

namespace App\Repositories\RoomMaintenanceRepository;

use App\Repositories\RepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Interface RoomMaintenanceRepositoryInterface
 *
 * @package App\Repositories\RoomMaintenanceRepository
 */
interface RoomMaintenanceRepositoryInterface extends RepositoryInterface
{
    /**
     * @param array<string, mixed> $filters
     */
    public function getList(array $filters): LengthAwarePaginator;

    /**
     * Find maintenance by id with optional partner scope.
     */
    public function findByIdForScope(int $id, ?int $partnerId): ?\App\Models\RoomMaintenance;

    /**
     * Get urgent maintenance requests for a specific partner
     *
     * @param int $partnerId
     * @param int $limit
     * @return \Illuminate\Support\Collection
     */
    public function getUrgentMaintenancesForPartner(int $partnerId, int $limit = 5);
}
