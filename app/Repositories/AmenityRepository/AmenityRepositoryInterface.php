<?php

namespace App\Repositories\AmenityRepository;

use App\Repositories\RepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

interface AmenityRepositoryInterface extends RepositoryInterface
{
    /**
     * Summary of getAllOrSearchAmenities
     * @param Request $request
     * @return void
     */
    public function getAllOrSearch(Request $request): LengthAwarePaginator;

    /**
     * Get all amenities without pagination
     *
     * @return \Illuminate\Support\Collection
     */
    public function getAllAmenities(): \Illuminate\Support\Collection;
}
