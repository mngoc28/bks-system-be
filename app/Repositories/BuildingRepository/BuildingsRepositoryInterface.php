<?php

declare(strict_types=1);

namespace App\Repositories\BuildingRepository;

use App\Repositories\RepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface BuildingsRepositoryInterface extends RepositoryInterface
{
    /**
     * Get all buildings or search by criteria
     *
     * @param Request $request
     * @param array $sort
     * @return LengthAwarePaginator
     */
    public function getAllOrSearchBuildings(Request $request, array $sort = []): LengthAwarePaginator;

    /**
     * Get all buildings types
     *
     * @return Collection
     */
    public function getAllBuildingsTypes(): Collection;

    /**
     * Get building by ID
     *
     * @param int $id
     * @return object | null
     */
    public function getBuildingById(int $id): object | null;

    /**
     * Get all buildings without pagination
     *
     * @return Collection
     */
    public function getAllBuildingNames(): Collection;
}
