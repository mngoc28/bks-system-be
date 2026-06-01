<?php

declare(strict_types=1);

namespace App\Repositories\SettlementAdjustmentRepository;

use App\Repositories\RepositoryInterface;
use Illuminate\Support\Collection;

/**
 * Interface SettlementAdjustmentRepositoryInterface
 *
 * @package App\Repositories\SettlementAdjustmentRepository
 */
interface SettlementAdjustmentRepositoryInterface extends RepositoryInterface
{
    /**
     * Get adjustments for a settlement period
     *
     * @param int $periodId
     * @return \Illuminate\Support\Collection
     */
    public function getByPeriod(int $periodId): Collection;
}
