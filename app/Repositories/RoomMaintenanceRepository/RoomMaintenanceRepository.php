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
 */
final class RoomMaintenanceRepository extends BaseRepository implements RoomMaintenanceRepositoryInterface
{
    public function getModel(): string
    {
        return RoomMaintenance::class;
    }

    /**
     * @param array<string, mixed> $filters
     */
    public function getList(array $filters): LengthAwarePaginator
    {
        /** @var Builder $query */
        $query = $this->model->newQuery()
            ->with([
                'room:id,title,room_number,property_id',
                'property:id,name,user_id',
            ]);

        if (! empty($filters['partner_id'])) {
            $query->whereHas('property', static function ($propertyQuery) use ($filters): void {
                $propertyQuery->where('user_id', (int) $filters['partner_id']);
            });
        }

        if (! empty($filters['room_id'])) {
            $query->where('room_id', (int) $filters['room_id']);
        }

        if (! empty($filters['property_id'])) {
            $query->where('property_id', (int) $filters['property_id']);
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

        $perPage = (int) ($filters['per_page'] ?? $filters['pagination'] ?? config('app.pagination_limit', 15));
        $perPage = max(1, min(100, $perPage));
        $page    = max(1, (int) ($filters['page'] ?? 1));

        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    public function findByIdForScope(int $id, ?int $partnerId): ?RoomMaintenance
    {
        /** @var Builder $query */
        $query = $this->model->newQuery()
            ->with([
                'room:id,title,room_number,property_id',
                'property:id,name,user_id',
                'roomBlock:id,room_id,start_date,end_date,block_type,reason',
                'creator:id,name',
            ]);

        if ($partnerId !== null) {
            $query->whereHas('property', static function ($propertyQuery) use ($partnerId): void {
                $propertyQuery->where('user_id', $partnerId);
            });
        }

        /** @var RoomMaintenance|null $record */
        $record = $query->find($id);

        return $record;
    }

    public function getUrgentMaintenancesForPartner(int $partnerId, int $limit = 5)
    {
        $today = Carbon::now()->toDateString();
        $currentBookingSubquery = DB::table('bookings')
            ->select('bookings.room_id', DB::raw('MAX(bookings.id) as booking_id'))
            ->whereIn('bookings.status', [BookingStatus::CONFIRMED->value, BookingStatus::COMPLETED->value])
            ->where('bookings.start_date', '<=', $today)
            ->where('bookings.end_date', '>=', $today)
            ->groupBy('bookings.room_id');

        return $this->model->select(
            'room_maintenances.id',
            'room_maintenances.room_id as roomId',
            'users.name as customerName',
            'rooms.room_number as roomName',
            'room_maintenances.title as issueDescription',
            'room_maintenances.status',
            'room_maintenances.maintenance_type as maintenanceType',
            'room_maintenances.created_at as createdAt'
        )
            ->join('rooms', 'room_maintenances.room_id', '=', 'rooms.id')
            ->join('properties', 'rooms.property_id', '=', 'properties.id')
            ->leftJoinSub($currentBookingSubquery, 'current_bookings', function ($join): void {
                $join->on('rooms.id', '=', 'current_bookings.room_id');
            })
            ->leftJoin('bookings', 'current_bookings.booking_id', '=', 'bookings.id')
            ->leftJoin('users', 'bookings.user_id', '=', 'users.id')
            ->where('properties.user_id', $partnerId)
            ->whereIn('room_maintenances.status', [
                RoomMaintenance::STATUS_PLANNED,
                RoomMaintenance::STATUS_IN_PROGRESS,
            ])
            ->orderByRaw("CASE WHEN room_maintenances.maintenance_type = 'emergency' THEN 0 ELSE 1 END")
            ->orderByDesc('room_maintenances.created_at')
            ->limit($limit)
            ->get();
    }
}
