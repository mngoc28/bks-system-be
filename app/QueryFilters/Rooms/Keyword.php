<?php

namespace App\QueryFilters\Rooms;

use App\QueryFilters\Filter;
use Illuminate\Database\Eloquent\Builder;

class Keyword extends Filter
{
    protected function filterName(): string
    {
        return 'keyword';
    }

    protected function applyFilter(Builder $query, $value)
    {
        return $query->where(function ($q) use ($value) {
            $q->where('rooms.title', 'like', '%' . $value . '%')
              ->orWhere('b.address_detail', 'like', '%' . $value . '%')
              ->orWhere('rooms.description', 'like', '%' . $value . '%');
        });
    }
}
