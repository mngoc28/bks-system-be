<?php

namespace App\QueryFilters\Rooms;

use App\QueryFilters\Filter;
use Illuminate\Database\Eloquent\Builder;

class PropertyId extends Filter
{
    protected function filterName(): string
    {
        return 'property_id';
    }

    protected function applyFilter(Builder $query, $value)
    {
        return $query->where('rooms.property_id', $value);
    }
}
