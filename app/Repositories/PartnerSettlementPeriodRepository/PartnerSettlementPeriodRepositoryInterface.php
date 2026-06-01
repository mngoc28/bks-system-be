<?php

declare(strict_types=1);

namespace App\Repositories\PartnerSettlementPeriodRepository;

use App\Repositories\RepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Interface PartnerSettlementPeriodRepositoryInterface
 *
 * @package App\Repositories\PartnerSettlementPeriodRepository
 */
interface PartnerSettlementPeriodRepositoryInterface extends RepositoryInterface
{
    /**
     * Get paginated settlement periods with filters
     *
     * @param array $filters
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function paginateWithFilters(array $filters): LengthAwarePaginator;
}
