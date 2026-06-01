<?php

namespace App\QueryFilters;

use Closure;
use Illuminate\Database\Eloquent\Builder;

abstract class Filter
{
    /**
     * Handle the incoming request query builder.
     *
     * @param mixed $query
     * @param \Closure $next
     * @return mixed
     */
    public function handle($query, Closure $next)
    {
        if (!request()->filled($this->filterName())) {
            return $next($query);
        }

        $query = $this->applyFilter($query, request($this->filterName()));

        return $next($query);
    }

    /**
     * Get the query parameter filter name.
     *
     * @return string
     */
    abstract protected function filterName(): string;

    /**
     * Apply filter query logic.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param mixed $value
     * @return \Illuminate\Database\Eloquent\Builder
     */
    abstract protected function applyFilter(Builder $query, $value);
}
