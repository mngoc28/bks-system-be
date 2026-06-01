<?php

namespace App\QueryFilters\Rooms;

use App\QueryFilters\Filter;
use Illuminate\Database\Eloquent\Builder;

class RoomNumber extends Filter
{
    protected function filterName(): string
    {
        return 'room_number';
    }

    protected function applyFilter(Builder $query, $value)
    {
        return $query->where('rooms.room_number', 'like', '%' . $value . '%');
    }
}
