<?php

declare(strict_types=1);

namespace App\Repositories\BookingRepository;

use App\Repositories\RepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Interface BookingRepositoryInterface
 *
 * @package App\Repositories\BookingRepository
 */
interface BookingRepositoryInterface extends RepositoryInterface
{
    /**
     * Get all bookings or search by criteria with pagination
     *
     * @param \Illuminate\Http\Request|mixed $request
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getAllOrSearchBookings($request): LengthAwarePaginator;

    /**
     * Check if there is a conflicting booking for the same room and time period
     *
     * @param int $roomId
     * @param string $startDate
     * @param string|null $endDate
     * @return bool
     */
    public function checkRoomConflict(int $roomId, string $startDate, ?string $endDate = null): bool;

    /**
     * Check room conflict excluding a specific booking id
     *
     * @param int $bookingId
     * @param int $roomId
     * @param string $startDate
     * @param string|null $endDate
     * @return bool
     */
    public function checkRoomConflictExcludeId(
        int $bookingId,
        int $roomId,
        string $startDate,
        ?string $endDate = null
    ): bool;

    /**
     * Get bookings per month grouped by month
     *
     * @param string $startDate
     * @param string $endDate
     * @return Collection
     */
    public function getBookingsPerMonth(string $startDate, string $endDate): Collection;

    /**
     * Check if the user is authorized for the booking
     *
     * @param \Illuminate\Http\Request|mixed $request
     * @return bool
     */
    public function checkUser($request): bool;

    /**
     * Check if the price exists for the specified room
     *
     * @param int $room_id
     * @param int $price_id
     * @return bool
     */
    public function checkPriceExistsForRoom(int $room_id, int $price_id): bool;

    /**
     * Get bookings grouped by building
     *
     * @return Collection
     */
    public function getBookingsByBuilding(): Collection;

    /**
     * Get revenue by month
     *
     * @param string $startDate
     * @param string $endDate
     * @return Collection
     */
    public function getRevenueByMonth(string $startDate, string $endDate): Collection;

    /**
     * Get total number of stays by a user
     *
     * @param int $userId
     * @return int
     */
    public function countStaysByUserId(int $userId): int;

    /**
     * Get total spending by a user (completed bookings)
     *
     * @param int $userId
     * @return float
     */
    public function getTotalSpendingByUserId(int $userId): float;

    /**
     * Get active or upcoming booking for a user
     *
     * @param int $userId
     * @return \App\Models\Booking|null
     */
    public function getActiveBookingByUserId(int $userId);

    /**
     * Get recent completed booking history for a user
     *
     * @param int $userId
     * @param int $limit
     * @return Collection
     */
    public function getRecentHistoryByUserId(int $userId, int $limit = 2): Collection;

    /**
     * Get full booking history for a user
     *
     * @param int $userId
     * @return Collection
     */
    public function getBookingHistoryByUserId(int $userId): Collection;

    // =========================================================================
    // PARTNER METHODS
    // =========================================================================

    /**
     * Get bookings for a specific partner with pagination
     *
     * @param int $partnerId
     * @param \Illuminate\Http\Request|mixed $request
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getBookingsForPartner(int $partnerId, $request): LengthAwarePaginator;

    /**
     * Get bookings per month for a specific partner
     *
     * @param int $partnerId
     * @param string $startDate
     * @param string $endDate
     * @return Collection
     */
    public function getBookingsPerMonthForPartner(int $partnerId, string $startDate, string $endDate): Collection;

    /**
     * Get bookings grouped by building for a specific partner
     *
     * @param int $partnerId
     * @return Collection
     */
    public function getBookingsByBuildingForPartner(int $partnerId): Collection;

    /**
     * Get revenue by month for a specific partner
     *
     * @param int $partnerId
     * @param string $startDate
     * @param string $endDate
     * @return Collection
     */
    public function getRevenueByMonthForPartner(int $partnerId, string $startDate, string $endDate): Collection;

    /**
     * Get pending bookings for a specific partner
     *
     * @param int $partnerId
     * @param int $limit
     * @return Collection
     */
    public function getPendingBookingsForPartner(int $partnerId, int $limit = 5): Collection;
}
