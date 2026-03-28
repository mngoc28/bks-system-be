<?php

namespace App\Repositories\ProvincesRepository;

use App\Repositories\RepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

interface ProvincesRepositoryInterface extends RepositoryInterface
{
    /**
     * Get all provinces
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function listProvinces($request): LengthAwarePaginator;

    /**
     * Get province details by ID
     * @param int $id
     * @return object
     */
    public function detailProvince(int $id): object;

    /**
     * Get all provinces types
     * @return object
     */
    public function getAllProvincesTypes(): object;

    /**
     * Get all provinces
     * @return array
     */
    public function getAllProvinces(): array;
}
