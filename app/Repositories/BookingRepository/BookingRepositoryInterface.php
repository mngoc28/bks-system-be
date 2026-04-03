<?php

declare(strict_types=1);

namespace App\Repositories\BookingRepository;

use App\Repositories\RepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface BookingRepositoryInterface extends RepositoryInterface
{
    /**
     * Get all bookings or search by criteria
     *
     * @return LengthAwarePaginator
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
     * Check user for booking
     *
     * @param $request
     * @return bool
     */
    public function checkUser($request): bool;

    /**
     * Check if the price exists for the room
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

    // =========================================================================
    // PARTNER METHODS
    // =========================================================================

    /**
     * Get bookings for a specific partner
     *
     * @param int $partnerId
     * @param mixed $request
     * @return LengthAwarePaginator
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
