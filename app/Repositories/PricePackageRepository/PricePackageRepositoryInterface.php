<?php

declare(strict_types=1);

namespace App\Repositories\PricePackageRepository;

use App\Repositories\RepositoryInterface;

/**
 * Interface PricePackageRepositoryInterface
 *
 * @package App\Repositories\PricePackageRepository
 */
interface PricePackageRepositoryInterface extends RepositoryInterface
{
    /**
     * Get all price packages without pagination
     *
     * @return \Illuminate\Support\Collection
     */
    public function getAllPricePackages(): \Illuminate\Support\Collection;

    /**
     * Get price packages by room ID
     *
     * @param int $roomId
     * @return \Illuminate\Support\Collection
     */
    public function getPricePackagesByRoomId(int $roomId): \Illuminate\Support\Collection;

    /**
     * Get default price of a room by room ID
     *
     * @param int $roomId
     * @return object|null
     */
    public function getDefaultPriceOfRoom(int $roomId): object|null;
}
