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
}
