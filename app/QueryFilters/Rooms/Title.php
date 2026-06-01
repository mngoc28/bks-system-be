<?php

namespace App\QueryFilters\Rooms;

use App\QueryFilters\Filter;
use Illuminate\Database\Eloquent\Builder;

class Title extends Filter
{
    protected function filterName(): string
    {
        return 'title';
    }

    protected function applyFilter(Builder $query, $value)
    {
        return $query->where('rooms.title', 'like', '%' . $value . '%');
    }
}
