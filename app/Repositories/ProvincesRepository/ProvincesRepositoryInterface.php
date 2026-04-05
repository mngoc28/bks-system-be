<?php

declare(strict_types=1);

namespace App\Repositories\ProvincesRepository;

use App\Repositories\RepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Interface ProvincesRepositoryInterface
 *
 * @package App\Repositories\ProvincesRepository
 */
interface ProvincesRepositoryInterface extends RepositoryInterface
{
    /**
     * Get paginated list of provinces
     *
     * @param \Illuminate\Http\Request|mixed $request
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function listProvinces($request): LengthAwarePaginator;

    /**
     * Get province details by ID
     *
     * @param int $id
     * @return object
     */
    public function detailProvince(int $id): object;

    /**
     * Get all province types
     *
     * @return object
     */
    public function getAllProvincesTypes(): object;

    /**
     * Get all provinces without pagination
     *
     * @return array
     */
    public function getAllProvinces(): array;
}
