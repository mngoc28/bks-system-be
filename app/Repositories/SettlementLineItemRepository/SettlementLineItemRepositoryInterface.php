<?php

declare(strict_types=1);

namespace App\Repositories\SettlementLineItemRepository;

use App\Repositories\RepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Interface SettlementLineItemRepositoryInterface
 *
 * @package App\Repositories\SettlementLineItemRepository
 */
interface SettlementLineItemRepositoryInterface extends RepositoryInterface
{
    /**
     * Get paginated line items for a settlement period
     *
     * @param int $periodId
     * @param array $filters
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function paginateByPeriod(int $periodId, array $filters): LengthAwarePaginator;
}
