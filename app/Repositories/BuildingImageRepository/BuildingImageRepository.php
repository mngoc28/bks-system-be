<?php

declare(strict_types=1);

namespace App\Repositories\BuildingImageRepository;

use App\Models\BuildingImage;
use App\Repositories\BaseRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Class BuildingImageRepository
 *
 * @package App\Repositories\BuildingImageRepository
 */
final class BuildingImageRepository extends BaseRepository implements BuildingImageRepositoryInterface
{
    /**
     * Get the model class name
     *
     * @return string
     */
    public function getModel(): string
    {
        return BuildingImage::class;
    }

    /**
     * Get images by building ID
     *
     * @param int $buildingId
     * @return Collection
     */
    public function getByBuildingId(int $buildingId): Collection
    {
        return $this->model
            ->where('building_id', $buildingId)
            ->orderBy('sort', 'asc')
            ->get();
    }

    /**
     * Get max sort value for a building
     *
     * @param int $buildingId
     * @return int
     */
    public function getMaxSortByBuildingId(int $buildingId): int
    {
        $maxSort = $this->model
            ->where('building_id', $buildingId)
            ->max('sort');

        return $maxSort ?? 0;
    }
}
