<?php

namespace App\QueryFilters\Rooms;

use App\QueryFilters\Filter;
use Closure;
use Illuminate\Database\Eloquent\Builder;

class ProvinceId extends Filter
{
    protected function filterName(): string
    {
        return 'province_id';
    }

    /**
     * Handle filtering by province_ids array or single province_id parameter.
     *
     * @param mixed $query
     * @param \Closure $next
     * @return mixed
     */
    public function handle($query, Closure $next)
    {
        $provinceIds = request()->input('province_ids', []);

        if (!empty($provinceIds) && is_array($provinceIds)) {
            $query = $query->whereIn('p.id', $provinceIds);
        } elseif (request()->filled('province_id')) {
            $query = $query->where('p.id', request()->input('province_id'));
        }

        return $next($query);
    }

    protected function applyFilter(Builder $query, $value)
    {
        return $query;
    }
}
