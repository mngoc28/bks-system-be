<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\BookingStatus;
use App\Repositories\BookingRepository\BookingRepositoryInterface;
use App\Repositories\RoomsRepository\RoomsRepositoryInterface;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Aggregates the four headline KPIs displayed on the Partner dashboard:
 *  - Occupancy today
 *  - Gross Merchandise Value (GMV) MTD
 *  - Net Revenue MTD (GMV minus 5% commission)
 *  - Average time to confirm pending bookings (last 30 days)
 *  - Currently pending booking count (for the alert banner)
 *
 * Each query is wrapped in a 60-second Redis-style cache slot keyed by partner
 * id. Invalidation by booking events is implemented in Phase 4 (T4.3); for
 * Phase 1 we accept a worst-case staleness of 60 seconds, which still respects
 * the Time-to-confirm SLA window of 5 minutes.
 *
 * Commission rate is intentionally a class constant rather than a config key
 * because the contract with partners (5%) is fixed for this release; once a
 * tiered model is introduced, lift it into config/billing.php.
 */
/**
 * Note: not declared `final` so unit tests can override `computeAvgConfirmSeconds`
 * to avoid the DB facade. All other public methods are stable.
 */
class PartnerKpiService
{
    public const COMMISSION_RATE = 0.05;
    public const CACHE_TTL_SECONDS = 60;

    public function __construct(
        private readonly BookingRepositoryInterface $bookingRepository,
        private readonly RoomsRepositoryInterface $roomsRepository,
    ) {
    }

    /**
     * Returns the consolidated KPI payload used by GET /partner/dashboard/kpis.
     *
     * @return array{
     *     success: bool,
     *     data: array{
     *         occupancyRate: float,
     *         occupiedRooms: int,
     *         totalRooms: int,
     *         gmvMtd: float,
     *         netRevenueMtd: float,
     *         commissionRate: float,
     *         avgConfirmSeconds: int|null,
     *         pendingCount: int,
     *         calculatedAt: string
     *     }|null,
     *     message: string
     * }
     */
    public function getDashboardKpis(int $partnerId): array
    {
        $cacheKey = $this->key($partnerId, 'dashboard');

        try {
            $payload = Cache::remember(
                $cacheKey,
                self::CACHE_TTL_SECONDS,
                fn (): array => $this->computeDashboard($partnerId),
            );

            return [
                'success' => true,
                'data'    => $payload,
                'message' => __('dashboard.messages.stats_fetched_successfully'),
            ];
        } catch (Exception $e) {
            Log::error('PartnerKpiService::getDashboardKpis failed', [
                'partner_id' => $partnerId,
                'error'      => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'data'    => null,
                'message' => __('dashboard.messages.stats_fetch_failed'),
            ];
        }
    }

    /**
     * Returns 30-day occupancy chart data for the authenticated partner.
     *
     * @return array{success: bool, data: array<int, array{date: string, occupancyRate: float}>|null, message: string}
     */
    public function getOccupancyChart(int $partnerId): array
    {
        try {
            $payload = Cache::remember(
                $this->key($partnerId, 'charts:occupancy'),
                self::CACHE_TTL_SECONDS,
                fn (): array => $this->computeOccupancyChart($partnerId),
            );

            return [
                'success' => true,
                'data'    => $payload,
                'message' => __('dashboard.messages.stats_fetched_successfully'),
            ];
        } catch (Exception $e) {
            Log::error('PartnerKpiService::getOccupancyChart failed', [
                'partner_id' => $partnerId,
                'error'      => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'data'    => null,
                'message' => __('dashboard.messages.stats_fetch_failed'),
            ];
        }
    }

    /**
     * Returns 30-day GMV / net revenue chart data for the authenticated partner.
     *
     * @return array{
     *     success: bool,
     *     data: array<int, array{date: string, gmv: float, netRevenue: float}>|null,
     *     message: string
     * }
     */
    public function getGmvChart(int $partnerId): array
    {
        try {
            $payload = Cache::remember(
                $this->key($partnerId, 'charts:gmv'),
                self::CACHE_TTL_SECONDS,
                fn (): array => $this->computeGmvChart($partnerId),
            );

            return [
                'success' => true,
                'data'    => $payload,
                'message' => __('dashboard.messages.stats_fetched_successfully'),
            ];
        } catch (Exception $e) {
            Log::error('PartnerKpiService::getGmvChart failed', [
                'partner_id' => $partnerId,
                'error'      => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'data'    => null,
                'message' => __('dashboard.messages.stats_fetch_failed'),
            ];
        }
    }

    /**
     * Cache slots owned by this service. Invalidation stays explicit so it
     * works with array/file cache drivers as well as Redis.
     *
     * @return array<int, string>
     */
    public static function cacheKeysForPartner(int $partnerId): array
    {
        return [
            sprintf('partner:%d:kpi:dashboard', $partnerId),
            sprintf('partner:%d:kpi:charts:occupancy', $partnerId),
            sprintf('partner:%d:kpi:charts:gmv', $partnerId),
        ];
    }

    /**
     * Build the full KPI payload. Kept public so admins/jobs can warm the cache
     * out-of-band if needed.
     *
     * @return array{
     *     occupancyRate: float,
     *     occupiedRooms: int,
     *     totalRooms: int,
     *     gmvMtd: float,
     *     netRevenueMtd: float,
     *     commissionRate: float,
     *     avgConfirmSeconds: int|null,
     *     pendingCount: int,
     *     calculatedAt: string
     * }
     */
    public function computeDashboard(int $partnerId): array
    {
        $totalRooms = (int) $this->roomsRepository->countRoomsForPartner($partnerId);
        $vacantRooms = (int) $this->roomsRepository->getEmptyRoomsForPartner($partnerId);
        $occupiedRooms = max(0, $totalRooms - $vacantRooms);
        $occupancyRate = $totalRooms > 0
            ? round(($occupiedRooms / $totalRooms) * 100, 2)
            : 0.0;

        $monthStart = Carbon::now('Asia/Ho_Chi_Minh')->startOfMonth()->format('Y-m-d');
        $monthEnd = Carbon::now('Asia/Ho_Chi_Minh')->endOfMonth()->format('Y-m-d');

        $revenueRows = $this->bookingRepository->getRevenueByMonthForPartner(
            $partnerId,
            $monthStart,
            $monthEnd,
        );
        $gmvMtd = (float) $revenueRows->sum('revenue');
        $netRevenueMtd = round($gmvMtd * (1 - self::COMMISSION_RATE), 2);

        return [
            'occupancyRate'      => (float) $occupancyRate,
            'occupiedRooms'      => $occupiedRooms,
            'totalRooms'         => $totalRooms,
            'gmvMtd'             => round($gmvMtd, 2),
            'netRevenueMtd'      => $netRevenueMtd,
            'commissionRate'     => self::COMMISSION_RATE,
            'avgConfirmSeconds'  => $this->computeAvgConfirmSeconds($partnerId),
            'pendingCount'       => $this->computePendingCount($partnerId),
            'calculatedAt'       => Carbon::now('Asia/Ho_Chi_Minh')->toIso8601String(),
        ];
    }

    /**
     * Compute occupancy per day for the last 30 days, including today.
     *
     * Occupancy is based on distinct rooms with a confirmed/completed booking
     * overlapping the date. Cancelled bookings are ignored.
     *
     * @return array<int, array{date: string, occupancyRate: float}>
     */
    public function computeOccupancyChart(int $partnerId): array
    {
        $totalRooms = (int) $this->roomsRepository->countRoomsForPartner($partnerId);
        $rangeEnd = Carbon::now('Asia/Ho_Chi_Minh')->startOfDay();
        $rangeStart = $rangeEnd->copy()->subDays(29);

        $activeBookings = DB::table('bookings')
            ->join('rooms', 'bookings.room_id', '=', 'rooms.id')
            ->join('properties', 'rooms.property_id', '=', 'properties.id')
            ->where('properties.user_id', $partnerId)
            ->whereIn('bookings.status', [
                BookingStatus::CONFIRMED->value,
                BookingStatus::COMPLETED->value,
            ])
            ->where('bookings.start_date', '<=', $rangeEnd->toDateString())
            ->where('bookings.end_date', '>', $rangeStart->toDateString())
            ->get([
                'bookings.room_id',
                'bookings.start_date',
                'bookings.end_date',
            ]);

        return $this->dateSeries($rangeStart, $rangeEnd)
            ->map(function (Carbon $date) use ($activeBookings, $totalRooms): array {
                $dateString = $date->toDateString();
                $occupiedRooms = $activeBookings
                    ->filter(
                        fn ($booking): bool => $booking->start_date <= $dateString
                            && $booking->end_date > $dateString,
                    )
                    ->pluck('room_id')
                    ->unique()
                    ->count();

                return [
                    'date'          => $dateString,
                    'occupancyRate' => $totalRooms > 0
                        ? round(($occupiedRooms / $totalRooms) * 100, 2)
                        : 0.0,
                ];
            })
            ->values()
            ->all();
    }

    /**
     * Compute daily GMV and net revenue for bookings starting in the last 30 days.
     *
     * @return array<int, array{date: string, gmv: float, netRevenue: float}>
     */
    public function computeGmvChart(int $partnerId): array
    {
        $rangeEnd = Carbon::now('Asia/Ho_Chi_Minh')->startOfDay();
        $rangeStart = $rangeEnd->copy()->subDays(29);

        $revenueByDate = DB::table('bookings')
            ->join('rooms', 'bookings.room_id', '=', 'rooms.id')
            ->join('properties', 'rooms.property_id', '=', 'properties.id')
            ->leftJoin('room_prices', 'bookings.price_id', '=', 'room_prices.id')
            ->where('properties.user_id', $partnerId)
            ->where('bookings.status', '!=', BookingStatus::CANCELLED->value)
            ->whereBetween('bookings.start_date', [$rangeStart->toDateString(), $rangeEnd->toDateString()])
            ->selectRaw('DATE(bookings.start_date) as date, COALESCE(SUM(room_prices.price), 0) as gmv')
            ->groupByRaw('DATE(bookings.start_date)')
            ->pluck('gmv', 'date');

        return $this->dateSeries($rangeStart, $rangeEnd)
            ->map(function (Carbon $date) use ($revenueByDate): array {
                $dateString = $date->toDateString();
                $gmv = round((float) ($revenueByDate[$dateString] ?? 0), 2);

                return [
                    'date'       => $dateString,
                    'gmv'        => $gmv,
                    'netRevenue' => round($gmv * (1 - self::COMMISSION_RATE), 2),
                ];
            })
            ->values()
            ->all();
    }

    /**
     * Average seconds between booking creation and confirmation in the last
     * 30 days, excluding rows seeded by the backfill command (those have a
     * timeline event with `event_type = 'backfilled'`).
     *
     * Returns null when there is no qualifying data, signaling the UI to
     * render a placeholder rather than 0.
     *
     * Marked `protected` so unit tests can override it without touching the DB.
     */
    protected function computeAvgConfirmSeconds(int $partnerId): ?int
    {
        $since = Carbon::now('Asia/Ho_Chi_Minh')->subDays(30)->toDateTimeString();

        $value = DB::table('bookings')
            ->join('rooms', 'bookings.room_id', '=', 'rooms.id')
            ->join('properties', 'rooms.property_id', '=', 'properties.id')
            ->where('properties.user_id', $partnerId)
            ->where('bookings.status', BookingStatus::CONFIRMED->value)
            ->whereNotNull('bookings.confirmed_at')
            ->where('bookings.confirmed_at', '>=', $since)
            ->whereNotExists(function ($sub): void {
                $sub->select(DB::raw(1))
                    ->from('booking_timeline_events as bte')
                    ->whereColumn('bte.booking_id', 'bookings.id')
                    ->where('bte.event_type', 'backfilled');
            })
            ->select(DB::raw(
                'AVG(TIMESTAMPDIFF(SECOND, bookings.created_at, bookings.confirmed_at)) as __avg_confirm_seconds',
            ))
            ->value('__avg_confirm_seconds');

        return $value === null ? null : (int) round((float) $value);
    }

    /**
     * Count of bookings still waiting on the partner.
     */
    private function computePendingCount(int $partnerId): int
    {
        return (int) $this->bookingRepository->countBookingsForPartner($partnerId, [
            'status' => BookingStatus::PENDING->value,
        ]);
    }

    /**
     * Cache key namespaced under partner id so per-partner invalidation works.
     */
    private function key(int $partnerId, string $bucket): string
    {
        return sprintf('partner:%d:kpi:%s', $partnerId, $bucket);
    }

    /**
     * @return Collection<int, Carbon>
     */
    private function dateSeries(Carbon $start, Carbon $end): Collection
    {
        $dates = collect();
        $cursor = $start->copy();

        while ($cursor <= $end) {
            $dates->push($cursor->copy());
            $cursor->addDay();
        }

        return $dates;
    }
}
