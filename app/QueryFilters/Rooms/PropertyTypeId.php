<?php

namespace App\QueryFilters\Rooms;

use App\QueryFilters\Filter;
use Illuminate\Database\Eloquent\Builder;

class PropertyTypeId extends Filter
{
    protected function filterName(): string
    {
        return 'property_type_id';
    }

    protected function applyFilter(Builder $query, $value)
    {
        return $query->where('b.property_type_id', $value);
    }
}
