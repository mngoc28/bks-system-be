<?php

declare(strict_types=1);

namespace App\Support\Bcp;

use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

/**
 * Logic thuần: phân loại stay + chọn tier (không truy vấn DB) — phục vụ test và CancellationPolicyResolver.
 */
final class CancellationPolicyTierMatcher
{
    /**
     * Số giờ từ `$at` tới đầu ngày check-in (theo timezone ứng dụng). Không âm.
     */
    public static function hoursBeforeCheckinStart(
        CarbonInterface $startDate,
        CarbonInterface $at,
        string $timezone,
    ): int {
        $checkinStart = Carbon::parse((string) $startDate, $timezone)->startOfDay();
        $atTz = $at->copy()->timezone($timezone);
        $seconds = $checkinStart->getTimestamp() - $atTz->getTimestamp();

        return max(0, (int) floor($seconds / 3600));
    }

    /**
     * `short` nếu số đêm < `longStayMinNights`, ngược lại `long`. Số đêm = max(1, diff ngày start/end).
     */
    public static function stayKind(
        CarbonInterface $startDate,
        CarbonInterface $endDate,
        int $longStayMinNights,
        string $timezone,
    ): string {
        $start = Carbon::parse((string) $startDate, $timezone)->startOfDay();
        $end = Carbon::parse((string) $endDate, $timezone)->startOfDay();
        $nights = (int) max(1, $start->diffInDays($end));

        return $nights >= $longStayMinNights ? 'long' : 'short';
    }

    /**
     * Mỗi phần tử gồm `hours_before_checkin_min`, `hours_before_checkin_max` (null = không trần),
     * cùng các key khác (`id`, `fee_percent`, …) được giữ khi trả về dòng khớp.
     *
     * @param Collection<int, array<string, mixed>> $tierRowsOrderedByMinDesc
     *
     * @return array<string, mixed>|null
     */
    public static function firstMatchingTierRow(Collection $tierRowsOrderedByMinDesc, int $hoursBefore): ?array
    {
        foreach ($tierRowsOrderedByMinDesc as $row) {
            $min = (int) $row['hours_before_checkin_min'];
            if ($hoursBefore < $min) {
                continue;
            }
            $max = $row['hours_before_checkin_max'] ?? null;
            if ($max !== null && $hoursBefore > (int) $max) {
                continue;
            }

            return $row;
        }

        return null;
    }
}
