<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * B7 / B5.4–B5.5: SLA resolve (p50/p90) và tỷ lệ pending "treo" theo `bcp.stale_request_hours`.
 */
final class BookingCancellationMetricsService
{
    /**
     * @return array{p50: int|null, p90: int|null, sample_size: int}
     */
    public function slaSecondsPercentiles(): array
    {
        $durationExpr = DB::getDriverName() === 'sqlite'
            ? '(CAST(strftime("%s", resolved_at) AS INTEGER) - CAST(strftime("%s", requested_at) AS INTEGER)) AS dur'
            : 'TIMESTAMPDIFF(SECOND, requested_at, resolved_at) AS dur';

        $rows = DB::table('booking_cancellation_requests')
            ->whereIn('status', ['approved', 'rejected'])
            ->whereNotNull('resolved_at')
            ->selectRaw($durationExpr)
            ->orderBy('dur')
            ->pluck('dur')
            ->map(static fn ($v): int => max(0, (int) $v))
            ->values()
            ->all();

        $n = count($rows);
        if ($n === 0) {
            return ['p50' => null, 'p90' => null, 'sample_size' => 0];
        }

        return [
            'p50'         => self::percentileFromSorted($rows, 0.50),
            'p90'         => self::percentileFromSorted($rows, 0.90),
            'sample_size' => $n,
        ];
    }

    /**
     * @return array{open: int, stale: int, stale_percent_of_open: float|null}
     */
    public function pendingStaleMetrics(): array
    {
        $open = (int) DB::table('booking_cancellation_requests')
            ->where('status', 'pending')
            ->count();

        $staleHours = (int) config('bcp.stale_request_hours', 48);
        $threshold = now()->subHours($staleHours);

        $stale = (int) DB::table('booking_cancellation_requests')
            ->where('status', 'pending')
            ->where('requested_at', '<', $threshold)
            ->count();

        $pct = $open > 0 ? round(100.0 * $stale / $open, 2) : null;

        return [
            'open'                   => $open,
            'stale'                  => $stale,
            'stale_percent_of_open'  => $pct,
        ];
    }

    /**
     * @return array{sla_seconds: array{p50: int|null, p90: int|null, sample_size: int}, pending_stale: array{open: int, stale: int, stale_percent_of_open: float|null}}
     */
    public function summary(): array
    {
        return [
            'sla_seconds'  => $this->slaSecondsPercentiles(),
            'pending_stale' => $this->pendingStaleMetrics(),
        ];
    }

    /**
     * @param list<int> $sortedAsc
     */
    private static function percentileFromSorted(array $sortedAsc, float $p): int
    {
        $n = count($sortedAsc);
        if ($n === 0) {
            return 0;
        }
        $idx = (int) floor($p * ($n - 1));

        return $sortedAsc[$idx];
    }
}
