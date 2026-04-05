<?php

declare(strict_types=1);

namespace App\Repositories\BuildingRepository;

use App\Repositories\RepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Interface BuildingsRepositoryInterface
 *
 * @package App\Repositories\BuildingRepository
 */
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

    // =========================================================================
    // PARTNER METHODS
    // =========================================================================

    /**
     * Get buildings for a specific partner
     *
     * @param int $partnerId
     * @param Request $request
     * @param array $sort
     * @return LengthAwarePaginator
     */
    public function getBuildingsForPartner(int $partnerId, Request $request, array $sort = []): LengthAwarePaginator;

    /**
     * Get building by ID for a specific partner
     *
     * @param int $id
     * @param int $partnerId
     * @return object|null
     */
    public function getBuildingByIdForPartner(int $id, int $partnerId): object|null;

    /**
     * Get all building names for a specific partner
     *
     * @param int $partnerId
     * @return Collection
     */
    public function getBuildingNamesForPartner(int $partnerId): Collection;
}
