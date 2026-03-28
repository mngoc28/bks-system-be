<?php

declare(strict_types=1);

namespace App\Repositories\PartnerInforRepository;

use App\Repositories\RepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

interface PartnerInforRepositoryInterface extends RepositoryInterface
{
    /**
     * Get list partner information
     *
     * @param Request $request
     * @return LengthAwarePaginator
     */
    public function getListPartner(Request $request): LengthAwarePaginator;

    /**
     * Get Partner Information By ID
     *
     * @param int $int
     * @return object
     */
    public function getPartnerById(int $id): ?object;

    /**
     * Get random partners for homepage
     *
     * @param Request $request
     * @return array
     */
    public function getRandomPartners(Request $request): array;

    /**
     * Get partners by province ID
     *
     * @param int $provinceId
     * @return array
     */
    public function getPartnersByProvinceId(int $provinceId): array;

    /**
     * Get public partner detail by ID
     *
     * @param int $id
     * @return object|null
     */
    public function getPartnerDetail(int $id): ?object;
}
