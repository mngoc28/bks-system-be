<?php

namespace App\Repositories\WardsRepository;

use App\Repositories\RepositoryInterface;

interface WardsRepositoryInterface extends RepositoryInterface
{
    /**
     * get ward by provinde id
     * @param int $provinceId
     * @return object
     */
    public function getWardsByProvinceId(int $provinceId): object;
}
