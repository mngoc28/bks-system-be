<?php

namespace App\QueryFilters\Rooms;

use App\QueryFilters\Filter;
use Illuminate\Database\Eloquent\Builder;

class PartnerId extends Filter
{
    protected function filterName(): string
    {
        return 'partner_id';
    }

    protected function applyFilter(Builder $query, $value)
    {
        return $query->where('pi.id', $value);
    }
}
