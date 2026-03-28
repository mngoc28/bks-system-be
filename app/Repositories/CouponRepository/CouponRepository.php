<?php

namespace App\Repositories\CouponRepository;

use App\Models\Coupon;
use App\Repositories\BaseRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CouponRepository extends BaseRepository implements CouponRepositoryInterface
{
    public function getModel(): string
    {
        return Coupon::class;
    }

    public function paginateWithFilters(array $filters): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        $allowedSortColumns = [
            'id',
            'code',
            'type',
            'value',
            'min_order_value',
            'max_discount_value',
            'usage_limit',
            'used_count',
            'start_date',
            'end_date',
            'status',
            'created_at',
            'updated_at',
        ];

        $sortBy = $filters['sort_by'] ?? 'created_at';
        if (! in_array($sortBy, $allowedSortColumns, true)) {
            $sortBy = 'created_at';
        }

        $direction = strtolower($filters['direction'] ?? 'desc') === 'asc' ? 'asc' : 'desc';

        $query->orderBy($sortBy, $direction);

        $perPage = (int) ($filters['pagination'] ?? config('const.DEFAULT_PER_PAGE'));
        if ($perPage < 1) {
            $perPage = (int) config('const.DEFAULT_PER_PAGE');
        }

        return $query->paginate($perPage)->appends($filters);
    }
}
