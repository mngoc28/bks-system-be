<?php

declare(strict_types=1);

namespace App\Repositories\RoomMaintenanceRepository;

use App\Models\RoomMaintenance;
use App\Repositories\BaseRepository;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use App\Enums\BookingStatus;
use Illuminate\Support\Facades\DB;

/**
 * Class RoomMaintenanceRepository
 *
 * @package App\Repositories\RoomMaintenanceRepository
 */
class RoomMaintenanceRepository extends BaseRepository implements RoomMaintenanceRepositoryInterface
{
    /**
     * Get the model class name
     *
     * @return string
     */
    public function getModel(): string
    {
        return RoomMaintenance::class;
    }

    /**
     * Get room maintenance list with optional filters
     *
     * @param array $filters
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator|\Illuminate\Support\Collection
     */
    public function getList(array $filters)
    {
        /** @var Builder $query */
        $query = $this->model->newQuery();

        if (! empty($filters['room_id'])) {
            $query->where('room_id', $filters['room_id']);
        }

        if (! empty($filters['property_id'])) {
            $query->where('property_id', $filters['property_id']);
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['maintenance_type'])) {
            $query->where('maintenance_type', $filters['maintenance_type']);
        }

        if (! empty($filters['from_date'])) {
            $query->whereDate('start_time', '>=', Carbon::parse($filters['from_date'])->startOfDay());
        }

        if (! empty($filters['to_date'])) {
            $query->whereDate('end_time', '<=', Carbon::parse($filters['to_date'])->endOfDay());
        }

        $query->orderByDesc('start_time');

        $perPage = $filters['pagination'] ?? config('app.pagination_limit', 15);

        if (! empty($filters['pagination'])) {
            return $query->paginate((int) $perPage);
        }

        return $query->get();
    }

    /**
     * Get urgent maintenance requests for a specific partner
     *
     * @param int $partnerId
     * @param int $limit
     * @return \Illuminate\Support\Collection
     */
    public function getUrgentMaintenancesForPartner(int $partnerId, int $limit = 5)
    {
        $today = Carbon::now()->toDateString();

        return $this->model->select(
            'room_maintenances.id',
            'users.name as customerName',
            'rooms.room_number as roomName',
            'room_maintenances.title as issueDescription',
            'room_maintenances.status',
            'room_maintenances.created_at as createdAt'
        )
            ->join('rooms', 'room_maintenances.room_id', '=', 'rooms.id')
            ->join('properties', 'rooms.property_id', '=', 'properties.id')
            // Join with current active booking to get customer name
            ->leftJoin('bookings', function ($join) use ($today) {
                $join->on('rooms.id', '=', 'bookings.room_id')
                    ->whereIn('bookings.status', [BookingStatus::CONFIRMED->value, BookingStatus::COMPLETED->value])
                    ->where('bookings.start_date', '<=', $today)
                    ->where('bookings.end_date', '>=', $today);
            })
            ->leftJoin('users', 'bookings.user_id', '=', 'users.id')
            ->where('properties.user_id', $partnerId)
            ->whereIn('room_maintenances.status', ['planned', 'in_progress'])
            ->orderBy('room_maintenances.created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}
