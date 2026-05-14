<?php

declare(strict_types=1);

namespace App\Repositories\PropertyImageRepository;

use App\Models\PropertyImage;
use App\Repositories\BaseRepository;
use Illuminate\Support\Collection;

final class PropertyImageRepository extends BaseRepository implements PropertyImageRepositoryInterface
{
    public function getModel(): string
    {
        return PropertyImage::class;
    }

    public function getByPropertyId(int $propertyId): Collection
    {
        return $this->model
            ->where('property_id', $propertyId)
            ->orderBy('sort', 'asc')
            ->get();
    }

    public function getMaxSortByPropertyId(int $propertyId): int
    {
        $maxSort = $this->model
            ->where('property_id', $propertyId)
            ->max('sort');

        return $maxSort ?? 0;
    }
}
