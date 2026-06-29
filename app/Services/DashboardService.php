<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\BookingStatus;
use App\Enums\RoomStatus;
use App\Enums\Status;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Repositories\BookingRepository\BookingRepositoryInterface;
use App\Repositories\PropertyRepository\PropertyRepositoryInterface;
use App\Repositories\RoomsRepository\RoomsRepositoryInterface;
use App\Repositories\RoomMaintenanceRepository\RoomMaintenanceRepositoryInterface;
use App\Repositories\ServiceRepository\ServiceRepositoryInterface;
use App\Repositories\UsersRepository\UsersRepositoryInterface;
use App\Services\ConflictChecker;
use Exception;
use Illuminate\Support\Facades\Log;

final class DashboardService
{
    protected RoomsRepositoryInterface $roomsRepository;
    protected BookingRepositoryInterface $bookingRepository;
    protected PropertyRepositoryInterface $propertyRepository;
    protected ServiceRepositoryInterface $serviceRepository;
    protected UsersRepositoryInterface $usersRepository;
    protected RoomMaintenanceRepositoryInterface $roomMaintenanceRepository;
    protected ConflictChecker $conflictChecker;

    public function __construct(
        RoomsRepositoryInterface $roomsRepository,
        BookingRepositoryInterface $bookingRepository,
        PropertyRepositoryInterface $propertyRepository,
        ServiceRepositoryInterface $serviceRepository,
        UsersRepositoryInterface $usersRepository,
        RoomMaintenanceRepositoryInterface $roomMaintenanceRepository,
        ConflictChecker $conflictChecker,
    ) {
        $this->roomsRepository = $roomsRepository;
        $this->bookingRepository = $bookingRepository;
        $this->propertyRepository = $propertyRepository;
        $this->serviceRepository = $serviceRepository;
        $this->usersRepository = $usersRepository;
        $this->roomMaintenanceRepository = $roomMaintenanceRepository;
        $this->conflictChecker = $conflictChecker;
    }

    /**
     * Get information about the number of users
     * @param $request
     * @return array
     */
    public function getTotalUsers($request): array
    {
        try {
            $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
            $endDate   = $request->input('end_date', now()->endOfMonth()->format('Y-m-d'));
            $cacheKey  = "admin_dashboard_total_users_{$startDate}_{$endDate}";

            $data = Cache::remember($cacheKey, 60, function () use ($request) {
                $totalUsers = (int) $this->usersRepository->countRecord([
                    "role" => "user",
                ]);
                $newUserThisMonth = (int) $this->usersRepository->countNewUserInCurrentMonth(
                    $request,
                    "user"
                );
                $userPending = (int) $this->usersRepository->countRecord([
                    "status" => Status::PENDING->value,
                    "role" => "user",
                ]);
                $userBlock = (int) $this->usersRepository->countRecord([
                    "status" => Status::BLOCKED->value,
                    "role" => "user",
                ]);

                return [
                    "totalUsers" => $totalUsers,
                    "newUserThisMonth" => $newUserThisMonth,
                    "userPending" => $userPending,
                    "userBlock" => $userBlock,
                ];
            });

            return [
                "success" => true,
                "data" => $data,
                "message" => __(
                    "dashboard.messages.stats_fetched_successfully"
                ),
            ];
        } catch (Exception $e) {
            Log::error(
                "Error in DashboardService::totalUser: " . $e->getMessage(),
                ["exception" => $e]
            );

            return [
                "success" => false,
                "data" => null,
                "message" => __("dashboard.messages.stats_fetch_failed"),
            ];
        }
    }

    /**
     * Get information about the number of partner
     * @param $request
     * @return array
     */
    public function getTotalPartner($request): array
    {
        try {
            $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
            $endDate   = $request->input('end_date', now()->endOfMonth()->format('Y-m-d'));
            $cacheKey  = "admin_dashboard_total_partners_{$startDate}_{$endDate}";

            $data = Cache::remember($cacheKey, 60, function () use ($request) {
                $totalPartners = (int) $this->usersRepository->countRecord([
                    "role" => "partner",
                ]);
                $newUPartnerThisMonth = (int) $this->usersRepository->countNewUserInCurrentMonth(
                    $request,
                    "partner"
                );
                $partnerPending = (int) $this->usersRepository->countRecord([
                    "status" => Status::PENDING_APPROVAL->value,
                    "role" => "partner",
                ]);
                $partnerBlock = (int) $this->usersRepository->countRecord([
                    "status" => Status::BLOCKED->value,
                    "role" => "partner",
                ]);

                return [
                    "totalPartners" => $totalPartners,
                    "newUPartnerThisMonth" => $newUPartnerThisMonth,
                    "partnerPending" => $partnerPending,
                    "partnerBlock" => $partnerBlock,
                ];
            });

            return [
                "success" => true,
                "data" => $data,
                "message" => __(
                    "dashboard.messages.stats_fetched_successfully"
                ),
            ];
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return [
                "success" => false,
                "data" => null,
                "message" => __("dashboard.messages.stats_fetch_failed"),
            ];
        }
    }

    /**
     * Total number of properties in the system.
     *
     * @return array
     */
    public function getSystemProperty(): array
    {
        try {
            $data = Cache::remember('admin_dashboard_system_property', 60, function () {
                $totalProperties = $this->propertyRepository->countRecord();
                return [
                    "totalProperties" => $totalProperties,
                ];
            });
            return [
                "success" => true,
                "data" => $data,
                "message" => __(
                    "dashboard.messages.stats_fetched_successfully"
                ),
            ];
        } catch (Exception $e) {
            Log::error("Get system property fail" . $e->getMessage());
            return [
                "success" => false,
                "data" => null,
                "message" => __("dashboard.messages.stats_fetch_failed"),
            ];
        }
    }

    /**
     * get rooms in the system
     * @return array
     */
    public function getSystemRoom(): array
    {
        try {
            $data = Cache::remember('admin_dashboard_system_room', 60, function () {
                $totalRooms = $this->roomsRepository->countRecord();
                $totalPrivateRooms = $this->roomsRepository->countRecord([
                    "status" => RoomStatus::PRIVATE,
                ]);
                $totalPublicRooms = $this->roomsRepository->countRecord([
                    "status" => RoomStatus::PUBLIC,
                ]);
                $totalAvailableRooms = $this->roomsRepository->getEmptyRooms();
                return [
                    "totalRooms" => $totalRooms,
                    "totalPrivateRooms" => $totalPrivateRooms,
                    "totalPublicRooms" => $totalPublicRooms,
                    "totalAvailableRooms" => $totalAvailableRooms,
                ];
            });
            return [
                "success" => true,
                "data" => $data,
                "message" => __(
                    "dashboard.messages.stats_fetched_successfully"
                ),
            ];
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return [
                "success" => false,
                "data" => null,
                "message" => __("dashboard.messages.stats_fetch_failed"),
            ];
        }
    }

    /**
     * Get bookings per month
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function getBookingsPerMonth($request): array
    {
        try {
            $startDate = $request->input(
                "start_date",
                now()
                    ->startOfMonth()
                    ->format("Y-m-d")
            );
            $endDate = $request->input(
                "end_date",
                now()
                    ->endOfMonth()
                    ->format("Y-m-d")
            );

            $cacheKey = "admin_bookings_per_month_{$startDate}_{$endDate}";
            $data = Cache::remember($cacheKey, 60, function () use ($startDate, $endDate) {
                $bookingsPerMonth = $this->bookingRepository->getBookingsPerMonth(
                    $startDate,
                    $endDate
                );
                return [
                    "bookingsPerMonth" => $bookingsPerMonth,
                    "dateRange" => [
                        "startDate" => $startDate,
                        "endDate" => $endDate,
                    ],
                ];
            });

            return [
                "success" => true,
                "data" => $data,
                "message" => __(
                    "dashboard.messages.bookings_per_month_fetched"
                ),
            ];
        } catch (\Exception $e) {
            Log::error(
                "Error fetching bookings per month: " . $e->getMessage()
            );

            return [
                "success" => false,
                "data" => null,
                "message" => __(
                    "dashboard.messages.bookings_per_month_fetch_failed"
                ),
            ];
        }
    }

    /**
     * Booking volume trend by day within the selected range (fills zero days).
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function getBookingsTrend($request): array
    {
        try {
            $startDate = $request->input(
                'start_date',
                now()->subDays(29)->format('Y-m-d')
            );
            $endDate = $request->input('end_date', now()->format('Y-m-d'));

            $start = Carbon::parse($startDate)->startOfDay();
            $end = Carbon::parse($endDate)->startOfDay();

            if ($start->gt($end)) {
                $start = $end->copy();
            }

            if ($start->diffInDays($end) > 90) {
                $start = $end->copy()->subDays(89);
                $startDate = $start->toDateString();
            }

            $cacheKey = "admin_bookings_trend_{$startDate}_{$endDate}";
            $data = Cache::remember($cacheKey, 60, function () use ($startDate, $endDate, $start, $end) {
                $countsByDate = $this->bookingRepository
                    ->getBookingsPerDay($startDate, $endDate)
                    ->keyBy('date');

                $points = [];
                foreach (CarbonPeriod::create($start, $end) as $day) {
                    $dateString = $day->toDateString();
                    $points[] = [
                        'date' => $dateString,
                        'total' => (int) ($countsByDate[$dateString]['total'] ?? 0),
                    ];
                }

                return [
                    'points' => $points,
                    'granularity' => 'day',
                    'dateRange' => [
                        'startDate' => $startDate,
                        'endDate' => $endDate,
                    ],
                ];
            });

            return [
                'success' => true,
                'data' => $data,
                'message' => __('dashboard.messages.bookings_trend_fetched'),
            ];
        } catch (\Exception $e) {
            Log::error('Error fetching bookings trend: ' . $e->getMessage());

            return [
                'success' => false,
                'data' => null,
                'message' => __('dashboard.messages.bookings_trend_fetch_failed'),
            ];
        }
    }

    /**
     * Get revenue per month
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function getRevenuePerMonth($request): array
    {
        try {
            $startDate = $request->input(
                "start_date",
                now()
                    ->startOfMonth()
                    ->format("Y-m-d")
            );
            $endDate = $request->input(
                "end_date",
                now()
                    ->endOfMonth()
                    ->format("Y-m-d")
            );

            $cacheKey = "admin_revenue_per_month_{$startDate}_{$endDate}";
            $data = Cache::remember($cacheKey, 60, function () use ($startDate, $endDate) {
                $revenueByMonth = $this->bookingRepository->getRevenueByMonth(
                    $startDate,
                    $endDate
                );

                $totalRevenue = $revenueByMonth->sum("revenue");

                return [
                    "revenueByMonth" => $revenueByMonth,
                    "totalRevenue" => (float) $totalRevenue,
                    "dateRange" => [
                        "startDate" => $startDate,
                        "endDate" => $endDate,
                    ],
                ];
            });

            return [
                "success" => true,
                "data" => $data,
                "message" => __("dashboard.messages.revenue_per_month_fetched"),
            ];
        } catch (\Exception $e) {
            Log::error("Error fetching revenue per month: " . $e->getMessage());

            return [
                "success" => false,
                "data" => null,
                "message" => __(
                    "dashboard.messages.revenue_per_month_fetch_failed"
                ),
            ];
        }
    }

    /**
     * Get bookings count grouped by property (admin).
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function getAllPropertiesBookingsCount($request): array
    {
        try {
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');

            $cacheKey = "admin_properties_bookings_count_" . ($startDate ?? 'all') . "_" . ($endDate ?? 'all');
            $data = Cache::remember($cacheKey, 60, function () use ($startDate, $endDate) {
                return $this->bookingRepository->getBookingsByProperty($startDate, $endDate);
            });

            return [
                "success" => true,
                "data" => $data,
                "message" => __(
                    "dashboard.messages.all_properties_bookings_count_fetched"
                ),
            ];
        } catch (\Exception $e) {
            Log::error(
                "Error fetching all properties bookings count: " .
                    $e->getMessage()
            );

            return [
                "success" => false,
                "data" => null,
                "message" => __(
                    "dashboard.messages.all_properties_bookings_count_fetch_failed"
                ),
            ];
        }
    }

    /**
     * Booking status breakdown for admin analytics (by start_date range).
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function getBookingStatusBreakdown($request): array
    {
        try {
            $startDate = $request->input(
                'start_date',
                now()->subDays(30)->format('Y-m-d')
            );
            $endDate = $request->input('end_date', now()->format('Y-m-d'));

            $cacheKey = "admin_booking_status_breakdown_{$startDate}_{$endDate}";
            $data = Cache::remember($cacheKey, 60, function () use ($startDate, $endDate) {
                $breakdown = $this->bookingRepository->getBookingStatusBreakdown($startDate, $endDate);
                return [
                    'breakdown' => $breakdown,
                    'dateRange' => [
                        'startDate' => $startDate,
                        'endDate' => $endDate,
                    ],
                ];
            });

            return [
                'success' => true,
                'data' => $data,
                'message' => __('dashboard.messages.booking_status_breakdown_fetched'),
            ];
        } catch (\Exception $e) {
            Log::error('Error fetching booking status breakdown: ' . $e->getMessage());

            return [
                'success' => false,
                'data' => null,
                'message' => __('dashboard.messages.booking_status_breakdown_fetch_failed'),
            ];
        }
    }

    /**
     * System-wide occupancy trend for admin analytics.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function getOccupancyChartForAdmin($request): array
    {
        try {
            $rangeEnd = Carbon::parse(
                $request->input('end_date', now()->format('Y-m-d')),
                'Asia/Ho_Chi_Minh'
            )->startOfDay();
            $rangeStart = Carbon::parse(
                $request->input('start_date', $rangeEnd->copy()->subDays(29)->format('Y-m-d')),
                'Asia/Ho_Chi_Minh'
            )->startOfDay();

            if ($rangeStart->gt($rangeEnd)) {
                $rangeStart = $rangeEnd->copy();
            }

            if ($rangeStart->diffInDays($rangeEnd) > 90) {
                $rangeStart = $rangeEnd->copy()->subDays(89);
            }

            $startDateStr = $rangeStart->toDateString();
            $endDateStr = $rangeEnd->toDateString();

            $cacheKey = "admin_occupancy_chart_{$startDateStr}_{$endDateStr}";
            $data = Cache::remember($cacheKey, 60, function () use ($rangeStart, $rangeEnd, $startDateStr, $endDateStr) {
                $totalRooms = (int) $this->roomsRepository->countRecord();

                $activeBookings = DB::table('bookings')
                    ->whereIn('bookings.status', [
                        BookingStatus::CONFIRMED->value,
                        BookingStatus::COMPLETED->value,
                        BookingStatus::PENDING_CANCELLATION->value,
                    ])
                    ->where('bookings.start_date', '<=', $endDateStr)
                    ->where('bookings.end_date', '>', $startDateStr)
                    ->get([
                        'bookings.room_id',
                        'bookings.start_date',
                        'bookings.end_date',
                    ]);

                $series = [];
                for ($cursor = $rangeStart->copy(); $cursor->lte($rangeEnd); $cursor->addDay()) {
                    $dateString = $cursor->toDateString();
                    $occupiedRooms = $activeBookings
                        ->filter(
                            static fn ($booking): bool => $booking->start_date <= $dateString
                                && $booking->end_date > $dateString,
                        )
                        ->pluck('room_id')
                        ->unique()
                        ->count();

                    $series[] = [
                        'date' => $dateString,
                        'occupancyRate' => $totalRooms > 0
                            ? round(($occupiedRooms / $totalRooms) * 100, 2)
                            : 0.0,
                    ];
                }

                return [
                    'points' => $series,
                    'dateRange' => [
                        'startDate' => $startDateStr,
                        'endDate' => $endDateStr,
                    ],
                    'totalRooms' => $totalRooms,
                ];
            });

            return [
                'success' => true,
                'data' => $data,
                'message' => __('dashboard.messages.occupancy_chart_fetched'),
            ];
        } catch (\Exception $e) {
            Log::error('Error fetching admin occupancy chart: ' . $e->getMessage());

            return [
                'success' => false,
                'data' => null,
                'message' => __('dashboard.messages.occupancy_chart_fetch_failed'),
            ];
        }
    }

    // =========================================================================
    // PARTNER METHODS
    // =========================================================================

    /**
     * Get properties count for partner.
     *
     * @param int $partnerId
     * @return array
     */
    public function getSystemPropertyForPartner(int $partnerId): array
    {
        try {
            $totalProperties = $this->propertyRepository->countRecord([
                'user_id' => $partnerId
            ]);
            return [
                "success" => true,
                "data" => [
                    "totalProperties" => $totalProperties,
                ],
                "message" => __("dashboard.messages.stats_fetched_successfully"),
            ];
        } catch (Exception $e) {
            Log::error("Partner get system property fail: " . $e->getMessage());
            return [
                "success" => false,
                "data" => null,
                "message" => __("dashboard.messages.stats_fetch_failed"),
            ];
        }
    }

    /**
     * Get rooms count for partner
     *
     * @param int $partnerId
     * @return array
     */
    public function getSystemRoomForPartner(int $partnerId): array
    {
        try {
            $totalRooms = $this->roomsRepository->countRoomsForPartner($partnerId);
            $totalPrivateRooms = $this->roomsRepository->countRoomsForPartner($partnerId, [
                "rooms.status" => RoomStatus::PRIVATE,
            ]);
            $totalPublicRooms = $this->roomsRepository->countRoomsForPartner($partnerId, [
                "rooms.status" => RoomStatus::PUBLIC,
            ]);
            $totalAvailableRooms = $this->roomsRepository->getEmptyRoomsForPartner($partnerId);
            return [
                "success" => true,
                "data" => [
                    "totalRooms" => $totalRooms,
                    "totalPrivateRooms" => $totalPrivateRooms,
                    "totalPublicRooms" => $totalPublicRooms,
                    "totalAvailableRooms" => $totalAvailableRooms,
                ],
                "message" => __("dashboard.messages.stats_fetched_successfully"),
            ];
        } catch (Exception $e) {
            Log::error("Partner get system room fail: " . $e->getMessage());
            return [
                "success" => false,
                "data" => null,
                "message" => __("dashboard.messages.stats_fetch_failed"),
            ];
        }
    }

    /**
     * Get bookings per month for partner
     *
     * @param int $partnerId
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function getBookingsPerMonthForPartner(int $partnerId, $request): array
    {
        try {
            $startDate = $request->input("start_date", now()->startOfYear()->format("Y-m-d"));
            $endDate = $request->input("end_date", now()->endOfMonth()->format("Y-m-d"));

            $bookingsPerMonth = $this->bookingRepository->getBookingsPerMonthForPartner(
                $partnerId,
                $startDate,
                $endDate
            );

            return [
                "success" => true,
                "data" => [
                    "bookingsPerMonth" => $bookingsPerMonth,
                    "dateRange" => [
                        "startDate" => $startDate,
                        "endDate" => $endDate,
                    ],
                ],
                "message" => __("dashboard.messages.bookings_per_month_fetched"),
            ];
        } catch (\Exception $e) {
            Log::error("Partner get bookings per month fail: " . $e->getMessage());
            return [
                "success" => false,
                "data" => null,
                "message" => __("dashboard.messages.bookings_per_month_fetch_failed"),
            ];
        }
    }

    /**
     * Get revenue per month for partner
     *
     * @param int $partnerId
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function getRevenuePerMonthForPartner(int $partnerId, $request): array
    {
        try {
            $startDate = $request->input("start_date", now()->startOfYear()->format("Y-m-d"));
            $endDate = $request->input("end_date", now()->endOfMonth()->format("Y-m-d"));

            $revenueByMonth = $this->bookingRepository->getRevenueByMonthForPartner(
                $partnerId,
                $startDate,
                $endDate
            );

            $totalRevenue = $revenueByMonth->sum("revenue");

            return [
                "success" => true,
                "data" => [
                    "revenueByMonth" => $revenueByMonth,
                    "totalRevenue" => (float) $totalRevenue,
                    "dateRange" => [
                        "startDate" => $startDate,
                        "endDate" => $endDate,
                    ],
                ],
                "message" => __("dashboard.messages.revenue_per_month_fetched"),
            ];
        } catch (\Exception $e) {
            Log::error("Partner get revenue per month fail: " . $e->getMessage());
            return [
                "success" => false,
                "data" => null,
                "message" => __("dashboard.messages.revenue_per_month_fetch_failed"),
            ];
        }
    }

    /**
     * Get bookings count grouped by property for partner.
     *
     * @param int $partnerId
     * @return array
     */
    public function getAllPropertiesBookingsCountForPartner(int $partnerId): array
    {
        try {
            $bookingsByProperty = $this->bookingRepository->getBookingsByPropertyForPartner($partnerId);

            return [
                "success" => true,
                "data" => $bookingsByProperty,
                "message" => __("dashboard.messages.all_properties_bookings_count_fetched"),
            ];
        } catch (\Exception $e) {
            Log::error("Partner get properties bookings count fail: " . $e->getMessage());
            return [
                "success" => false,
                "data" => null,
                "message" => __("dashboard.messages.all_properties_bookings_count_fetch_failed"),
            ];
        }
    }

    /**
     * Get dashboard stats for partner
     *
     * @param int $partnerId
     * @return array
     */
    public function getStatsForPartner(int $partnerId, ?int $propertyId = null): array
    {
        try {
            $propertyFilter = $propertyId !== null ? ['property_id' => $propertyId] : [];
            $roomFilters = $propertyId !== null ? ['rooms.property_id' => $propertyId] : [];

            $totalProperties = $propertyId !== null
                ? 1
                : (int) $this->propertyRepository->countRecord(['user_id' => $partnerId]);
            $totalRooms = $this->roomsRepository->countRoomsForPartner($partnerId, $roomFilters);
            $vacantRooms = $this->roomsRepository->getEmptyRoomsForPartner($partnerId, $propertyId);

            $occupancyRate = $totalRooms > 0 ? round((($totalRooms - $vacantRooms) / $totalRooms) * 100, 1) : 0;

            $currentMonthStart = now()->startOfMonth()->format('Y-m-d');
            $currentMonthEnd = now()->endOfMonth()->format('Y-m-d');

            $revenueData = $this->bookingRepository->getRevenueByMonthForPartner(
                $partnerId,
                $currentMonthStart,
                $currentMonthEnd,
                $propertyId,
            );

            $estimatedRevenue = $revenueData->sum('revenue');

            $countWithScope = function (array $filters) use ($partnerId, $propertyFilter): int {
                return (int) $this->bookingRepository->countBookingsForPartner(
                    $partnerId,
                    array_merge($filters, $propertyFilter),
                );
            };

            return [
                "success" => true,
                "data" => [
                    "totalProperties" => (int) $totalProperties,
                    "totalRooms" => (int) $totalRooms,
                    "vacantRooms" => (int) $vacantRooms,
                    "occupancyRate" => (float) $occupancyRate,
                    "estimatedRevenue" => (float) $estimatedRevenue,
                    "pendingBookingsCount" => $countWithScope(['status' => 0]),
                    "confirmedBookingsCount" => $countWithScope(['status' => 1]),
                    "cancelledBookingsCount" => $countWithScope(['status' => 2]),
                    "completedBookingsCount" => $countWithScope(['status' => 3]),
                    "pendingCancellationCount" => $countWithScope(['status' => 4]),
                    "todayCheckInCount" => $countWithScope([
                        'start_date' => now()->format('Y-m-d'),
                        'status' => 1,
                        'stay_status' => 'pending',
                    ]),
                    "todayCheckOutCount" => $countWithScope([
                        'end_date' => now()->format('Y-m-d'),
                        'status' => 1,
                        'stay_status' => 'checked_in',
                    ]),
                    "inStayCount" => $countWithScope([
                        'status' => 1,
                        'stay_status' => 'checked_in',
                    ]),
                    "totalBookingsCount" => $countWithScope([]),
                ],
                "message" => __("dashboard.messages.stats_fetched_successfully"),
            ];
        } catch (Exception $e) {
            Log::error("Partner get dashboard stats fail: " . $e->getMessage());
            return [
                "success" => false,
                "data" => null,
                "message" => __("dashboard.messages.stats_fetch_failed"),
            ];
        }
    }

    /**
     * Get dashboard operational stats for admin (system-wide).
     *
     * @return array{success: bool, data: array<string, int|float>|null, message: string}
     */
    public function getStatsForAdmin(): array
    {
        try {
            $data = Cache::remember('admin_dashboard_stats', 60, function () {
                $totalRooms = (int) $this->roomsRepository->countRecord();
                $vacantRooms = (int) $this->roomsRepository->getEmptyRooms();
                $occupancyRate = $totalRooms > 0
                    ? round((($totalRooms - $vacantRooms) / $totalRooms) * 100, 1)
                    : 0.0;

                $today = now()->format('Y-m-d');

                $summary = DB::table('bookings')
                    ->selectRaw('COUNT(*) as totalBookingsCount')
                    ->selectRaw('SUM(CASE WHEN status = 0 THEN 1 ELSE 0 END) as pendingBookingsCount')
                    ->selectRaw('SUM(CASE WHEN status = 4 THEN 1 ELSE 0 END) as pendingCancellationCount')
                    ->selectRaw("SUM(CASE WHEN DATE(start_date) = ? AND status = 1 AND stay_status = 'pending' THEN 1 ELSE 0 END) as todayCheckInCount", [$today])
                    ->selectRaw("SUM(CASE WHEN DATE(end_date) = ? AND status = 1 AND stay_status = 'checked_in' THEN 1 ELSE 0 END) as todayCheckOutCount", [$today])
                    ->selectRaw("SUM(CASE WHEN status = 1 AND stay_status = 'checked_in' THEN 1 ELSE 0 END) as inStayCount")
                    ->first();

                return [
                    'totalRooms' => $totalRooms,
                    'vacantRooms' => $vacantRooms,
                    'occupancyRate' => (float) $occupancyRate,
                    'pendingBookingsCount' => (int) ($summary->pendingBookingsCount ?? 0),
                    'pendingCancellationCount' => (int) ($summary->pendingCancellationCount ?? 0),
                    'todayCheckInCount' => (int) ($summary->todayCheckInCount ?? 0),
                    'todayCheckOutCount' => (int) ($summary->todayCheckOutCount ?? 0),
                    'inStayCount' => (int) ($summary->inStayCount ?? 0),
                    'totalBookingsCount' => (int) ($summary->totalBookingsCount ?? 0),
                ];
            });

            return [
                'success' => true,
                'data' => $data,
                'message' => __('dashboard.messages.stats_fetched_successfully'),
            ];
        } catch (Exception $e) {
            Log::error('Admin get dashboard stats fail: ' . $e->getMessage());

            return [
                'success' => false,
                'data' => null,
                'message' => __('dashboard.messages.stats_fetch_failed'),
            ];
        }
    }

    /**
     * Get pending bookings for partner
     *
     * @param int $partnerId
     * @return array
     */
    public function getPendingBookingsForPartner(int $partnerId, int $limit = 10, ?int $propertyId = null): array
    {
        try {
            $bookings = $this->bookingRepository
                ->getPendingBookingsForPartner($partnerId, $limit, $propertyId)
                ->map(function (object $booking): object {
                    $booking->has_conflict = $this->conflictChecker->hasConflict(
                        (int) $booking->room_id,
                        (string) $booking->start_date,
                        (string) $booking->end_date,
                        (int) $booking->id,
                    );

                    return $booking;
                });

            return [
                "success" => true,
                "data" => $bookings->values(),
                "message" => __("dashboard.messages.pending_bookings_fetched"),
            ];
        } catch (Exception $e) {
            Log::error("Partner get pending bookings fail: " . $e->getMessage());
            return [
                "success" => false,
                "data" => null,
                "message" => __("dashboard.messages.pending_bookings_fetch_failed"),
            ];
        }
    }

    /**
     * Get urgent maintenances for partner
     *
     * @param int $partnerId
     * @return array
     */
    public function getUrgentMaintenancesForPartner(int $partnerId): array
    {
        try {
            $maintenances = $this->roomMaintenanceRepository->getUrgentMaintenancesForPartner($partnerId);

            return [
                "success" => true,
                "data" => $maintenances,
                "message" => __("dashboard.messages.urgent_maintenances_fetched"),
            ];
        } catch (Exception $e) {
            Log::error("Partner get urgent maintenances fail: " . $e->getMessage());
            return [
                "success" => false,
                "data" => null,
                "message" => __("dashboard.messages.urgent_maintenances_fetch_failed"),
            ];
        }
    }

    /**
     * Get revenue analytics for partner
     *
     * @param int $partnerId
     * @return array
     */
    public function getRevenueAnalyticsForPartner(int $partnerId): array
    {
        try {
            $startDate = now()->subMonths(5)->startOfMonth()->format('Y-m-d');
            $endDate = now()->endOfMonth()->format('Y-m-d');

            $revenueByMonth = $this->bookingRepository->getRevenueByMonthForPartner(
                $partnerId,
                $startDate,
                $endDate
            );

            $analytics = $revenueByMonth->map(function ($item) {
                $revenue = (float) $item['revenue'];
                $commission = $revenue * 0.05; // 5% commission as per example
                return [
                    "month" => $item['month'],
                    "revenue" => $revenue,
                    "commission" => (float) $commission,
                    "netIncome" => $revenue - $commission,
                ];
            });

            return [
                "success" => true,
                "data" => $analytics,
                "message" => __("dashboard.messages.revenue_analytics_fetched"),
            ];
        } catch (Exception $e) {
            Log::error("Partner get revenue analytics fail: " . $e->getMessage());
            return [
                "success" => false,
                "data" => null,
                "message" => __("dashboard.messages.revenue_analytics_fetch_failed"),
            ];
        }
    }
}
