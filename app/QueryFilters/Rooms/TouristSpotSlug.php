<?php

declare(strict_types=1);

namespace App\QueryFilters\Rooms;

use App\QueryFilters\Filter;
use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

final class TouristSpotSlug extends Filter
{
    protected function filterName(): string
    {
        return 'tourist_spot_slug';
    }

    /**
     * @param mixed $query
     * @param Closure $next
     * @return mixed
     */
    public function handle($query, Closure $next)
    {
        if (! request()->filled('tourist_spot_slug')) {
            return $next($query);
        }

        $slug = (string) request()->input('tourist_spot_slug');

        $query = $query->whereExists(function ($subQuery) use ($slug): void {
            $subQuery->select(DB::raw('1'))
                ->from('room_tourist_spot_maps as rtsm_filter')
                ->join('tourist_spots as ts_filter', 'ts_filter.id', '=', 'rtsm_filter.tourist_spot_id')
                ->join('rooms as rooms_filter', 'rooms_filter.id', '=', 'rtsm_filter.room_id')
                ->join('properties as props_filter', 'props_filter.id', '=', 'rooms_filter.property_id')
                ->whereColumn('rtsm_filter.room_id', 'rooms.id')
                ->where('ts_filter.slug', $slug)
                ->where('ts_filter.is_active', true)
                ->whereNotNull('ts_filter.province_id')
                ->whereColumn('props_filter.province_id', 'ts_filter.province_id');
        });

        return $next($query);
    }

    protected function applyFilter(Builder $query, $value)
    {
        return $query;
    }
}
