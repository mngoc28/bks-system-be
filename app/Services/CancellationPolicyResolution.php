<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Kết quả resolve tier theo phiên bản chính sách + stay_kind + số giờ trước check-in.
 */
final readonly class CancellationPolicyResolution
{
    public function __construct(
        public string $policyVersion,
        public string $stayKind,
        public int $hoursBeforeCheckin,
        public ?int $matchedTierId,
        public ?float $feePercent,
        public ?float $refundPercent,
    ) {
    }

    /**
     * Fragment metadata timeline (B5.2) — không chứa PII.
     *
     * @return array<string, int|float|string|null>
     */
    public function toTimelineMetadataFragment(): array
    {
        return [
            'policy_version'          => $this->policyVersion,
            'stay_kind'               => $this->stayKind,
            'hours_before_checkin'    => $this->hoursBeforeCheckin,
            'policy_tier_id'          => $this->matchedTierId,
            'policy_fee_percent'      => $this->feePercent,
            'policy_refund_percent'   => $this->refundPercent,
        ];
    }
}
