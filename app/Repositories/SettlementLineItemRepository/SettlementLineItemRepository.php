<?php

declare(strict_types=1);

namespace App\Repositories\SettlementLineItemRepository;

use App\Models\SettlementLineItem;
use App\Repositories\BaseRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Class SettlementLineItemRepository
 *
 * @package App\Repositories\SettlementLineItemRepository
 */
class SettlementLineItemRepository extends BaseRepository implements SettlementLineItemRepositoryInterface
{
    /**
     * Get the model class name
     *
     * @return string
     */
    public function getModel(): string
    {
        return SettlementLineItem::class;
    }

    /**
     * Get paginated line items for a settlement period
     *
     * @param int $periodId
     * @param array $filters
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function paginateByPeriod(int $periodId, array $filters): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        $query->where('settlement_period_id', $periodId);

        // Eager load booking details to prevent N+1 queries.
        // booking.room.property allows rendering property details and room names in reports or UI.
        $query->with(['booking.room.property']);

        $allowedSortColumns = [
            'id',
            'booking_id',
            'checkout_date',
            'room_gmv',
            'services_gmv',
            'total_gmv',
            'commission_amount',
            'created_at',
        ];

        $sortBy = $filters['sort_by'] ?? 'checkout_date';
        if (! in_array($sortBy, $allowedSortColumns, true)) {
            $sortBy = 'checkout_date';
        }

        $direction = strtolower($filters['direction'] ?? 'desc') === 'asc' ? 'asc' : 'desc';
        $query->orderBy($sortBy, $direction);

        $perPage = (int) ($filters['pagination'] ?? config('const.DEFAULT_PER_PAGE'));
        if ($perPage < 1) {
            $perPage = (int) config('const.DEFAULT_PER_PAGE', 10);
        }

        return $query->paginate($perPage)->appends($filters);
    }
}
