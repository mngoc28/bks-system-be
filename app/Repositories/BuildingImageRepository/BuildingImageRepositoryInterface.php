<?php

declare(strict_types=1);

namespace App\Repositories\BuildingImageRepository;

use App\Repositories\RepositoryInterface;
use Illuminate\Support\Collection;

/**
 * Interface BuildingImageRepositoryInterface
 *
 * @package App\Repositories\BuildingImageRepository
 */
interface BuildingImageRepositoryInterface extends RepositoryInterface
{
    /**
     * Get images by building ID
     *
     * @param int $buildingId
     * @return Collection
     */
    public function getByBuildingId(int $buildingId): Collection;

    /**
     * Get max sort value for a building
     *
     * @param int $buildingId
     * @return int
     */
    public function getMaxSortByBuildingId(int $buildingId): int;
}
