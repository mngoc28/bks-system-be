<?php

declare(strict_types=1);

namespace App\Repositories\SettlementAdjustmentRepository;

use App\Models\SettlementAdjustment;
use App\Repositories\BaseRepository;
use Illuminate\Support\Collection;

/**
 * Class SettlementAdjustmentRepository
 *
 * @package App\Repositories\SettlementAdjustmentRepository
 */
class SettlementAdjustmentRepository extends BaseRepository implements SettlementAdjustmentRepositoryInterface
{
    /**
     * Get the model class name
     *
     * @return string
     */
    public function getModel(): string
    {
        return SettlementAdjustment::class;
    }

    /**
     * Get adjustments for a settlement period
     *
     * @param int $periodId
     * @return \Illuminate\Support\Collection
     */
    public function getByPeriod(int $periodId): Collection
    {
        return $this->model->newQuery()
            ->where('settlement_period_id', $periodId)
            ->with(['creator'])
            ->orderBy('created_at', 'asc')
            ->get();
    }
}
