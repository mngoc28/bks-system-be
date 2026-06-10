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
     * Count non-cancelled bookings grouped by start_date (day).
     *
     * @param string $startDate
     * @param string $endDate
     * @return Collection<int, array{date: string, total: int}>
     */
    public function getBookingsPerDay(string $startDate, string $endDate): Collection;

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
     * Get bookings grouped by property
     *
     * @param string|null $startDate
     * @param string|null $endDate
     * @return Collection
     */
    public function getBookingsByProperty(?string $startDate = null, ?string $endDate = null): Collection;

    /**
     * Count bookings grouped by status within a start_date range.
     *
     * @param string $startDate
     * @param string $endDate
     * @return Collection<int, array{status: int, total: int}>
     */
    public function getBookingStatusBreakdown(string $startDate, string $endDate): Collection;

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
    public function getActiveBookingByUserId(int $userId): ?\App\Models\Booking;

    /**
     * Get recent completed booking history for a user
     *
     * @param int $userId
     * @param int $limit
     * @return Collection
     */
    public function getRecentHistoryByUserId(int $userId, int $limit = 2): Collection;

    /**
     * Get full booking history for a user with pagination
     *
     * @param int $userId
     * @param int $perPage
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getBookingHistoryByUserId(int $userId, int $perPage = 10): LengthAwarePaginator;

    /**
     * Get single booking detail for a user
     *
     * @param int $bookingId
     * @param int $userId
     * @return \App\Models\Booking|null
     */
    public function getBookingDetailByUserId(int $bookingId, int $userId): ?\App\Models\Booking;

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
     * Get bookings grouped by property for a specific partner
     *
     * @param int $partnerId
     * @return Collection
     */
    public function getBookingsByPropertyForPartner(int $partnerId): Collection;

    /**
     * Get revenue by month for a specific partner
     *
     * @param int $partnerId
     * @param string $startDate
     * @param string $endDate
     * @return Collection
     */
    public function getRevenueByMonthForPartner(
        int $partnerId,
        string $startDate,
        string $endDate,
        ?int $propertyId = null,
    ): Collection;

    /**
     * Get pending bookings for a specific partner
     *
     * @param int $partnerId
     * @param int $limit
     * @param int|null $propertyId
     * @return Collection
     */
    public function getPendingBookingsForPartner(int $partnerId, int $limit = 10, ?int $propertyId = null): Collection;

    /**
     * Count bookings matching criteria for a specific partner
     *
     * @param int $partnerId
     * @param array $filters
     * @return int
     */
    public function countBookingsForPartner(int $partnerId, array $filters = []): int;

    /**
     * Count bookings matching criteria across the whole system (admin scope).
     *
     * @param array<string, mixed> $filters
     */
    public function countBookingsForAdmin(array $filters = []): int;
}
