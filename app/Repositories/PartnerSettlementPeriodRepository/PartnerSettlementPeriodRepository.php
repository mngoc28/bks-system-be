<?php

declare(strict_types=1);

namespace App\Repositories\PartnerSettlementPeriodRepository;

use App\Models\PartnerSettlementPeriod;
use App\Repositories\BaseRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Class PartnerSettlementPeriodRepository
 *
 * @package App\Repositories\PartnerSettlementPeriodRepository
 */
class PartnerSettlementPeriodRepository extends BaseRepository implements PartnerSettlementPeriodRepositoryInterface
{
    /**
     * Get the model class name
     *
     * @return string
     */
    public function getModel(): string
    {
        return PartnerSettlementPeriod::class;
    }

    /**
     * Get paginated settlement periods with filters
     *
     * @param array $filters
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function paginateWithFilters(array $filters): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        // Eager load relationships to prevent N+1 queries
        $query->with(['partner', 'adjustments']);

        // Filter by partner_id
        if (isset($filters['partner_id']) && $filters['partner_id'] !== '') {
            $query->where('partner_id', $filters['partner_id']);
        }

        // Filter by status
        if (isset($filters['status']) && $filters['status'] !== '') {
            $query->where('status', $filters['status']);
        }

        // Filter by date range (period_start)
        if (isset($filters['start_date']) && $filters['start_date'] !== '') {
            $query->where('period_start', '>=', $filters['start_date']);
        }

        // Filter by date range (period_end)
        if (isset($filters['end_date']) && $filters['end_date'] !== '') {
            $query->where('period_end', '<=', $filters['end_date']);
        }

        $allowedSortColumns = [
            'id',
            'partner_id',
            'period_start',
            'period_end',
            'total_gmv',
            'total_commission',
            'commission_rate',
            'status',
            'issue_date',
            'issued_at',
            'paid_at',
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
            $perPage = (int) config('const.DEFAULT_PER_PAGE', 10);
        }

        return $query->paginate($perPage)->appends($filters);
    }
}
