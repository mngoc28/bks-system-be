<?php

declare(strict_types=1);

namespace App\Repositories\PropertyRepository;

use App\Enums\BookingStatus;
use App\Enums\UserType;
use App\Models\Property;
use App\Models\Room;
use App\Repositories\BaseRepository;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

final class PropertyRepository extends BaseRepository implements PropertyRepositoryInterface
{
    public function getModel(): string
    {
        return Property::class;
    }

    public function getAllOrSearchProperties(Request $request, array $sort = []): LengthAwarePaginator
    {
        $user = Auth::user();
        $query = $this->model
            ->select([
                'properties.*',
                'users.name as user_name',
                'provinces.name as province_name',
                'wards.name as ward_name',
                DB::raw(
                    '(SELECT pi.image_url FROM property_images pi ' .
                    'WHERE pi.property_id = properties.id ' .
                    'ORDER BY pi.sort ASC, pi.id ASC LIMIT 1) as cover_image_url'
                ),
            ])
            ->leftJoin('users', 'properties.user_id', '=', 'users.id')
            ->leftJoin('provinces', 'properties.province_id', '=', 'provinces.id')
            ->leftJoin('wards', 'properties.ward_id', '=', 'wards.id');

        if ($user && $user->role !== UserType::ADMIN) {
            $query->where('properties.user_id', $user->id);
        }

        if ($request->filled('partner_id')) {
            $query->where('properties.user_id', $request->partner_id);
        }

        if ($request->filled('name')) {
            $query->whereRaw('LOWER(properties.name) LIKE ?', ['%' . strtolower($request->name) . '%']);
        }

        if ($request->filled('keyword')) {
            $this->applyPropertyKeywordFilter($query, (string) $request->keyword);
        }

        if ($request->filled('ward_name')) {
            $query->whereRaw('LOWER(wards.name) LIKE ?', ['%' . strtolower($request->ward_name) . '%']);
        }

        if ($request->filled('province_name')) {
            $query->whereRaw('LOWER(provinces.name) LIKE ?', ['%' . strtolower($request->province_name) . '%']);
        }

        if ($request->filled('year_built')) {
            $query->where('properties.year_built', $request->year_built);
        }

        if ($request->filled('property_type_id')) {
            $query->where('properties.property_type_id', $request->property_type_id);
        }

        if ($request->filled('rent_category')) {
            $query->where('properties.rent_category', $request->rent_category);
        }

        if ($request->filled('area_min')) {
            $query->where('properties.area', '>=', $request->area_min);
        }

        if ($request->filled('area_max')) {
            $query->where('properties.area', '<=', $request->area_max);
        }

        if (!empty($sort)) {
            foreach ($sort as $item) {
                $filed = $item['field'];
                $order = $item['order'] ?? 'asc';

                switch ($filed) {
                    case 'user_name':
                        $query->orderBy('users.name', $order);
                        break;
                    case 'province_name':
                        $query->orderBy('provinces.name', $order);
                        break;
                    case 'ward_name':
                        $query->orderBy('wards.name', $order);
                        break;
                    default:
                        $query->orderBy("properties.$filed", $order);
                }
            }
        } else {
            $query->orderBy('properties.id', 'desc');
        }

        $perPage = (int) ($request->filled('per_page') ?
            $request->per_page : config('const.DEFAULT_PER_PAGE'));
        $page = (int) ($request->filled('page') ? $request->page : config('const.DEFAULT_PAGE'));

        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    public function getAllPropertyTypes(): Collection
    {
        return \App\Models\PropertyType::where('is_active', true)
            ->select('id as value', 'name as label', 'slug')
            ->get();
    }

    public function getPropertyById(int $id): object | null
    {
        $user = Auth::user();

        $query = $this->model->with(['province', 'ward', 'user'])
            ->where('id', $id);

        if ($user->role !== UserType::ADMIN) {
            $query->where('user_id', $user->id);
        }

        return $query->first() ?? null;
    }

    public function getAllPropertyNames(): Collection
    {
        $user = Auth::user();
        $query = $this->model->select('id', 'name');

        if ($user && $user->role !== UserType::ADMIN) {
            $query->where('user_id', $user->id);
        }

        return $query->get();
    }

    public function getPropertiesForPartner(int $partnerId, Request $request, array $sort = []): LengthAwarePaginator
    {
        $roomsLoadMode = $this->resolvePartnerRoomsLoadMode($request);
        $includeCover  = $this->shouldIncludePartnerPropertyCover($request);
        $needsUserJoin = $this->partnerPropertySortIncludes($sort, 'user_name');
        $needsProvinceJoin = true;
        $needsWardJoin = true;

        $today = now()->toDateString();
        $confirmedStatus = BookingStatus::CONFIRMED->value;

        $selectColumns = [
            'properties.*',
            $this->buildVacantRoomsCountSelectRaw($today, $confirmedStatus),
        ];

        if ($needsUserJoin) {
            $selectColumns[] = 'users.name as user_name';
        }
        $selectColumns[] = 'provinces.name as province_name';
        $selectColumns[] = 'wards.name as ward_name';


        if ($includeCover) {
            $selectColumns[] = DB::raw(
                '(SELECT pi.image_url FROM property_images pi ' .
                'WHERE pi.property_id = properties.id ' .
                'ORDER BY pi.sort ASC, pi.id ASC LIMIT 1) as cover_image_url'
            );
        }

        $query = $this->model
            ->select($selectColumns)
            ->where('properties.user_id', $partnerId)
            ->withCount('rooms');

        if ($needsUserJoin) {
            $query->leftJoin('users', 'properties.user_id', '=', 'users.id');
        }
        $query->leftJoin('provinces', 'properties.province_id', '=', 'provinces.id');
        $query->leftJoin('wards', 'properties.ward_id', '=', 'wards.id');


        $this->applyPartnerRoomsEagerLoad($query, $request, $roomsLoadMode);

        if ($request->filled('id')) {
            $query->where('properties.id', (int) $request->id);
        }

        if ($request->filled('name')) {
            $query->whereRaw('LOWER(properties.name) LIKE ?', ['%' . strtolower($request->name) . '%']);
        }

        if ($request->filled('keyword')) {
            $this->applyPropertyKeywordFilter($query, (string) $request->keyword);
        }

        if ($request->filled('ward_name')) {
            $query->whereRaw('LOWER(wards.name) LIKE ?', ['%' . strtolower($request->ward_name) . '%']);
        }

        if ($request->filled('province_name')) {
            $query->whereRaw('LOWER(provinces.name) LIKE ?', ['%' . strtolower($request->province_name) . '%']);
        }

        if ($request->filled('year_built')) {
            $query->where('properties.year_built', $request->year_built);
        }

        if ($request->filled('property_type_id')) {
            $query->where('properties.property_type_id', $request->property_type_id);
        }

        if ($request->filled('rent_category')) {
            $query->where('properties.rent_category', $request->rent_category);
        }

        if ($request->filled('occupancy_filter')) {
            $this->applyPartnerOccupancyFilter($query, (string) $request->occupancy_filter);
        }

        if ($request->has('min_rating') && $request->input('min_rating') !== '' && $request->input('min_rating') !== null) {
            $this->applyPartnerMinRatingFilter($query, $partnerId, (float) $request->min_rating);
        }

        if ($request->has('has_rooms') && $request->input('has_rooms') !== '' && $request->input('has_rooms') !== null) {
            $hasRooms = (int) $request->has_rooms;
            if ($hasRooms === 1) {
                $query->having('rooms_count', '>', 0);
            } elseif ($hasRooms === 0) {
                $query->having('rooms_count', '=', 0);
            }
        }

        if ($request->filled('area_min')) {
            $query->where('properties.area', '>=', $request->area_min);
        }

        if ($request->filled('area_max')) {
            $query->where('properties.area', '<=', $request->area_max);
        }

        if (!empty($sort)) {
            $reviewSortJoined = false;

            foreach ($sort as $item) {
                $filed = $item['field'];
                $order = $item['order'] ?? 'asc';

                switch ($filed) {
                    case 'user_name':
                        $query->orderBy('users.name', $order);
                        break;
                    case 'province_name':
                        $query->orderBy('provinces.name', $order);
                        break;
                    case 'ward_name':
                        $query->orderBy('wards.name', $order);
                        break;
                    case 'rooms_count':
                        $query->orderBy('rooms_count', $order);
                        break;
                    case 'reviews_avg_rating':
                        if (! $reviewSortJoined) {
                            $this->joinPartnerPropertyReviewSort($query, $partnerId);
                            $reviewSortJoined = true;
                        }
                        $this->applyPartnerPropertyReviewSortOrder($query, $order);
                        break;
                    default:
                        $query->orderBy("properties.$filed", $order);
                }
            }
        } else {
            $query->orderBy('properties.id', 'desc');
        }

        $perPage = (int) ($request->filled('per_page') ? $request->per_page : config('const.DEFAULT_PER_PAGE'));
        $page = (int) ($request->filled('page') ? $request->page : config('const.DEFAULT_PAGE'));

        $properties = $query->paginate($perPage, ['*'], 'page', $page);
        $this->attachPartnerPropertyReviewAggregates($properties, $partnerId);

        return $properties;
    }

    private function partnerPropertySortIncludes(array $sort, string $field): bool
    {
        foreach ($sort as $item) {
            if (($item['field'] ?? null) === $field) {
                return true;
            }
        }

        return false;
    }

    private function attachPartnerPropertyReviewAggregates(LengthAwarePaginator $properties, int $partnerId): void
    {
        $propertyIds = $properties->getCollection()
            ->pluck('id')
            ->map(static fn ($id) => (int) $id)
            ->filter()
            ->values();

        if ($propertyIds->isEmpty()) {
            return;
        }

        $reviewAggregates = DB::table('reviews')
            ->join('rooms', 'reviews.room_id', '=', 'rooms.id')
            ->join('properties', 'rooms.property_id', '=', 'properties.id')
            ->where('properties.user_id', $partnerId)
            ->whereIn('rooms.property_id', $propertyIds)
            ->select([
                'rooms.property_id',
                DB::raw('COUNT(reviews.id) as reviews_count'),
                DB::raw('ROUND(AVG(reviews.rating), 1) as reviews_avg_rating'),
            ])
            ->groupBy('rooms.property_id')
            ->get()
            ->keyBy('property_id');

        $properties->getCollection()->transform(static function ($property) use ($reviewAggregates) {
            $aggregate = $reviewAggregates->get($property->id);
            $property->reviews_count = (int) ($aggregate->reviews_count ?? 0);
            $property->reviews_avg_rating = $aggregate->reviews_avg_rating ?? null;

            return $property;
        });
    }

    public function getPropertyByIdForPartner(int $id, int $partnerId): object|null
    {
        return $this->model->with(['province', 'ward', 'user'])
            ->withCount('rooms')
            ->where('id', $id)
            ->where('user_id', $partnerId)
            ->first();
    }

    public function getPropertyNamesForPartner(int $partnerId): Collection
    {
        return $this->model->select('id', 'name')
            ->where('user_id', $partnerId)
            ->get();
    }

    public function getPropertyRoomPreviewForPartner(int $propertyId, int $partnerId, int $limit): ?array
    {
        /** @var Property|null $property */
        $property = $this->model
            ->where('id', $propertyId)
            ->where('user_id', $partnerId)
            ->withCount('rooms')
            ->first();

        if ($property === null) {
            return null;
        }

        $today = now()->toDateString();
        $confirmedStatus = BookingStatus::CONFIRMED->value;

        $rooms = Room::query()
            ->where('property_id', $propertyId)
            ->with([
                'amenities:id,name',
                'services:id,name',
                'prices:id,room_id,price_package_id,unit,price,deposit_amount,minimum_stay',
            ])
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->select(
                'rooms.id',
                'rooms.property_id',
                'rooms.room_number',
                'rooms.title',
                'rooms.room_type',
                'rooms.status',
                'rooms.area',
                'rooms.people',
            )
            ->selectRaw($this->buildOccupancyStatusSelect($today, $confirmedStatus))
            ->orderBy('rooms.id', 'desc')
            ->limit($limit)
            ->get();

        return [
            'property' => $property,
            'rooms'    => $rooms,
        ];
    }

    /**
     * @return 'none'|'preview'|'full'
     */
    private function resolvePartnerRoomsLoadMode(Request $request): string
    {
        $value = $request->input('with_rooms');

        if ($value === null || $value === '' || $value === false || $value === 0 || $value === '0') {
            return 'none';
        }

        if (is_string($value) && strtolower($value) === 'preview') {
            return 'preview';
        }

        return 'full';
    }

    private function shouldIncludePartnerPropertyCover(Request $request): bool
    {
        if (! $request->filled('include')) {
            return false;
        }

        return str_contains((string) $request->input('include'), 'cover');
    }

    /**
     * @param 'none'|'preview'|'full' $mode
     */
    private function applyPartnerRoomsEagerLoad($query, Request $request, string $mode): void
    {
        if ($mode === 'none') {
            return;
        }

        $roomsLimit = $mode === 'preview'
            ? min(max((int) $request->input('rooms_limit', 6), 1), 20)
            : null;

        $query->with(['rooms' => function ($roomQuery) use ($mode, $roomsLimit): void {
            $relations = [
                'amenities:id,name',
                'services:id,name',
                'prices:id,room_id,price_package_id,unit,price,deposit_amount,minimum_stay',
            ];

            if ($mode === 'full') {
                $relations[] = 'utilityFees';
                $relations['images'] = static function ($imgQuery): void {
                    $imgQuery->where('sort', 1)->select('id', 'room_id', 'image_url');
                };
            }

            $roomQuery->with($relations)
                ->withCount('reviews')
                ->withAvg('reviews', 'rating')
                ->select(
                    'rooms.id',
                    'rooms.property_id',
                    'rooms.room_number',
                    'rooms.title',
                    'rooms.room_type',
                    'rooms.status',
                    'rooms.area',
                    'rooms.people',
                )
                ->orderBy('rooms.id', 'desc');

            if ($roomsLimit !== null) {
                $roomQuery->limit($roomsLimit);
            }
        }]);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder $query
     */
    private function joinPartnerPropertyReviewSort($query, int $partnerId): void
    {
        $sub = DB::table('reviews')
            ->join('rooms', 'reviews.room_id', '=', 'rooms.id')
            ->join('properties as review_properties', 'rooms.property_id', '=', 'review_properties.id')
            ->where('review_properties.user_id', $partnerId)
            ->groupBy('rooms.property_id')
            ->select([
                'rooms.property_id',
                DB::raw('ROUND(AVG(reviews.rating), 1) as reviews_avg_rating_sort'),
            ]);

        $query->leftJoinSub($sub, 'property_review_sort', 'property_review_sort.property_id', '=', 'properties.id');
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder $query
     */
    private function applyPartnerPropertyReviewSortOrder($query, string $order): void
    {
        $direction = strtolower($order) === 'asc' ? 'asc' : 'desc';

        if ($direction === 'desc') {
            $query->orderByRaw('property_review_sort.reviews_avg_rating_sort IS NULL ASC')
                ->orderBy('property_review_sort.reviews_avg_rating_sort', 'desc');

            return;
        }

        $query->orderByRaw('property_review_sort.reviews_avg_rating_sort IS NULL DESC')
            ->orderBy('property_review_sort.reviews_avg_rating_sort', 'asc');
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder $query
     */
    private function applyPropertyKeywordFilter($query, string $keyword): void
    {
        $term = '%' . strtolower($keyword) . '%';

        $query->where(static function ($inner) use ($term): void {
            $inner->whereRaw('LOWER(properties.name) LIKE ?', [$term])
                ->orWhereRaw('LOWER(properties.address_detail) LIKE ?', [$term]);
        });
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder $query
     */
    private function applyPartnerOccupancyFilter($query, string $occupancyFilter): void
    {
        $allowed = ['vacant', 'occupied', 'maintenance'];
        if (! in_array($occupancyFilter, $allowed, true)) {
            return;
        }

        $today = now()->toDateString();
        $confirmedStatus = BookingStatus::CONFIRMED->value;
        $caseExpression = $this->buildOccupancyStatusCaseExpression($today, $confirmedStatus);

        $query->whereExists(static function ($sub) use ($caseExpression, $occupancyFilter): void {
            $sub->select(DB::raw('1'))
                ->from('rooms')
                ->whereColumn('rooms.property_id', 'properties.id')
                ->whereRaw('(' . $caseExpression . ') = ?', [$occupancyFilter]);
        });
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder $query
     */
    private function applyPartnerMinRatingFilter($query, int $partnerId, float $minRating): void
    {
        if ($minRating <= 0) {
            $query->whereNotExists(static function ($sub): void {
                $sub->select(DB::raw('1'))
                    ->from('reviews')
                    ->join('rooms', 'reviews.room_id', '=', 'rooms.id')
                    ->whereColumn('rooms.property_id', 'properties.id');
            });

            return;
        }

        $query->whereIn('properties.id', static function ($sub) use ($partnerId, $minRating): void {
            $sub->select('rooms.property_id')
                ->from('reviews')
                ->join('rooms', 'reviews.room_id', '=', 'rooms.id')
                ->join('properties as rating_properties', 'rooms.property_id', '=', 'rating_properties.id')
                ->where('rating_properties.user_id', $partnerId)
                ->groupBy('rooms.property_id')
                ->havingRaw('AVG(reviews.rating) >= ?', [$minRating]);
        });
    }

    private function buildOccupancyStatusCaseExpression(string $today, int $confirmedStatus): string
    {
        return $this->buildOccupancyStatusCaseExpressionForRoom('rooms', $today, $confirmedStatus);
    }

    private function buildOccupancyStatusCaseExpressionForRoom(string $roomAlias, string $today, int $confirmedStatus): string
    {
        return "CASE
            WHEN {$roomAlias}.status = 0 THEN 'hidden'
            WHEN EXISTS (
                SELECT 1 FROM bookings
                WHERE bookings.room_id = {$roomAlias}.id
                AND bookings.status = {$confirmedStatus}
                AND bookings.start_date <= '{$today}'
                AND bookings.end_date >= '{$today}'
            ) THEN 'occupied'
            WHEN EXISTS (
                SELECT 1 FROM room_maintenances
                WHERE room_maintenances.room_id = {$roomAlias}.id
                AND room_maintenances.status IN ('planned', 'in_progress')
                AND DATE(room_maintenances.start_time) <= '{$today}'
                AND (room_maintenances.end_time IS NULL OR DATE(room_maintenances.end_time) >= '{$today}')
            ) THEN 'maintenance'
            ELSE 'vacant'
        END";
    }

    private function buildVacantRoomsCountSelectRaw(string $today, int $confirmedStatus): \Illuminate\Database\Query\Expression
    {
        $caseExpression = $this->buildOccupancyStatusCaseExpressionForRoom('r', $today, $confirmedStatus);

        return DB::raw(
            "(SELECT COUNT(*) FROM rooms r WHERE r.property_id = properties.id AND ({$caseExpression}) = 'vacant') as vacant_rooms_count"
        );
    }

    private function buildOccupancyStatusSelect(string $today, int $confirmedStatus): string
    {
        return $this->buildOccupancyStatusCaseExpression($today, $confirmedStatus) . ' as occupancy_status';
    }
}
