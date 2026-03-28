<?php

namespace App\Repositories\ServiceRepository;

use App\Repositories\RepositoryInterface;

interface ServiceRepositoryInterface extends RepositoryInterface
{
    /**
     * Get all services or search services
     * @param mixed $request
     * @return mixed
     */
    public function getAllOrSearch($request): mixed;

    /**
     * Get all services without pagination
     *
     * @return \Illuminate\Support\Collection
     */
    public function getAllServices(): \Illuminate\Support\Collection;
}
