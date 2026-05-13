<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\RoomStatus;
use App\Enums\Status;
use App\Repositories\BookingRepository\BookingRepositoryInterface;
use App\Repositories\BuildingRepository\BuildingsRepositoryInterface;
use App\Repositories\RoomsRepository\RoomsRepositoryInterface;
use App\Repositories\RoomMaintenanceRepository\RoomMaintenanceRepositoryInterface;
use App\Repositories\ServiceRepository\ServiceRepositoryInterface;
use App\Repositories\UsersRepository\UsersRepositoryInterface;
use Exception;
use Illuminate\Support\Facades\Log;

final class DashboardService
{
    protected RoomsRepositoryInterface $roomsRepository;
    protected BookingRepositoryInterface $bookingRepository;
    protected BuildingsRepositoryInterface $buildingsRepository;
    protected ServiceRepositoryInterface $serviceRepository;
    protected UsersRepositoryInterface $usersRepository;
    protected RoomMaintenanceRepositoryInterface $roomMaintenanceRepository;

    public function __construct(
        RoomsRepositoryInterface $roomsRepository,
        BookingRepositoryInterface $bookingRepository,
        BuildingsRepositoryInterface $buildingsRepository,
        ServiceRepositoryInterface $serviceRepository,
        UsersRepositoryInterface $usersRepository,
        RoomMaintenanceRepositoryInterface $roomMaintenanceRepository
    ) {
        $this->roomsRepository = $roomsRepository;
        $this->bookingRepository = $bookingRepository;
        $this->buildingsRepository = $buildingsRepository;
        $this->serviceRepository = $serviceRepository;
        $this->usersRepository = $usersRepository;
        $this->roomMaintenanceRepository = $roomMaintenanceRepository;
    }

    /**
     * Get information about the number of users
     * @param $request
     * @return array
     */
    public function getTotalUsers($request): array
    {
        try {
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
                "success" => true,
                "data" => [
                    "totalUsers" => $totalUsers,
                    "newUserThisMonth" => $newUserThisMonth,
                    "userPending" => $userPending,
                    "userBlock" => $userBlock,
                ],
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
            $totalPartners = (int) $this->usersRepository->countRecord([
                "role" => "partner",
            ]);
            $newUPartnerThisMonth = (int) $this->usersRepository->countNewUserInCurrentMonth(
                $request,
                "partner"
            );
            $partnerPending = (int) $this->usersRepository->countRecord([
                "status" => Status::PENDING->value,
                "role" => "partner",
            ]);
            $partnerBlock = (int) $this->usersRepository->countRecord([
                "status" => Status::BLOCKED->value,
                "role" => "partner",
            ]);

            return [
                "success" => true,
                "data" => [
                    "totalPartners" => $totalPartners,
                    "newUPartnerThisMonth" => $newUPartnerThisMonth,
                    "partnerPending" => $partnerPending,
                    "partnerBlock" => $partnerBlock,
                ],
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
     * Total number of buildings in the system
     * @return array
     */
    public function getSystemBuilding(): array
    {
        try {
            $totalBuildings = $this->buildingsRepository->countRecord();
            return [
                "success" => true,
                "data" => [
                    "totalBuildings" => $totalBuildings,
                ],
                "message" => __(
                    "dashboard.messages.stats_fetched_successfully"
                ),
            ];
        } catch (Exception $e) {
            Log::error("Get system building fail" . $e->getMessage());
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
            $totalRooms = $this->roomsRepository->countRecord();
            $totalPrivateRooms = $this->roomsRepository->countRecord([
                "status" => RoomStatus::PRIVATE,
            ]);
            $totalPublicRooms = $this->roomsRepository->countRecord([
                "status" => RoomStatus::PUBLIC,
            ]);
            $totalAvailableRooms = $this->roomsRepository->getEmptyRooms();
            return [
                "success" => true,
                "data" => [
                    "totalRooms" => $totalRooms,
                    "totalPrivateRooms" => $totalPrivateRooms,
                    "totalPublicRooms" => $totalPublicRooms,
                    "totalAvailableRooms" => $totalAvailableRooms,
                ],
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

            $bookingsPerMonth = $this->bookingRepository->getBookingsPerMonth(
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

            $revenueByMonth = $this->bookingRepository->getRevenueByMonth(
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
     * Get bookings count for all buildings
     *
     * @return array
     */
    public function getAllBuildingsBookingsCount(): array
    {
        try {
            $bookingsByBuilding = $this->bookingRepository->getBookingsByBuilding();

            return [
                "success" => true,
                "data" => $bookingsByBuilding,
                "message" => __(
                    "dashboard.messages.all_buildings_bookings_count_fetched"
                ),
            ];
        } catch (\Exception $e) {
            Log::error(
                "Error fetching all buildings bookings count: " .
                    $e->getMessage()
            );

            return [
                "success" => false,
                "data" => null,
                "message" => __(
                    "dashboard.messages.all_buildings_bookings_count_fetch_failed"
                ),
            ];
        }
    }

    // =========================================================================
    // PARTNER METHODS
    // =========================================================================

    /**
     * Get buildings count for partner
     *
     * @param int $partnerId
     * @return array
     */
    public function getSystemBuildingForPartner(int $partnerId): array
    {
        try {
            $totalBuildings = $this->buildingsRepository->countRecord([
                'user_id' => $partnerId
            ]);
            return [
                "success" => true,
                "data" => [
                    "totalBuildings" => $totalBuildings,
                ],
                "message" => __("dashboard.messages.stats_fetched_successfully"),
            ];
        } catch (Exception $e) {
            Log::error("Partner get system building fail: " . $e->getMessage());
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
     * Get bookings count by building for partner
     *
     * @param int $partnerId
     * @return array
     */
    public function getAllBuildingsBookingsCountForPartner(int $partnerId): array
    {
        try {
            $bookingsByBuilding = $this->bookingRepository->getBookingsByBuildingForPartner($partnerId);

            return [
                "success" => true,
                "data" => $bookingsByBuilding,
                "message" => __("dashboard.messages.all_buildings_bookings_count_fetched"),
            ];
        } catch (\Exception $e) {
            Log::error("Partner get buildings bookings count fail: " . $e->getMessage());
            return [
                "success" => false,
                "data" => null,
                "message" => __("dashboard.messages.all_buildings_bookings_count_fetch_failed"),
            ];
        }
    }

    /**
     * Get dashboard stats for partner
     *
     * @param int $partnerId
     * @return array
     */
    public function getStatsForPartner(int $partnerId): array
    {
        try {
            $totalBuildings = $this->buildingsRepository->countRecord(['user_id' => $partnerId]);
            $totalRooms = $this->roomsRepository->countRoomsForPartner($partnerId);
            $vacantRooms = $this->roomsRepository->getEmptyRoomsForPartner($partnerId);

            $occupancyRate = $totalRooms > 0 ? round((($totalRooms - $vacantRooms) / $totalRooms) * 100, 1) : 0;

            $currentMonthStart = now()->startOfMonth()->format('Y-m-d');
            $currentMonthEnd = now()->endOfMonth()->format('Y-m-d');

            $revenueData = $this->bookingRepository->getRevenueByMonthForPartner(
                $partnerId,
                $currentMonthStart,
                $currentMonthEnd
            );

            $estimatedRevenue = $revenueData->sum('revenue');

            $pendingBookingsCount = $this->bookingRepository->countBookingsForPartner($partnerId, [
                'status' => 0 // Pending
            ]);
            $confirmedBookingsCount = $this->bookingRepository->countBookingsForPartner($partnerId, [
                'status' => 1 // Confirmed
            ]);
            $cancelledBookingsCount = $this->bookingRepository->countBookingsForPartner($partnerId, [
                'status' => 2 // Cancelled
            ]);

            return [
                "success" => true,
                "data" => [
                    "totalBuildings" => (int) $totalBuildings,
                    "totalRooms" => (int) $totalRooms,
                    "vacantRooms" => (int) $vacantRooms,
                    "occupancyRate" => (float) $occupancyRate,
                    "estimatedRevenue" => (float) $estimatedRevenue,
                    "pendingBookingsCount" => (int) $pendingBookingsCount,
                    "confirmedBookingsCount" => (int) $confirmedBookingsCount,
                    "cancelledBookingsCount" => (int) $cancelledBookingsCount,
                    "todayCheckInCount" => (int) $this->bookingRepository->countBookingsForPartner($partnerId, [
                        'start_date' => now()->format('Y-m-d'),
                        'status' => 1 // Confirmed
                    ]),
                    "todayCheckOutCount" => (int) $this->bookingRepository->countBookingsForPartner($partnerId, [
                        'end_date' => now()->format('Y-m-d'),
                        'status' => 1 // Confirmed
                    ]),
                    "inStayCount" => (int) $this->bookingRepository->countBookingsForPartner($partnerId, [
                        'status' => 1, // Confirmed
                        'stay_status' => 'checked_in'
                    ]),
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
     * Get pending bookings for partner
     *
     * @param int $partnerId
     * @return array
     */
    public function getPendingBookingsForPartner(int $partnerId): array
    {
        try {
            $bookings = $this->bookingRepository->getPendingBookingsForPartner($partnerId);

            return [
                "success" => true,
                "data" => $bookings,
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
