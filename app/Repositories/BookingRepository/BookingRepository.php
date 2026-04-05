<?php

declare(strict_types=1);

namespace App\Repositories\BookingRepository;

use App\Enums\BookingStatus;
use App\Models\Booking;
use App\Repositories\BaseRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Class BookingRepository
 *
 * @package App\Repositories\BookingRepository
 */
final class BookingRepository extends BaseRepository implements BookingRepositoryInterface
{
    /**
     * Get the model class name
     *
     * @return string
     */
    public function getModel(): string
    {
        return Booking::class;
    }
    /**
     * Get all bookings or search by criteria with pagination
     *
     * @param \Illuminate\Http\Request|mixed $request
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getAllOrSearchBookings($request): LengthAwarePaginator
    {
        $query = $this->model->select(
            'bookings.id',
            'users.name as user_name',
            'rooms.room_number as room_name',
            'buildings.name as building_name',
            'bookings.start_date',
            'bookings.end_date',
            'room_prices.price',
            'bookings.status as booking_status',
            'partner.name as partner_name',
            'bookings.created_at',
        );

        $query->join('rooms', 'bookings.room_id', '=', 'rooms.id')
            ->join('room_prices', 'bookings.price_id', '=', 'room_prices.id')
            ->join('buildings', 'rooms.building_id', '=', 'buildings.id')
            ->join('users', 'bookings.user_id', '=', 'users.id')
            ->leftJoin('users as partner', 'buildings.user_id', '=', 'partner.id');

        if (Auth::check() && Auth::user()->role === 'partner') {
            // Filter by partner_id
            $query->where('buildings.user_id', Auth::id());
        }

        if ($request->filled('room_id')) {
            $query->where('bookings.room_id', $request->room_id);
        }

        if ($request->filled('user_id')) {
            $query->where('bookings.user_id', $request->user_id);
        }

        if ($request->filled('building_id')) {
            $query->where('rooms.building_id', $request->building_id);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('bookings.start_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('bookings.end_date', '<=', $request->end_date);
        }

        if ($request->filled('status')) {
            $query->where('bookings.status', $request->status);
        }

        $sortField = $request->input('sort_field');
        $sortDirection = $request->input('sort_direction');

        if (
            $sortField
            && in_array($sortField, [
            'id',
            'room',
            'user',
            'start_date',
            'end_date',
            'price',
            'status',
            'assignee',
            'created_at'
            ])
            && in_array($sortDirection, ['asc', 'desc'])
        ) {
            if ($sortField === 'room') {
                $query->orderBy('rooms.room_number', $sortDirection);
            } elseif ($sortField === 'user') {
                $query->orderBy('users.name', $sortDirection);
            } elseif ($sortField === 'price') {
                $query->orderBy('room_prices.price', $sortDirection);
            } elseif ($sortField === 'assignee') {
                $query->orderBy('partner.name', $sortDirection);
            } else {
                $query->orderBy('bookings.' . $sortField, $sortDirection);
            }
        } else {
            // Default sorting
            $query->orderBy('bookings.id', 'desc');
        }

        $page    = $request->input('page', config('const.DEFAULT_PAGE'));
        $perPage = $request->input('per_page', config('const.DEFAULT_PER_PAGE'));

        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Check if there is a conflicting booking for the same room and time period
     *
     * @param int $roomId
     * @param string $startDate
     * @param string|null $endDate
     * @return bool
     */
    public function checkRoomConflict(int $roomId, string $startDate, ?string $endDate = null): bool
    {
        return $this->model->where('room_id', $roomId)
            ->where(function ($query) use ($startDate, $endDate): void {
                $query->where('start_date', '<=', $endDate ?? $startDate)
                    ->where('end_date', '>=', $startDate);
            })
            ->exists();
    }

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
    ): bool {
        return $this->model->where('id', '!=', $bookingId)
            ->where('room_id', $roomId)
            ->whereNotIn('status', ['cancelled', 'completed'])
            ->where(function ($query) use ($startDate, $endDate): void {
                $query->where('start_date', '<', $endDate ?? $startDate)
                    ->where('end_date', '>', $startDate);
            })
            ->exists();
    }

    /**
     * Get bookings per month grouped by month
     *
     * @param string $startDate
     * @param string $endDate
     * @return \Illuminate\Support\Collection
     */
    public function getBookingsPerMonth(string $startDate, string $endDate): Collection
    {
        $query = $this->model->where('status', '!=', BookingStatus::CANCELLED->value);

        if ($startDate && $endDate) {
            $query->whereBetween('start_date', [$startDate, $endDate]);
        } elseif ($startDate) {
            $query->whereDate('start_date', '>=', $startDate);
        } elseif ($endDate) {
            $query->whereDate('start_date', '<=', $endDate);
        }

        return $query
            ->select(
                DB::raw('DATE_FORMAT(start_date, "%Y-%m") as month'),
                DB::raw('COUNT(*) as total')
            )
            ->groupBy(DB::raw('DATE_FORMAT(start_date, "%Y-%m")'))
            ->orderBy('month')
            ->get()
            ->map(fn($b) => [
                'month' => $b->month,
                'total' => (int) $b->total,
            ]);
    }

    /**
     * Check if the user is authorized for the booking operation
     * Supports both create (with room_id) and update/view (with booking id)
     *
     * @param \Illuminate\Http\Request|mixed $request
     * @return bool
     */
    public function checkUser($request): bool
    {
        $bookingId = $request->id ?? $request->route('id') ?? null;
        $roomId    = $request->input('room_id') ?? null;
        $role      = Auth::user()->role ?? null;

        // Admin has full privileges
        if ($role === 'admin') {
            return true;
        }

        // Partner only allowed to manage bookings for their buildings
        if ($role === 'partner') {
            // For creating new booking (check by room_id)
            if ($roomId && ! $bookingId) {
                return $this->model
                    ->join('rooms', 'bookings.room_id', '=', 'rooms.id')
                    ->join('buildings', 'rooms.building_id', '=', 'buildings.id')
                    ->where('rooms.id', $roomId)
                    ->where('buildings.user_id', Auth::user()->id)
                    ->exists();
            }

            // For updating booking operations (check by booking_id)
            if ($bookingId) {
                return $this->model
                    ->join('rooms', 'bookings.room_id', '=', 'rooms.id')
                    ->join('buildings', 'rooms.building_id', '=', 'buildings.id')
                    ->where('bookings.id', $bookingId)
                    ->where('buildings.user_id', Auth::user()->id)
                    ->exists();
            }

            return false;
        }

        // User role
        // For creating new booking, users can create bookings for themselves
        if (! $bookingId && $roomId) {
            return true;
        }

        // For existing bookings, users can only manage their own bookings
        if ($bookingId) {
            return $this->model->where('id', $bookingId)
                ->where('user_id', Auth::user()->id)
                ->exists();
        }

        return false;
    }

    /**
     * Check if the price exists for the specified room
     *
     * @param int $room_id
     * @param int $price_id
     * @return bool
     */
    public function checkPriceExistsForRoom(int $room_id, int $price_id): bool
    {
        return $this->model
            ->from('room_prices')
            ->where('room_id', $room_id)
            ->where('id', $price_id)
            ->exists();
    }

    /**
     * Get bookings grouped by building
     *
     * @return Collection
     */
    public function getBookingsByBuilding(): Collection
    {
        return $this->model
            ->join('rooms', 'bookings.room_id', '=', 'rooms.id')
            ->join('buildings', 'rooms.building_id', '=', 'buildings.id')
            ->select(
                'buildings.id as building_id',
                'buildings.name as building_name',
                DB::raw('COUNT(*) as total')
            )
            ->where('bookings.status', '!=', BookingStatus::CANCELLED->value)
            ->groupBy('buildings.id', 'buildings.name')
            ->get()
            ->map(fn($b) => [
                'building_id'   => $b->building_id,
                'building_name' => $b->building_name,
                'total'         => (int) $b->total,
            ]);
    }

    /**
     * Get revenue by month
     *
     * @param string $startDate
     * @param string $endDate
     * @return Collection
     */
    public function getRevenueByMonth(string $startDate, string $endDate): Collection
    {
        $query = $this->model
            ->join('room_prices', 'bookings.price_id', '=', 'room_prices.id')
            ->whereIn('bookings.status', [BookingStatus::CONFIRMED->value, BookingStatus::COMPLETED->value]);

        if ($startDate && $endDate) {
            $query->whereBetween('bookings.start_date', [$startDate, $endDate]);
        } elseif ($startDate) {
            $query->whereDate('bookings.start_date', '>=', $startDate);
        } elseif ($endDate) {
            $query->whereDate('bookings.start_date', '<=', $endDate);
        }

        return $query
            ->select(
                DB::raw('DATE_FORMAT(bookings.start_date, "%Y-%m") as month'),
                DB::raw("
                   SUM(
                    CASE
                        WHEN room_prices.unit = 'day' THEN
                            room_prices.price * (DATEDIFF(COALESCE(bookings.end_date, bookings.start_date),
                            bookings.start_date) + 1)
                        when room_prices.unit = 'month' THEN
                            room_prices.price * (DATEDIFF(COALESCE(bookings.end_date, bookings.start_date),
                            bookings.start_date) + 1)/30
                        ELSE 0
                    END
                   ) as revenue
                ")
            )
            ->groupBy(DB::raw('DATE_FORMAT(bookings.start_date, "%Y-%m")'))
            ->orderBy('month')
            ->get()
            ->map(fn($b) => [
                'month'   => $b->month,
                'revenue' => (float) ($b->revenue ?? 0),
            ]);
    }

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
    public function getBookingsForPartner(int $partnerId, $request): LengthAwarePaginator
    {
        $query = $this->model->select(
            'bookings.id',
            'users.name as user_name',
            'rooms.room_number as room_name',
            'buildings.name as building_name',
            'bookings.start_date',
            'bookings.end_date',
            'room_prices.price',
            'bookings.status as booking_status',
            'partner.name as partner_name',
            'bookings.created_at',
        );

        $query->join('rooms', 'bookings.room_id', '=', 'rooms.id')
            ->join('room_prices', 'bookings.price_id', '=', 'room_prices.id')
            ->join('buildings', 'rooms.building_id', '=', 'buildings.id')
            ->join('users', 'bookings.user_id', '=', 'users.id')
            ->leftJoin('users as partner', 'buildings.user_id', '=', 'partner.id')
            ->where('buildings.user_id', $partnerId);

        if ($request->filled('status')) {
            $query->where('bookings.status', $request->status);
        }

        $query->orderBy('bookings.id', 'desc');

        $page    = $request->input('page', config('const.DEFAULT_PAGE'));
        $perPage = $request->input('per_page', config('const.DEFAULT_PER_PAGE'));

        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Get bookings per month for a specific partner
     *
     * @param int $partnerId
     * @param string $startDate
     * @param string $endDate
     * @return Collection
     */
    public function getBookingsPerMonthForPartner(int $partnerId, string $startDate, string $endDate): Collection
    {
        $query = $this->model
            ->join('rooms', 'bookings.room_id', '=', 'rooms.id')
            ->join('buildings', 'rooms.building_id', '=', 'buildings.id')
            ->where('buildings.user_id', $partnerId)
            ->where('bookings.status', '!=', BookingStatus::CANCELLED->value);

        if ($startDate && $endDate) {
            $query->whereBetween('bookings.start_date', [$startDate, $endDate]);
        }

        return $query
            ->select(
                DB::raw('DATE_FORMAT(bookings.start_date, "%Y-%m") as month'),
                DB::raw('COUNT(*) as total')
            )
            ->groupBy(DB::raw('DATE_FORMAT(bookings.start_date, "%Y-%m")'))
            ->orderBy('month')
            ->get()
            ->map(fn($b) => [
                'month' => $b->month,
                'total' => (int) $b->total,
            ]);
    }

    /**
     * Get bookings grouped by building for a specific partner
     *
     * @param int $partnerId
     * @return Collection
     */
    public function getBookingsByBuildingForPartner(int $partnerId): Collection
    {
        return $this->model
            ->join('rooms', 'bookings.room_id', '=', 'rooms.id')
            ->join('buildings', 'rooms.building_id', '=', 'buildings.id')
            ->select(
                'buildings.id as building_id',
                'buildings.name as building_name',
                DB::raw('COUNT(*) as total')
            )
            ->where('buildings.user_id', $partnerId)
            ->where('bookings.status', '!=', BookingStatus::CANCELLED->value)
            ->groupBy('buildings.id', 'buildings.name')
            ->get()
            ->map(fn($b) => [
                'building_id'   => $b->building_id,
                'building_name' => $b->building_name,
                'total'         => (int) $b->total,
            ]);
    }

    /**
     * Get revenue by month for a specific partner
     *
     * @param int $partnerId
     * @param string $startDate
     * @param string $endDate
     * @return Collection
     */
    public function getRevenueByMonthForPartner(int $partnerId, string $startDate, string $endDate): Collection
    {
        $query = $this->model
            ->join('rooms', 'bookings.room_id', '=', 'rooms.id')
            ->join('buildings', 'rooms.building_id', '=', 'buildings.id')
            ->join('room_prices', 'bookings.price_id', '=', 'room_prices.id')
            ->where('buildings.user_id', $partnerId)
            ->whereIn('bookings.status', [BookingStatus::CONFIRMED->value, BookingStatus::COMPLETED->value]);

        if ($startDate && $endDate) {
            $query->whereBetween('bookings.start_date', [$startDate, $endDate]);
        }

        return $query
            ->select(
                DB::raw('DATE_FORMAT(bookings.start_date, "%Y-%m") as month'),
                DB::raw("
                   SUM(
                    CASE
                        WHEN room_prices.unit = 'day' THEN
                            room_prices.price * (DATEDIFF(COALESCE(bookings.end_date, bookings.start_date),
                            bookings.start_date) + 1)
                        when room_prices.unit = 'month' THEN
                            room_prices.price * (DATEDIFF(COALESCE(bookings.end_date, bookings.start_date),
                            bookings.start_date) + 1)/30
                        ELSE 0
                    END
                   ) as revenue
                ")
            )
            ->groupBy(DB::raw('DATE_FORMAT(bookings.start_date, "%Y-%m")'))
            ->orderBy('month')
            ->get()
            ->map(fn($b) => [
                'month'   => $b->month,
                'revenue' => (float) ($b->revenue ?? 0),
            ]);
    }
    /**
     * Get pending bookings for a specific partner
     *
     * @param int $partnerId
     * @param int $limit
     * @return Collection
     */
    public function getPendingBookingsForPartner(int $partnerId, int $limit = 5): Collection
    {
        return $this->model->select(
            'bookings.id',
            'users.name as customerName',
            'rooms.room_number as roomName',
            'bookings.start_date as checkInDate',
            'bookings.status'
        )
            ->join('rooms', 'bookings.room_id', '=', 'rooms.id')
            ->join('buildings', 'rooms.building_id', '=', 'buildings.id')
            ->join('users', 'bookings.user_id', '=', 'users.id')
            ->where('buildings.user_id', $partnerId)
            ->where('bookings.status', BookingStatus::PENDING->value)
            ->orderBy('bookings.created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}
