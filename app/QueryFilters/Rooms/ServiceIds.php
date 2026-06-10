<?php

namespace App\QueryFilters\Rooms;

use App\QueryFilters\Filter;
use Illuminate\Database\Eloquent\Builder;

class ServiceIds extends Filter
{
    protected function filterName(): string
    {
        return 'service_ids';
    }

    protected function applyFilter(Builder $query, $value)
    {
        $ids = is_array($value) ? $value : explode(',', (string) $value);
        $ids = array_values(array_filter(array_map('intval', $ids)));

        if (empty($ids)) {
            return $query;
        }

        foreach ($ids as $id) {
            $query->whereHas('services', function ($q) use ($id) {
                $q->where('services.id', $id);
            });
        }

        return $query;
    }
}
