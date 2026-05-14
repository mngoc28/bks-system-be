<?php

declare(strict_types=1);

namespace App\Repositories\PropertyImageRepository;

use App\Repositories\RepositoryInterface;
use Illuminate\Support\Collection;

interface PropertyImageRepositoryInterface extends RepositoryInterface
{
    public function getByPropertyId(int $propertyId): Collection;

    public function getMaxSortByPropertyId(int $propertyId): int;
}
