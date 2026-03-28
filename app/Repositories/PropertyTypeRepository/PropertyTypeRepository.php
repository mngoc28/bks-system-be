<?php

namespace App\Repositories\PropertyTypeRepository;

use App\Models\PropertyType;
use App\Repositories\BaseRepository;

class PropertyTypeRepository extends BaseRepository implements PropertyTypeRepositoryInterface
{
    /**
     * Get model class name.
     *
     * @return string
     */
    public function getModel(): string
    {
        return PropertyType::class;
    }

    /**
     * Retrieve property types list.
     *
     * @param array{pagination?: int|null} $filters
     * @return mixed
     */
    public function getList(array $filters = [])
    {
        $query = $this->model->newQuery()->orderByDesc('created_at');

        if (! empty($filters['pagination'])) {
            return $query->paginate((int) $filters['pagination']);
        }

        return $query->get();
    }
}
