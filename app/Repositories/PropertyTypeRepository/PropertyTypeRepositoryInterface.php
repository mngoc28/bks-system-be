<?php

namespace App\Repositories\PropertyTypeRepository;

use App\Repositories\RepositoryInterface;

interface PropertyTypeRepositoryInterface extends RepositoryInterface
{
    /**
     * Retrieve property types list.
     *
     * @param array{pagination?: int|null} $filters
     * @return mixed
     */
    public function getList(array $filters = []);
}
