<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\PartnerSettlementPeriod;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Event phát hành kỳ đối soát công nợ hoa hồng của đối tác.
 */
final class SettlementPeriodIssued
{
    use Dispatchable;
    use SerializesModels;

    /**
     * @var \App\Models\PartnerSettlementPeriod
     */
    public PartnerSettlementPeriod $period;

    /**
     * Create a new event instance.
     *
     * @param \App\Models\PartnerSettlementPeriod $period
     */
    public function __construct(PartnerSettlementPeriod $period)
    {
        $this->period = $period;
    }
}
