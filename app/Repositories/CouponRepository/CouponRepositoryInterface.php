<?php

namespace App\Repositories\CouponRepository;

use App\Repositories\RepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface CouponRepositoryInterface extends RepositoryInterface
{
    public function paginateWithFilters(array $filters): LengthAwarePaginator;
}
