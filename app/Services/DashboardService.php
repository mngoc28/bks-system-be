<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\RoomStatus;
use App\Enums\Status;
use App\Repositories\BookingRepository\BookingRepositoryInterface;
use App\Repositories\BuildingRepository\BuildingsRepositoryInterface;
use App\Repositories\RoomsRepository\RoomsRepositoryInterface;
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

    public function __construct(
        RoomsRepositoryInterface $roomsRepository,
        BookingRepositoryInterface $bookingRepository,
        BuildingsRepositoryInterface $buildingsRepository,
        ServiceRepositoryInterface $serviceRepository,
        UsersRepositoryInterface $usersRepository
    ) {
        $this->roomsRepository = $roomsRepository;
        $this->bookingRepository = $bookingRepository;
        $this->buildingsRepository = $buildingsRepository;
        $this->serviceRepository = $serviceRepository;
        $this->usersRepository = $usersRepository;
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
}
