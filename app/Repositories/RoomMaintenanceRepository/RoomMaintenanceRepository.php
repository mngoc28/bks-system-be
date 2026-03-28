<?php

namespace App\Repositories\RoomMaintenanceRepository;

use App\Models\RoomMaintenance;
use App\Repositories\BaseRepository;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class RoomMaintenanceRepository extends BaseRepository implements RoomMaintenanceRepositoryInterface
{
    /**
     * Get the model class name.
     *
     * @return string
     */
    public function getModel(): string
    {
        return RoomMaintenance::class;
    }

    /**
     * Retrieve room maintenance list with optional filters.
     *
     * @param array $filters
     * @return LengthAwarePaginator|\Illuminate\Support\Collection
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
}
