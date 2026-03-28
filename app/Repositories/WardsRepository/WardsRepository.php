<?php

namespace App\Repositories\WardsRepository;

use App\Models\Ward;
use App\Repositories\BaseRepository;

class WardsRepository extends BaseRepository implements WardsRepositoryInterface
{
    /**
     * get the model for the repository
     * @return mixed
     */
    public function getModel(): mixed
    {
        return Ward::class;
    }

    /**
     * get ward by provinde id
     * @param int $provinceId
     * @return object
     */
    public function getWardsByProvinceId(int $provinceId): object
    {
        return $this->model->where('province_id', $provinceId)->get();
    }
}
