<?php

namespace App\QueryFilters\Rooms;

use App\QueryFilters\Filter;
use Illuminate\Database\Eloquent\Builder;

class AmenityIds extends Filter
{
    protected function filterName(): string
    {
        return 'amenity_ids';
    }

    protected function applyFilter(Builder $query, $value)
    {
        $ids = is_array($value) ? $value : explode(',', (string) $value);
        $ids = array_values(array_filter(array_map('intval', $ids)));

        if (empty($ids)) {
            return $query;
        }

        foreach ($ids as $id) {
            $query->whereHas('amenities', function ($q) use ($id) {
                $q->where('amenities.id', $id);
            });
        }

        return $query;
    }
}
