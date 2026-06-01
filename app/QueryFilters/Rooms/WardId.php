<?php

namespace App\QueryFilters\Rooms;

use App\QueryFilters\Filter;
use Illuminate\Database\Eloquent\Builder;

class WardId extends Filter
{
    protected function filterName(): string
    {
        return 'ward_id';
    }

    protected function applyFilter(Builder $query, $value)
    {
        return $query->where('b.ward_id', $value);
    }
}
