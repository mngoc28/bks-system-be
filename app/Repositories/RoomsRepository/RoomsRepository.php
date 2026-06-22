<?php

declare(strict_types=1);

namespace App\Repositories\RoomsRepository;

use App\Enums\BookingStatus;
use App\Enums\ImageType;
use App\Enums\RoomStatus;
use App\Models\Property;
use App\Models\Room;
use App\Repositories\BaseRepository;
use App\Repositories\RoomsRepository\RoomsRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Pipeline\Pipeline;

/**
 * Class RoomsRepository
 *
 * @package App\Repositories\RoomsRepository
 */
class RoomsRepository extends BaseRepository implements RoomsRepositoryInterface
{
    /**
     * Get the model class name
     *
     * @return string
     */
    public function getModel(): string
    {
        return Room::class;
    }

    /**
     * Build a database-specific SQL expression for aggregated room prices.
     *
     * @return string
     */
    private function allPricesExpression(): string
    {
        return Room::allPricesSql() . ' as all_prices';
    }

    /**
     * Get count of empty rooms
     *
     * @return int
     */
    public function getEmptyRooms(): int
    {
        $today = now()->toDateString();
        return $this->model
            ->where('status', RoomStatus::PUBLIC)
            ->whereDoesntHave('bookings', function ($query) use ($today) {
                $query->whereIn('status', [
                    BookingStatus::PENDING->value,
                    BookingStatus::CONFIRMED->value,
                    BookingStatus::PENDING_CANCELLATION->value,
                ])
                    ->where('end_date', '>=', $today);
            })->count();
    }

    /**
     * Get all rooms or search by criteria with pagination
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getAllOrSearchRooms($request): LengthAwarePaginator
    {
        // Eager load relationships to avoid N+1 problem
        $query = $this->model->with([
            'prices:id,room_id,price_package_id,unit,price',
            'images' => function ($q) {
                $q->where('sort', 1)->select('id', 'room_id', 'image_url');
            }
        ])
            ->join('properties', 'rooms.property_id', '=', 'properties.id')
            ->join('users', 'properties.user_id', '=', 'users.id')
            ->leftJoin('partner_info as pi', 'pi.user_id', '=', 'users.id')
            ->select(
                'rooms.id',
                'rooms.property_id',
                'rooms.room_number',
                'rooms.title',
                'properties.name as property_name',
                'pi.id as partner_id',
                'rooms.room_type',
                'rooms.status',
                'rooms.area',
                'rooms.people',
                'rooms.bedrooms_count',
                'rooms.beds_count'
            );

        // Filter for partner: only show rooms from properties they manage
        if (Auth::check() && Auth::user()->role === 'partner') {
            $query->where('properties.user_id', Auth::id());
        }

        $query = app(Pipeline::class)
            ->send($query)
            ->through([
                \App\QueryFilters\Rooms\RoomNumber::class,
                \App\QueryFilters\Rooms\PartnerId::class,
                \App\QueryFilters\Rooms\PropertyId::class,
                \App\QueryFilters\Rooms\Title::class,
                \App\QueryFilters\Rooms\RoomType::class,
                \App\QueryFilters\Rooms\Status::class,
            ])
            ->thenReturn();

        $this->applyAllOrSearchSorting($query, $request);

        // Default sorting
        $perPage = (int) $request->input('per_page', config('const.DEFAULT_PER_PAGE'));
        $page = (int) $request->input('page', config('const.DEFAULT_PAGE'));

        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Apply sorting for search rooms query.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param mixed $request
     * @return void
     */
    private function applyAllOrSearchSorting($query, $request): void
    {
        $sortField = $request->input('sort_field');
        $sortDirection = $request->input('sort_direction');
        $allowedFields = ['id', 'room_number', 'title', 'property_name', 'room_type', 'status', 'area', 'people'];

        if ($sortField && in_array($sortField, $allowedFields, true) && in_array($sortDirection, ['asc', 'desc'], true)) {
            if ($sortField === 'property_name') {
                $query->orderBy('properties.name', $sortDirection);
            } else {
                $query->orderBy('rooms.' . $sortField, $sortDirection);
            }
        } else {
            $query->orderBy('rooms.id', 'desc');
        }
    }

    /**
     * Get room details by ID
     *
     * @param int|string $id
     * @return mixed
     */
    public function roomDetail($id): mixed
    {
        $query = $this->model->with([
            'amenities:id,name',
            'services:id,name',
            'prices:id,room_id,price_package_id,unit,price',
            'images' => function ($q) {
                $q->select('id', 'room_id', 'image_url', 'image_type');
            }
        ])->join('properties', 'rooms.property_id', '=', 'properties.id')
            ->select($this->getRoomDetailSelectColumns())
            ->where('rooms.id', $id);

        // Filter for partner: only show rooms from properties they manage
        if (Auth::check() && Auth::user()->role === 'partner') {
            $query->where('properties.user_id', Auth::id());
        }
        return $query->first();
    }

    /**
     * Get select columns for room detail.
     *
     * @return array
     */
    private function getRoomDetailSelectColumns(): array
    {
        return [
            'rooms.id',
            'rooms.room_number',
            'rooms.title',
            'rooms.property_id',
            'rooms.room_type',
            'rooms.floor_number',
            'rooms.deposit',
            'rooms.status',
            'rooms.area',
            'rooms.people',
            'rooms.bedrooms_count',
            'rooms.beds_count',
            'rooms.description',
            'rooms.created_at',
            'rooms.updated_at',
            'properties.name as property_name',
            'properties.province_id as province_id',
            'properties.property_type_id as property_type_id'
        ];
    }

    /**
     * Get room names by property ID
     *
     * @param int $propertyId
     * @return \Illuminate\Support\Collection
     */
    public function getRoomNamesByPropertyId(int $propertyId): \Illuminate\Support\Collection
    {
        return $this->model
            ->where('property_id', $propertyId)
            ->select('id', 'room_number')
            ->get();
    }

    // ====== The functions below are APIs for the end user ======
    /**
     * Get top rated rooms
     *
     * @param \Illuminate\Http\Request $request
     * @return object | null
     */
    public function getTopRatedRooms(Request $request): object | null
    {
        $roomsQuery = Room::query()->withListJoins();


        $this->applyTopRatedQueryLogic($roomsQuery);

        $limit = $request->filled('limit') ? (int) $request->limit : (int) config('const.DEFAULT_PER_PAGE');

        return $roomsQuery->limit($limit)->get();
    }

    /**
     * Apply sorting, reviews left join, and select columns for top rated query.
     *
     * @param \Illuminate\Database\Eloquent\Builder $roomsQuery
     * @return void
     */
    private function applyTopRatedQueryLogic($roomsQuery): void
    {
        $roomsQuery
            ->leftJoin(DB::raw('(
                SELECT room_id, 
                       COUNT(*) as reviews_count, 
                       ROUND(AVG(rating), 1) as reviews_avg_rating 
                FROM reviews 
                GROUP BY room_id
            ) as rev'), 'rooms.id', '=', 'rev.room_id')
            ->where('rooms.status', RoomStatus::PUBLIC)
            ->select(array_merge($this->getBaseSelectColumns(), [
                DB::raw('COALESCE(rev.reviews_count, 0) as reviews_count'),
                DB::raw('COALESCE(rev.reviews_avg_rating, 0) as reviews_avg_rating')
            ]))
            ->orderByRaw('reviews_avg_rating DESC')
            ->orderByRaw('reviews_count DESC')
            ->orderBy('rooms.updated_at', 'desc');
    }

    /**
     * Get room list with filters
     *
     * @param \Illuminate\Http\Request $request
     * @return object|null
     */
    public function getRoomList(Request $request): object | null
    {
        $selectColumns = $this->getBaseSelectColumns();
        $groupByColumns = $this->getBaseGroupByColumns();

        if ($request->boolean('with_details')) {
            $selectColumns[] = 'rooms.description';
            $selectColumns[] = DB::raw('(SELECT GROUP_CONCAT(DISTINCT a.name) FROM room_amenities ra JOIN amenities a ON ra.amenity_id = a.id WHERE ra.room_id = rooms.id) as amenities');
            if (DB::getDriverName() === 'sqlite') {
                $selectColumns[] = DB::raw('(SELECT GROUP_CONCAT(s.name || " (" || CAST(s.price AS TEXT) || "đ)") FROM room_services rs JOIN services s ON rs.service_id = s.id WHERE rs.room_id = rooms.id) as services');
            } else {
                $selectColumns[] = DB::raw('(SELECT GROUP_CONCAT(DISTINCT CONCAT(s.name, " (", s.price, "đ)")) FROM room_services rs JOIN services s ON rs.service_id = s.id WHERE rs.room_id = rooms.id) as services');
            }
            $groupByColumns[] = 'rooms.description';
        }

        $extraSelectColumns = $this->getRoomListReviewSelectColumns();

        if ($this->needsProvinceRowNumber($request)) {
            $extraSelectColumns[] = DB::raw('ROW_NUMBER() OVER (PARTITION BY p.id ORDER BY rooms.updated_at DESC) as province_row_num');
        }

        $roomsQuery = Room::query()->withListJoins()
            ->where('rooms.status', RoomStatus::PUBLIC);

        $this->applyReviewAggregateJoin($roomsQuery);
        $roomsQuery->select(array_merge($selectColumns, $extraSelectColumns));

        $roomsQuery = app(Pipeline::class)
            ->send($roomsQuery)
            ->through([
                \App\QueryFilters\Rooms\ProvinceId::class,
                \App\QueryFilters\Rooms\WardId::class,
                \App\QueryFilters\Rooms\PropertyTypeId::class,
                \App\QueryFilters\Rooms\Keyword::class,
                \App\QueryFilters\Rooms\PartnerId::class,
                \App\QueryFilters\Rooms\TouristSpotSlug::class,
                \App\QueryFilters\Rooms\AmenityIds::class,
                \App\QueryFilters\Rooms\ServiceIds::class,
            ])
            ->thenReturn();

        if ($this->needsWrappedRoomListQuery($request)) {
            $roomsQueryForExecution = DB::table(DB::raw("({$roomsQuery->toSql()}) as ranked_rooms"))
                ->mergeBindings($roomsQuery->getQuery());

            $this->applyRoomListLimitsAndSorting($roomsQueryForExecution, $request);

            if ($request->filled('page') || $request->filled('per_page')) {
                $perPage = (int) $request->input('per_page', config('const.DEFAULT_PER_PAGE'));
                $page = (int) $request->input('page', config('const.DEFAULT_PAGE'));

                return $roomsQueryForExecution
                    ->select('*')
                    ->paginate($perPage, ['*'], 'page', $page)
                    ->appends($request->query());
            }

            return $roomsQueryForExecution->select('*')->get();
        }

        $this->applyRoomListLimitsAndSortingOnBuilder($roomsQuery, $request);

        if ($request->filled('page') || $request->filled('per_page')) {
            $perPage = (int) $request->input('per_page', config('const.DEFAULT_PER_PAGE'));
            $page = (int) $request->input('page', config('const.DEFAULT_PAGE'));

            return $roomsQuery
                ->paginate($perPage, ['*'], 'page', $page)
                ->appends($request->query());
        }

        return $roomsQuery->get();
    }

    /**
     * @return array<int, \Illuminate\Database\Query\Expression|string>
     */
    private function getRoomListReviewSelectColumns(): array
    {
        return [
            DB::raw('COALESCE(rev.reviews_count, 0) as reviews_count'),
            DB::raw('COALESCE(rev.reviews_avg_rating, 0) as reviews_avg_rating'),
        ];
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $roomsQuery
     */
    private function applyReviewAggregateJoin($roomsQuery): void
    {
        $reviewsSubquery = DB::table('reviews')
            ->select('room_id')
            ->selectRaw('COUNT(*) as reviews_count')
            ->selectRaw('ROUND(AVG(rating), 1) as reviews_avg_rating')
            ->groupBy('room_id');

        $roomsQuery->leftJoinSub($reviewsSubquery, 'rev', 'rooms.id', '=', 'rev.room_id');
    }

    /**
     * Whether the homepage-style per-province row cap is required.
     */
    private function needsProvinceRowNumber(Request $request): bool
    {
        return ! $this->hasRoomListSpecificFilter($request)
            && ! ($request->filled('page') || $request->filled('per_page'));
    }

    /**
     * Whether computed select aliases must be filtered via a wrapped subquery.
     */
    private function needsWrappedRoomListQuery(Request $request): bool
    {
        return $this->needsProvinceRowNumber($request);
    }

    /**
     * @param array<int, mixed>|null $provinceIds
     */
    private function hasRoomListSpecificFilter(Request $request, ?array $provinceIds = null): bool
    {
        $provinceIds ??= $request->input('province_ids', []);

        return $request->filled('province_id')
            || ! empty($provinceIds)
            || $request->filled('ward_id')
            || $request->filled('property_type_id')
            || $request->filled('keyword')
            || $request->filled('partner_id')
            || $request->filled('tourist_spot_slug')
            || $request->filled('price_min')
            || $request->filled('price_max')
            || $request->filled('guests')
            || $request->filled('rent_type');
    }

    /**
     * Apply limits and sorting directly on the Eloquent room list query.
     *
     * @param \Illuminate\Database\Eloquent\Builder $roomsQuery
     */
    private function applyRoomListLimitsAndSortingOnBuilder($roomsQuery, Request $request): void
    {
        if ($request->filled('price_min')) {
            $roomsQuery->where('rp.cheapest_daily_price', '>=', (float) $request->input('price_min'));
        }
        if ($request->filled('price_max')) {
            $roomsQuery->where('rp.cheapest_daily_price', '<=', (float) $request->input('price_max'));
        }
        if ($request->filled('guests')) {
            $roomsQuery->where('rooms.people', '>=', (int) $request->input('guests'));
        }
        if ($request->filled('rating_min')) {
            $roomsQuery->whereRaw('COALESCE(rev.reviews_avg_rating, 0) >= ?', [(float) $request->input('rating_min')]);
        }
        if ($request->filled('rent_type')) {
            $this->applyRentTypeFilterOnBuilder($roomsQuery, (string) $request->input('rent_type'));
        }

        $sortBy = $request->input('sort_by');
        $sortDirection = $request->input('sort_direction', 'asc');

        if (in_array($sortBy, ['cheapest_daily_price', 'people'], true) && in_array($sortDirection, ['asc', 'desc'], true)) {
            if ($sortBy === 'cheapest_daily_price') {
                $roomsQuery->orderBy('rp.cheapest_daily_price', $sortDirection);
            } else {
                $roomsQuery->orderBy('rooms.people', $sortDirection);
            }

            $roomsQuery->orderBy('rooms.id', 'desc');

            return;
        }

        $roomsQuery->orderBy('rooms.id', 'desc');
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $roomsQuery
     */
    private function applyRentTypeFilterOnBuilder($roomsQuery, string $rentType): void
    {
        if ($rentType === 'daily') {
            $roomsQuery->where(function ($query): void {
                $query->where('pt.name', 'NOT LIKE', '%căn hộ%')
                    ->where('pt.name', 'NOT LIKE', '%apartment%');
            });

            return;
        }

        if ($rentType === 'monthly') {
            $roomsQuery->where(function ($query): void {
                $query->where('pt.name', 'LIKE', '%căn hộ%')
                    ->orWhere('pt.name', 'LIKE', '%apartment%');
            });
        }
    }

    /**
     * Apply limits and sorting to the raw wrapped room list query.
     *
     * @param mixed $roomsQueryForExecution
     * @param \Illuminate\Http\Request $request
     * @return void
     */
    private function applyRoomListLimitsAndSorting($roomsQueryForExecution, Request $request): void
    {
        $shouldPaginate = $request->filled('page') || $request->filled('per_page');
        $hasSpecificFilter = $this->hasRoomListSpecificFilter($request);

        if ($request->filled('price_min')) {
            $roomsQueryForExecution->where('cheapest_daily_price', '>=', floatval($request->input('price_min')));
        }
        if ($request->filled('price_max')) {
            $roomsQueryForExecution->where('cheapest_daily_price', '<=', floatval($request->input('price_max')));
        }
        if ($request->filled('guests')) {
            $roomsQueryForExecution->where('people', '>=', intval($request->input('guests')));
        }
        if ($request->filled('rating_min')) {
            $roomsQueryForExecution->where('reviews_avg_rating', '>=', floatval($request->input('rating_min')));
        }
        if ($request->filled('rent_type')) {
            $rentType = $request->input('rent_type');
            if ($rentType === 'daily') {
                $roomsQueryForExecution->where(function ($q) {
                    $q->where('property_type_name', 'NOT LIKE', '%căn hộ%')
                      ->where('property_type_name', 'NOT LIKE', '%apartment%');
                });
            } elseif ($rentType === 'monthly') {
                $roomsQueryForExecution->where(function ($q) {
                    $q->where('property_type_name', 'LIKE', '%căn hộ%')
                      ->orWhere('property_type_name', 'LIKE', '%apartment%');
                });
            }
        }

        if (!$hasSpecificFilter && !$shouldPaginate) {
            $limitPerProvince = $request->input('limit') ?: $request->input('per_page') ?: config('const.DEFAULT_PER_PAGE');
            $roomsQueryForExecution->where('province_row_num', '<=', (int) $limitPerProvince);
        }

        $sortBy = $request->input('sort_by');
        $sortDirection = $request->input('sort_direction', 'asc');

        if (in_array($sortBy, ['cheapest_daily_price', 'people'], true) && in_array($sortDirection, ['asc', 'desc'], true)) {
            $roomsQueryForExecution->orderBy($sortBy, $sortDirection)->orderBy('id', 'desc');
        } else {
            $roomsQueryForExecution->orderBy('id', 'desc');
        }
    }

    /**
     * Get room info for sending mail
     *
     * @param int $roomId
     * @return object|null
     */
    public function getRoomInfoSendMail(int $roomId): object | null
    {
        return $this->model
            ->with(['amenities:id,name', 'services:id,name,price'])
            ->join('properties as b', 'rooms.property_id', '=', 'b.id')
            ->join('users as u', 'b.user_id', '=', 'u.id')
            ->leftjoin('partner_info as pi', 'pi.id', '=', 'u.id')
            ->where('rooms.id', $roomId)
            ->select(
                'rooms.id',
                'rooms.title',
                'rooms.description',
                'rooms.room_number',
                'pi.company_name',
                'pi.phone as company_phone',
                'pi.address',
                'b.name as property_name',
                'b.address_detail as property_address'
            )
            ->first();
    }

    /**
     * Get public room detail by ID
     *
     * @param int $id
     * @return object|null
     */
    public function getPublicRoomDetail(int $id): object | null
    {
        return $this->model
            ->leftJoin('room_amenities as ra', 'rooms.id', '=', 'ra.room_id')
            ->leftJoin('amenities as a', 'ra.amenity_id', '=', 'a.id')
            ->leftJoin('room_prices as rp', 'rooms.id', '=', 'rp.room_id')
            ->leftJoin('room_images as ri', function ($join) {
                $join->on('rooms.id', '=', 'ri.room_id')->where('ri.sort', 1);
            })
            ->join('properties as b', 'rooms.property_id', '=', 'b.id')
            ->join('users as u', 'b.user_id', '=', 'u.id')
            ->leftJoin('partner_info as pi', 'pi.user_id', '=', 'u.id')
            ->join('provinces as p', 'b.province_id', '=', 'p.id')
            ->leftJoin('property_types as pt', 'b.property_type_id', '=', 'pt.id')
            ->leftjoin('room_services as rs', 'rooms.id', '=', 'rs.room_id')
            ->leftJoin('services as s', 'rs.service_id', '=', 's.id')
            ->leftJoin('utility_fees as uf', 'rooms.id', '=', 'uf.room_id')
            ->select($this->getPublicRoomDetailSelectColumns())
            ->groupBy($this->getPublicRoomDetailGroupByColumns())
            ->where('rooms.id', $id)
            ->first();
    }

    /**
     * Get select columns for public room detail.
     *
     * @return array
     */
    private function getPublicRoomDetailSelectColumns(): array
    {
        return [
            'rooms.id',
            'rooms.title',
            'rooms.room_type',
            'rooms.people',
            'rooms.base_people',
            'rooms.extra_people_fee',
            'rooms.bedrooms_count',
            'rooms.beds_count',
            'rooms.description',
            'rooms.area',
            'ri.image_url as room_image',
            'b.name as property_name',
            'b.address_detail as property_address',
            'b.pet_policy',
            'b.pet_policy_note',
            'b.standard_checkin_start',
            'b.standard_checkout_end',
            'b.checkin_method',
            'b.smoking_allowed',
            'b.parties_allowed',
            'b.quiet_hours_start',
            'b.quiet_hours_end',
            'b.has_elevator',
            'b.has_step_free_access',
            'b.is_ground_floor',
            'p.name as province_name',
            'pt.name as property_type_name',
            'b.property_type_id',
            'pi.id as partner_id',
            'u.name as partner_name',
            'u.email as partner_email',
            'pi.company_name as partner_company_name',
            'pi.phone as partner_phone',
            'pi.address as partner_address',
            'pi.description as partner_description',
            DB::raw('CONCAT(\'[\', GROUP_CONCAT(DISTINCT CONCAT(' .
                '\'{"id":\', s.id, \',"name":"\', REPLACE(s.name, \'"\', \'\\"\'), \'","price":\', s.price, \'}\'' .
                ')), \']\') as services'),
            DB::raw('GROUP_CONCAT(DISTINCT a.name) as amenities'),
            DB::raw($this->getCheapestDailyPriceExpression() . ' as cheapest_daily_price'),
            DB::raw(Room::allPricesSql() . ' as all_prices'),
            DB::raw('IFNULL(CONCAT(\'[\', GROUP_CONCAT(DISTINCT CONCAT(' .
                '\'{"type":"\', uf.fee_type, \'", "method":"\', uf.calc_method, ' .
                '\'", "price":\', uf.unit_price, \', "included":\', uf.is_included, \'}\'' .
                ')), \']\'), \'[]\') as utility_fees'),
            DB::raw('(SELECT COUNT(*) FROM reviews WHERE reviews.room_id = rooms.id) as reviews_count'),
            DB::raw('(SELECT ROUND(AVG(rating), 1) FROM reviews WHERE reviews.room_id = rooms.id) as reviews_avg_rating'),
            DB::raw('(SELECT JSON_ARRAYAGG(JSON_OBJECT(\'id\', rim.id, \'image_url\', rim.image_url)) FROM (SELECT id, image_url FROM room_images WHERE room_id = rooms.id ORDER BY sort ASC) rim) as images'),
        ];
    }

    /**
     * Get group by columns for public room detail.
     *
     * @return array
     */
    private function getPublicRoomDetailGroupByColumns(): array
    {
        return [
            'rooms.id',
            'rooms.title',
            'rooms.room_type',
            'rooms.people',
            'rooms.base_people',
            'rooms.extra_people_fee',
            'rooms.bedrooms_count',
            'rooms.beds_count',
            'rooms.description',
            'rooms.area',
            'ri.image_url',
            'b.name',
            'b.address_detail',
            'b.pet_policy',
            'b.pet_policy_note',
            'b.standard_checkin_start',
            'b.standard_checkout_end',
            'b.checkin_method',
            'b.smoking_allowed',
            'b.parties_allowed',
            'b.quiet_hours_start',
            'b.quiet_hours_end',
            'b.has_elevator',
            'b.has_step_free_access',
            'b.is_ground_floor',
            'rooms.updated_at',
            'p.name',
            'pt.name',
            'b.property_type_id',
            'u.id',
            'pi.id',
            'u.name',
            'u.email',
            'pi.company_name',
            'pi.phone',
            'pi.address',
            'pi.description'
        ];
    }

    // =========================================================================
    // PARTNER METHODS
    // =========================================================================

    /**
     * Get all rooms for a specific partner
     *
     * @param int $partnerId
     * @param mixed $request
     * @return LengthAwarePaginator
     */
    public function getRoomsForPartner(int $partnerId, $request): LengthAwarePaginator
    {
        if ($request->filled('property_id')) {
            return $this->getRoomsForPartnerProperty($partnerId, (int) $request->property_id, $request);
        }

        $query = $this->model->with([
            'amenities:id,name',
            'services:id,name',
            'prices:id,room_id,price_package_id,unit,price,deposit_amount,minimum_stay',
        ])
            ->join('properties', 'rooms.property_id', '=', 'properties.id')
            ->select($this->getPartnerRoomsListSelectColumns())
            ->where('properties.user_id', $partnerId)
            ->orderBy('rooms.id', 'desc');

        $query = app(Pipeline::class)
            ->send($query)
            ->through([
                \App\QueryFilters\Rooms\RoomNumber::class,
                \App\QueryFilters\Rooms\Status::class,
            ])
            ->thenReturn();

        $this->applyRoomsForPartnerPropertyFilters($query, $request);
        $this->applyPartnerOccupancyFilter($query, $request);

        $perPage = $request->input('per_page', config('const.DEFAULT_PER_PAGE'));
        $page = $request->input('page', config('const.DEFAULT_PAGE'));

        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    private function getRoomsForPartnerProperty(int $partnerId, int $propertyId, $request): LengthAwarePaginator
    {
        $perPage = (int) $request->input('per_page', config('const.DEFAULT_PER_PAGE'));
        $page = (int) $request->input('page', config('const.DEFAULT_PAGE'));

        $property = Property::query()
            ->select('id', 'name')
            ->where('id', $propertyId)
            ->where('user_id', $partnerId)
            ->first();

        if ($property === null) {
            return new \Illuminate\Pagination\LengthAwarePaginator([], 0, $perPage, $page);
        }

        $query = $this->model->with([
            'amenities:id,name',
            'services:id,name',
            'prices:id,room_id,price_package_id,unit,price,deposit_amount,minimum_stay',
        ])
            ->select($this->getPartnerRoomsListSelectColumns(includePropertyName: false))
            ->where('rooms.property_id', $propertyId)
            ->orderBy('rooms.id', 'desc');

        $query = app(Pipeline::class)
            ->send($query)
            ->through([
                \App\QueryFilters\Rooms\RoomNumber::class,
                \App\QueryFilters\Rooms\Status::class,
            ])
            ->thenReturn();

        $this->applyPartnerOccupancyFilter($query, $request);

        $rooms = $query->paginate($perPage, ['*'], 'page', $page);
        $rooms->getCollection()->transform(static function ($room) use ($property) {
            $room->property_name = $property->name;

            return $room;
        });

        return $rooms;
    }

    /**
     * Apply property ID filters for partner query.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param mixed $request
     * @return void
     */
    private function applyRoomsForPartnerPropertyFilters($query, $request): void
    {
        if ($request->filled('property_ids') && is_array($request->input('property_ids'))) {
            $ids = array_values(array_unique(array_filter(array_map('intval', $request->input('property_ids')))));
            if ($ids !== []) {
                $query->whereIn('rooms.property_id', $ids);
            }
        } elseif ($request->filled('property_id')) {
            $query->where('rooms.property_id', $request->property_id);
        }
    }

    /**
     * Get room detail for a specific partner
     *
     * @param int $id
     * @param int $partnerId
     * @return mixed
     */
    public function getRoomDetailForPartner(int $id, int $partnerId): mixed
    {
        return $this->model->with([
            'amenities:id,name',
            'services:id,name',
            'prices:id,room_id,price_package_id,unit,price,deposit_amount,minimum_stay',
            'utilityFees',
            'images' => function ($q) {
                $q->select('id', 'room_id', 'image_url', 'image_type');
            }
        ])->join('properties', 'rooms.property_id', '=', 'properties.id')
            ->join('users as property_owner', 'properties.user_id', '=', 'property_owner.id')
            ->select($this->getRoomDetailForPartnerSelectColumns())
            ->where('rooms.id', $id)
            ->where('properties.user_id', $partnerId)
            ->first();
    }

    /**
     * Get select columns for partner room detail.
     *
     * @return array
     */
    private function getRoomDetailForPartnerSelectColumns(): array
    {
        return [
            'rooms.id',
            'rooms.room_number',
            'rooms.title',
            'rooms.property_id',
            'rooms.room_type',
            'rooms.floor_number',
            'rooms.deposit',
            'rooms.status',
            'rooms.housekeeping_status',
            'rooms.area',
            'rooms.people',
            'rooms.bedrooms_count',
            'rooms.beds_count',
            'rooms.description',
            'rooms.created_at',
            'rooms.updated_at',
            'properties.name as property_name',
            'properties.province_id as province_id',
            'properties.property_type_id as property_type_id',
            'property_owner.phone as partner_phone',
            'property_owner.email as partner_email',
            DB::raw('(SELECT COUNT(*) FROM reviews WHERE reviews.room_id = rooms.id) as reviews_count'),
            DB::raw('(SELECT ROUND(AVG(rating), 1) FROM reviews WHERE reviews.room_id = rooms.id) as reviews_avg_rating')
        ];
    }

    /**
     * Count rooms for a specific partner
     *
     * @param int $partnerId
     * @param array $filters
     * @return int
     */
    public function countRoomsForPartner(int $partnerId, array $filters = []): int
    {
        $query = $this->model->join('properties', 'rooms.property_id', '=', 'properties.id')
            ->where('properties.user_id', $partnerId);

        if (!empty($filters)) {
            $query->where($filters);
        }

        return $query->count();
    }

    /**
     * Get empty rooms for a specific partner
     *
     * @param int $partnerId
     * @return int
     */
    public function getEmptyRoomsForPartner(int $partnerId, ?int $propertyId = null): int
    {
        $today = now()->toDateString();
        $query = $this->model->join('properties', 'rooms.property_id', '=', 'properties.id')
            ->where('properties.user_id', $partnerId)
            ->where('rooms.status', RoomStatus::PUBLIC);

        if ($propertyId !== null) {
            $query->where('properties.id', $propertyId);
        }

        return $query
            ->whereDoesntHave('bookings', function ($query) use ($today) {
                $query->whereIn('status', [
                    BookingStatus::PENDING->value,
                    BookingStatus::CONFIRMED->value,
                    BookingStatus::PENDING_CANCELLATION->value,
                ])
                    ->where('end_date', '>=', $today);
            })->count();
    }

    /**
     * Get rooms occupancy data for a specific property/partner
     *
     * @param int $partnerId
     * @param int $propertyId
     * @return \Illuminate\Support\Collection
     */
    public function getOccupancyForPartner(int $partnerId, int $propertyId): \Illuminate\Support\Collection
    {
        $today = now()->toDateString();
        $confirmedStatus = BookingStatus::CONFIRMED->value;

        return $this->model
            ->join('properties', 'rooms.property_id', '=', 'properties.id')
            ->where('properties.user_id', $partnerId)
            ->where('rooms.property_id', $propertyId)
            ->select($this->getOccupancySelectColumns($today, $confirmedStatus))
            ->get();
    }

    /**
     * Get select columns for partner occupancy.
     *
     * @param string $today
     * @param int $confirmedStatus
     * @return array
     */
    /**
     * @return array{0: string, 1: int}
     */
    private function partnerOccupancyContext(): array
    {
        return [now()->toDateString(), BookingStatus::CONFIRMED->value];
    }

    private function buildPartnerOccupancyStatusCaseExpression(
        string $roomAlias,
        string $today,
        int $confirmedStatus,
    ): string {
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

    private function buildPartnerOccupancyStatusSelectRaw(string $roomAlias = 'rooms'): \Illuminate\Database\Query\Expression
    {
        [$today, $confirmedStatus] = $this->partnerOccupancyContext();
        $caseExpression = $this->buildPartnerOccupancyStatusCaseExpression($roomAlias, $today, $confirmedStatus);

        return DB::raw("({$caseExpression}) as occupancy_status");
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder $query
     */
    private function applyPartnerOccupancyFilter($query, mixed $request): void
    {
        if (! $request->filled('occupancy')) {
            return;
        }

        $occupancy = (string) $request->input('occupancy');
        $allowed = ['vacant', 'occupied', 'maintenance'];
        if (! in_array($occupancy, $allowed, true)) {
            return;
        }

        [$today, $confirmedStatus] = $this->partnerOccupancyContext();
        $caseExpression = $this->buildPartnerOccupancyStatusCaseExpression('rooms', $today, $confirmedStatus);
        $query->whereRaw('(' . $caseExpression . ') = ?', [$occupancy]);
    }

    /**
     * @return array<int, mixed>
     */
    private function getPartnerRoomsListSelectColumns(bool $includePropertyName = true): array
    {
        $columns = [
            'rooms.id',
            'rooms.property_id',
            'rooms.room_number',
            'rooms.title',
        ];

        if ($includePropertyName) {
            $columns[] = 'properties.name as property_name';
        }

        return array_merge($columns, [
            'rooms.room_type',
            'rooms.status',
            'rooms.housekeeping_status',
            'rooms.area',
            'rooms.people',
            'rooms.bedrooms_count',
            'rooms.beds_count',
            $this->buildPartnerOccupancyStatusSelectRaw('rooms'),
            DB::raw('(SELECT COUNT(*) FROM reviews WHERE reviews.room_id = rooms.id) as reviews_count'),
            DB::raw('(SELECT ROUND(AVG(rating), 1) FROM reviews WHERE reviews.room_id = rooms.id) as reviews_avg_rating'),
        ]);
    }

    private function getOccupancySelectColumns(string $today, int $confirmedStatus): array
    {
        $occupancyStatusSelect = '(' . $this->buildPartnerOccupancyStatusCaseExpression('rooms', $today, $confirmedStatus) . ') as occupancy_status';

        return [
            'rooms.id',
            'rooms.room_number',
            'rooms.title',
            'rooms.floor_number',
            'rooms.status as room_visibility',
            DB::raw($occupancyStatusSelect),
            DB::raw("(SELECT u.name FROM bookings b JOIN users u ON b.user_id = u.id 
                WHERE b.room_id = rooms.id AND b.status = " . $confirmedStatus . "
                AND b.start_date <= '$today' AND b.end_date >= '$today' LIMIT 1) as customer_name"),
            DB::raw("(SELECT u.phone FROM bookings b JOIN users u ON b.user_id = u.id 
                WHERE b.room_id = rooms.id AND b.status = " . $confirmedStatus . "
                AND b.start_date <= '$today' AND b.end_date >= '$today' LIMIT 1) as customer_phone"),
            DB::raw("(SELECT b.start_date FROM bookings b 
                WHERE b.room_id = rooms.id AND b.status = " . $confirmedStatus . "
                AND b.start_date <= '$today' AND b.end_date >= '$today' LIMIT 1) as check_in_date"),
            DB::raw("(SELECT b.end_date FROM bookings b 
                WHERE b.room_id = rooms.id AND b.status = " . $confirmedStatus . "
                AND b.start_date <= '$today' AND b.end_date >= '$today' LIMIT 1) as check_out_date")
        ];
    }

    public function getSuggestedRoomsByProvince(array $provinceIds, int $limit): object | null
    {
        if (empty($provinceIds)) {
            return collect();
        }

        $roomsQuery = Room::query()->withListJoins()
            ->where('rooms.status', RoomStatus::PUBLIC)
            ->whereIn('p.id', $provinceIds);

        $this->applyReviewAggregateJoin($roomsQuery);

        $roomsQuery->select(array_merge($this->getBaseSelectColumns(), $this->getRoomListReviewSelectColumns(), [
                DB::raw('ROW_NUMBER() OVER (PARTITION BY p.id ORDER BY rooms.updated_at DESC) as province_row_num'),
            ]));

        $executionQuery = DB::table(DB::raw("({$roomsQuery->toSql()}) as ranked_rooms"))
            ->mergeBindings($roomsQuery->getQuery())
            ->where('province_row_num', '<=', $limit);

        return $executionQuery->get();
    }

    public function getSuggestedRoomsByTouristSpot(array $touristSpotIds, int $limit): object | null
    {
        if ($touristSpotIds === []) {
            return collect();
        }

        $rowNumberOrder = 'rtsm.is_primary DESC, '
            . 'COALESCE(rev.reviews_avg_rating, 0) DESC, '
            . 'COALESCE(rev.reviews_count, 0) DESC, '
            . 'rtsm.travel_time_minutes ASC, rooms.updated_at DESC';

        $roomsQuery = Room::query()->withListJoins()

            ->join('room_tourist_spot_maps as rtsm', 'rtsm.room_id', '=', 'rooms.id')
            ->join('tourist_spots as ts', 'ts.id', '=', 'rtsm.tourist_spot_id')
            ->where('rooms.status', RoomStatus::PUBLIC)
            ->where('ts.is_active', true)
            ->whereIn('ts.id', $touristSpotIds)
            ->whereNotNull('ts.province_id')
            ->whereColumn('b.province_id', 'ts.province_id')
            ->whereExists(function ($q) {
                $q->select(DB::raw(1))
                    ->from('room_prices')
                    ->whereColumn('room_prices.room_id', 'rooms.id')
                    ->where('room_prices.unit', 'night');
            });

        $this->applyReviewAggregateJoin($roomsQuery);

        $roomsQuery->select(array_merge($this->getBaseSelectColumns(), $this->getRoomListReviewSelectColumns(), [
                'ts.id as tourist_spot_id',
                'ts.name as tourist_spot_name',
                'ts.slug as tourist_spot_slug',
                'ts.region_label as tourist_spot_region_label',
                DB::raw("ROW_NUMBER() OVER (PARTITION BY ts.id ORDER BY {$rowNumberOrder}) as tourist_spot_row_num"),
            ]));

        $executionQuery = DB::table(DB::raw("({$roomsQuery->toSql()}) as ranked_rooms"))
            ->mergeBindings($roomsQuery->getQuery())
            ->where('tourist_spot_row_num', '<=', $limit);

        return $executionQuery->get();
    }

    /**
     * Apply base joins required for room cards.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function applyBaseRoomJoins($query)
    {
        return $query->withBaseJoins();
    }

    /**
     * Get SQL expression for cheapest daily price.
     *
     * @return string
     */
    private function getCheapestDailyPriceExpression(): string
    {
        return Room::cheapestDailyPriceSql();
    }

    private function getBaseSelectColumns(): array
    {
        return [
            'rooms.id',
            'rooms.title',
            'rooms.room_type',
            'rooms.people',
            'rooms.bedrooms_count',
            'rooms.beds_count',
            'rooms.area',
            'rooms.deposit as deposit',
            'rooms.floor_number as floor_number',
            'pi.phone as partner_phone',
            'u.name as partner_name',
            'p.id as province_id',
            'p.name as province_name',
            'p.name_en as province_name_en',
            'ri.image_url as room_image',
            'b.address_detail as property_address',
            'pt.name as property_type_name',
            'pi.company_name as partner_company_name',
            'pi.id as partner_id',
            'rp.cheapest_daily_price as cheapest_daily_price',
            'rp.cheapest_monthly_price as cheapest_monthly_price',
            'rp.cheapest_nightly_price as cheapest_nightly_price',
        ];
    }

    /**
     * Get base group by columns for room cards.
     *
     * @return array
     */
    private function getBaseGroupByColumns(): array
    {
        return [
            'rooms.id',
            'rooms.title',
            'rooms.room_type',
            'rooms.people',
            'rooms.bedrooms_count',
            'rooms.beds_count',
            'rooms.area',
            'rooms.deposit',
            'rooms.floor_number',
            'pi.phone',
            'u.name',
            'p.name',
            'p.name_en',
            'p.id',
            'ri.image_url',
            'b.address_detail',
            'pt.name',
            'rooms.updated_at',
            'pi.company_name',
            'u.id',
            'pi.id',
        ];
    }
}
