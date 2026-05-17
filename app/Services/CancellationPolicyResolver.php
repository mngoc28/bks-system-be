<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Booking;
use App\Models\CancellationPolicyTier;
use App\Support\Bcp\CancellationPolicyTierMatcher;
use Carbon\Carbon;

final class CancellationPolicyResolver
{
    /**
     * Chọn tier phù hợp theo `stay_kind` (ngắn/dài hạn) và số giờ còn lại tới đầu ngày check-in.
     */
    public function resolveForBooking(Booking $booking, ?Carbon $at = null): CancellationPolicyResolution
    {
        $at = $at ?? Carbon::now();
        $tz = (string) config('app.timezone');
        $version = (string) config('bcp.baseline_policy_version', '2026-baseline-v1');

        $start = Carbon::parse((string) $booking->start_date, $tz);
        $end = Carbon::parse((string) $booking->end_date, $tz);

        $longMin = (int) config('bcp.long_stay_min_nights', 30);
        $stayKind = CancellationPolicyTierMatcher::stayKind($start, $end, $longMin, $tz);
        $hoursBefore = CancellationPolicyTierMatcher::hoursBeforeCheckinStart($start, $at, $tz);

        $candidates = CancellationPolicyTier::query()
            ->where('version', $version)
            ->where('stay_kind', $stayKind)
            ->orderByDesc('hours_before_checkin_min')
            ->get();

        /** @var \Illuminate\Support\Collection<int, array<string, mixed>> $rows */
        $rows = $candidates->map(static function (CancellationPolicyTier $t): array {
            return [
                'id'                        => (int) $t->id,
                'hours_before_checkin_min'  => (int) $t->hours_before_checkin_min,
                'hours_before_checkin_max'  => $t->hours_before_checkin_max !== null
                    ? (int) $t->hours_before_checkin_max
                    : null,
                'fee_percent'               => $t->fee_percent,
                'refund_percent'            => $t->refund_percent,
            ];
        });

        $match = CancellationPolicyTierMatcher::firstMatchingTierRow($rows, $hoursBefore);

        $fee = null;
        $refund = null;
        $tierId = null;
        if ($match !== null) {
            $tierId = (int) $match['id'];
            if ($match['fee_percent'] !== null) {
                $fee = (float) $match['fee_percent'];
            }
            if ($match['refund_percent'] !== null) {
                $refund = (float) $match['refund_percent'];
            }
        }

        return new CancellationPolicyResolution(
            $version,
            $stayKind,
            $hoursBefore,
            $tierId,
            $fee,
            $refund,
        );
    }
}
