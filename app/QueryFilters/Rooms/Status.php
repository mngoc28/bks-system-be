<?php

namespace App\QueryFilters\Rooms;

use App\QueryFilters\Filter;
use Illuminate\Database\Eloquent\Builder;

class Status extends Filter
{
    protected function filterName(): string
    {
        return 'status';
    }

    protected function applyFilter(Builder $query, $value)
    {
        return $query->where('rooms.status', $value);
    }
}
