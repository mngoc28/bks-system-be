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
    public function getList(array $filters);
}
