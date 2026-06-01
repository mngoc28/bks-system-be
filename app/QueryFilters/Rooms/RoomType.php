<?php

namespace App\QueryFilters\Rooms;

use App\QueryFilters\Filter;
use Illuminate\Database\Eloquent\Builder;

class RoomType extends Filter
{
    protected function filterName(): string
    {
        return 'room_type';
    }

    protected function applyFilter(Builder $query, $value)
    {
        return $query->where('rooms.room_type', $value);
    }
}
