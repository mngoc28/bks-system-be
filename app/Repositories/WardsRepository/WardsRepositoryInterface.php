<?php

declare(strict_types=1);

namespace App\Repositories\WardsRepository;

use App\Repositories\RepositoryInterface;

/**
 * Interface WardsRepositoryInterface
 *
 * @package App\Repositories\WardsRepository
 */
interface WardsRepositoryInterface extends RepositoryInterface
{
    /**
     * Get wards by province ID
     *
     * @param int $provinceId
     * @return object
     */
    public function getWardsByProvinceId(int $provinceId): object;
}
