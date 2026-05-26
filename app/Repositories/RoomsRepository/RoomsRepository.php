<?php

declare(strict_types=1);

namespace App\Repositories\RoomsRepository;

use App\Enums\BookingStatus;
use App\Enums\ImageType;
use App\Enums\RoomStatus;
use App\Models\Room;
use App\Repositories\BaseRepository;
use App\Repositories\RoomsRepository\RoomsRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        if (DB::getDriverName() === 'sqlite') {
            return <<<'SQL'
COALESCE(
    '[' || GROUP_CONCAT(DISTINCT
        '{"unit":"' || rp.unit || '", "price":' || rp.price ||
        ', "deposit_amount":' || COALESCE(rp.deposit_amount, 0) ||
        ', "minimum_stay":' || COALESCE(rp.minimum_stay, 0) || '}'
    ) || ']',
    '[]'
) as all_prices
SQL;
        }

        return 'IFNULL(CONCAT(\'[\', GROUP_CONCAT(DISTINCT CONCAT(' .
            '\'{"unit":"\', rp.unit, \'", "price":\', rp.price, ' .
            '\', "deposit_amount":\', IFNULL(rp.deposit_amount, 0), ' .
            '\', "minimum_stay":\', IFNULL(rp.minimum_stay, 0), \'}\'' .
            ')), \']\'), \'[]\') as all_prices';
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
            'amenities:id,name',
            'services:id,name',
            'prices:id,room_id,price_package_id,unit,price',
            'images' => function ($q) {
                $q->where('sort', 1)->select('id', 'room_id', 'image_url');
            }
        ])
            ->join('properties', 'rooms.property_id', '=', 'properties.id')
            ->select(
                'rooms.id',
                'rooms.property_id',
                'rooms.room_number',
                'rooms.title',
                'properties.name as property_name',
                'rooms.room_type',
                'rooms.status',
                'rooms.area',
                'rooms.people'
            );

        // Filter for partner: only show rooms from properties they manage
        if (Auth::check() && Auth::user()->role === 'partner') {
            $query->where('properties.user_id', Auth::id());
        }

        // Filter by room_number (partial match)
        if ($request->filled('room_number')) {
            $query->where('room_number', 'like', '%' . $request->room_number . '%');
        }

        if ($request->filled('property_id')) {
            $query->where('property_id', $request->property_id);
        }

        // Filter by title (partial match)
        if ($request->filled('title')) {
            $query->where('title', 'like', '%' . $request->title . '%');
        }

        // Filter by room_type
        if ($request->filled('room_type')) {
            $query->where('room_type', $request->room_type);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $sortField = $request->input('sort_field');
        $sortDirection = $request->input('sort_direction');

        if (
            $sortField
            && in_array($sortField, [
                'id',
                'room_number',
                'title',
                'property_name',
                'room_type',
                'status',
                'area',
                'people'
            ])
            && in_array($sortDirection, ['asc', 'desc'])
        ) {
            if ($sortField === 'property_name') {
                $query->orderBy('properties.name', $sortDirection);
            } else {
                $query->orderBy('rooms.' . $sortField, $sortDirection);
            }
        } else {
            $query->orderBy('rooms.id', 'desc');
        }
        // Default sorting
        $perPage = $request->input('per_page', config('const.DEFAULT_PER_PAGE'));
        $page = $request->input('page', config('const.DEFAULT_PAGE'));

        $paginator = $query->paginate($perPage, ['*'], 'page', $page);
        return $paginator;
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
            ->select(
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
                'rooms.description',
                'rooms.created_at',
                'rooms.updated_at',
                'properties.name as property_name'
            )->where('rooms.id', $id);

        // Filter for partner: only show rooms from properties they manage
        if (Auth::check() && Auth::user()->role === 'partner') {
            $query->where('properties.user_id', Auth::id());
        }
        $result = $query->first();
        return $result;
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
     * Get latest rooms
     *
     * @param \Illuminate\Http\Request $request
     * @return object | null
     */
    public function getLatestRooms(Request $request): object | null
    {
        // First get rooms with row numbers per province
        $roomsQuery = $this->model
            ->join('properties as b', 'rooms.property_id', '=', 'b.id')
            ->join('users as u', 'b.user_id', '=', 'u.id')
            ->join('provinces as p', 'b.province_id', '=', 'p.id')
            ->leftJoin('property_types as pt', 'b.property_type_id', '=', 'pt.id')
            ->leftJoin('room_amenities as ra', 'rooms.id', '=', 'ra.room_id')
            ->leftJoin('amenities as a', 'ra.amenity_id', '=', 'a.id')
            ->leftJoin('room_prices as rp', 'rooms.id', '=', 'rp.room_id')
            ->leftJoin('room_images as ri', 'rooms.id', '=', 'ri.room_id')
            ->where('rooms.status', RoomStatus::PUBLIC)
            ->select(
                'rooms.id',
                'rooms.title',
                'rooms.room_type',
                'rooms.people',
                'rooms.description',
                'rooms.area',
                'b.id as property_id',
                'b.name as property_name',
                'p.name as province_name',
                'p.name_en as province_name_en',
                'ri.image_url as room_image',
                'b.address_detail as property_address',
                'pt.name as property_type_name',
                'b.property_type_id',
                DB::raw('GROUP_CONCAT(DISTINCT a.name) as amenities'),
                DB::raw('ROUND(CASE
                    WHEN MIN(CASE WHEN rp.unit = "day" THEN rp.price END) IS NOT NULL
                        AND MIN(CASE WHEN rp.unit = "month" THEN rp.price / 30 END) IS NOT NULL
                    THEN (CASE WHEN MIN(CASE WHEN rp.unit = "day" THEN rp.price END)
                            < MIN(CASE WHEN rp.unit = "month" THEN rp.price / 30 END)
                        THEN MIN(CASE WHEN rp.unit = "day" THEN rp.price END)
                        ELSE MIN(CASE WHEN rp.unit = "month" THEN rp.price / 30 END) END)
                    WHEN MIN(CASE WHEN rp.unit = "day" THEN rp.price END) IS NOT NULL
                    THEN MIN(CASE WHEN rp.unit = "day" THEN rp.price END)
                    ELSE MIN(CASE WHEN rp.unit = "month" THEN rp.price / 30 END)
                END, 0) as cheapest_daily_price'),
                DB::raw('MIN(CASE WHEN rp.unit = "month" THEN rp.price END) as cheapest_monthly_price'),
                DB::raw($this->allPricesExpression()),
                DB::raw('(SELECT COUNT(*) FROM reviews WHERE reviews.room_id = rooms.id) as reviews_count'),
                DB::raw('(SELECT ROUND(AVG(rating), 1) FROM reviews ' .
                    'WHERE reviews.room_id = rooms.id) as reviews_avg_rating'),
            )
            ->groupBy(
                'rooms.id',
                'rooms.title',
                'rooms.room_type',
                'rooms.people',
                'rooms.description',
                'rooms.area',
                'b.id',
                'b.name',
                'p.name',
                'p.name_en',
                'p.id',
                'ri.image_url',
                'b.address_detail',
                'pt.name',
                'b.property_type_id',
                'rooms.updated_at'
            );

        // filter by updated_at
        if ($request->filled('updated_at')) {
            $roomsQuery->orderBy('rooms.updated_at', 'desc');
        }
        $limit = $request->filled('limit') ? $request->limit : config('const.DEFAULT_PER_PAGE');
        $roomsQuery->limit($limit);

        return $roomsQuery->get();
    }

    /**
     * Get room list with filters
     *
     * @param \Illuminate\Http\Request $request
     * @return object|null
     */
    public function getRoomList(Request $request): object | null
    {
        $limitPerProvince = $request->input('limit', config('const.DEFAULT_PER_PAGE'));
        $provinceIds = $request->input('province_ids', []);

        // First get rooms with row numbers per province
        $roomsQuery = $this->model
            ->join('properties as b', 'rooms.property_id', '=', 'b.id')
            ->join('users as u', 'b.user_id', '=', 'u.id')
            ->join('provinces as p', 'b.province_id', '=', 'p.id')
            ->leftJoin('property_types as pt', 'b.property_type_id', '=', 'pt.id')
            ->leftJoin('room_amenities as ra', 'rooms.id', '=', 'ra.room_id')
            ->leftJoin('amenities as a', 'ra.amenity_id', '=', 'a.id')
            ->leftJoin('room_prices as rp', 'rooms.id', '=', 'rp.room_id')
            ->leftJoin('room_images as ri', 'rooms.id', '=', 'ri.room_id')
            ->where('rooms.status', RoomStatus::PUBLIC)
            ->select(
                'rooms.id',
                'rooms.title',
                'rooms.room_type',
                'rooms.people',
                'rooms.description',
                'rooms.area',
                'p.id as province_id',
                'p.name as province_name',
                'p.name_en as province_name_en',
                'ri.image_url as room_image',
                'b.address_detail as property_address',
                'pt.name as property_type_name',
                'b.property_type_id',
                DB::raw('GROUP_CONCAT(DISTINCT a.name) as amenities'),
                DB::raw('ROUND(CASE
                    WHEN MIN(CASE WHEN rp.unit = "day" THEN rp.price END) IS NOT NULL
                        AND MIN(CASE WHEN rp.unit = "month" THEN rp.price / 30 END) IS NOT NULL
                    THEN (CASE WHEN MIN(CASE WHEN rp.unit = "day" THEN rp.price END)
                            < MIN(CASE WHEN rp.unit = "month" THEN rp.price / 30 END)
                        THEN MIN(CASE WHEN rp.unit = "day" THEN rp.price END)
                        ELSE MIN(CASE WHEN rp.unit = "month" THEN rp.price / 30 END) END)
                    WHEN MIN(CASE WHEN rp.unit = "day" THEN rp.price END) IS NOT NULL
                    THEN MIN(CASE WHEN rp.unit = "day" THEN rp.price END)
                    ELSE MIN(CASE WHEN rp.unit = "month" THEN rp.price / 30 END)
                END, 0) as cheapest_daily_price'),
                DB::raw('MIN(CASE WHEN rp.unit = "month" THEN rp.price END) as cheapest_monthly_price'),
                DB::raw($this->allPricesExpression()),
                DB::raw('(SELECT COUNT(*) FROM reviews WHERE reviews.room_id = rooms.id) as reviews_count'),
                DB::raw('(SELECT ROUND(AVG(rating), 1) FROM reviews ' .
                    'WHERE reviews.room_id = rooms.id) as reviews_avg_rating'),
                // Add row number for each province (ordered by updated_at DESC)
                DB::raw('ROW_NUMBER() OVER (PARTITION BY p.id ORDER BY rooms.updated_at DESC) as province_row_num')
            )
            ->groupBy(
                'rooms.id',
                'rooms.title',
                'rooms.room_type',
                'rooms.people',
                'rooms.description',
                'rooms.area',
                'p.name',
                'p.name_en',
                'p.id',
                'ri.image_url',
                'b.address_detail',
                'pt.name',
                'b.property_type_id',
                'rooms.updated_at'
            );

        if (!empty($provinceIds) && is_array($provinceIds)) {
            $roomsQuery->whereIn('p.id', $provinceIds);
        }

        if ($request->filled('partner_id')) {
            $roomsQuery->where('u.id', $request->partner_id);
        }

        // Wrap in subquery to filter by row number
        $rooms = DB::table(DB::raw("({$roomsQuery->toSql()}) as ranked_rooms"))
            ->mergeBindings($roomsQuery->getQuery())
            ->where('province_row_num', '<=', $limitPerProvince)
            ->select('*')
            ->get();

        return $rooms;
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
            $join->on('rooms.id', '=', 'ri.room_id')
                ->where('ri.sort', 1);
        })
        ->join('properties as b', 'rooms.property_id', '=', 'b.id')
        ->join('users as u', 'b.user_id', '=', 'u.id')
        ->leftJoin('partner_info as pi', 'pi.user_id', '=', 'u.id')
        ->join('provinces as p', 'b.province_id', '=', 'p.id')
        ->leftJoin('property_types as pt', 'b.property_type_id', '=', 'pt.id')
        ->leftjoin('room_services as rs', 'rooms.id', '=', 'rs.room_id')
        ->leftJoin('services as s', 'rs.service_id', '=', 's.id')
        ->leftJoin('utility_fees as uf', 'rooms.id', '=', 'uf.room_id')
        ->select(
            'rooms.id',
            'rooms.title',
            'rooms.room_type',
            'rooms.people',
            'rooms.description',
            'rooms.area',
            'ri.image_url as room_image',
            'b.address_detail as property_address',
            'p.name as province_name',
            'pt.name as property_type_name',
            'b.property_type_id',
            'u.id as partner_id',
            'u.name as partner_name',
            'u.email as partner_email',
            'pi.company_name as partner_company_name',
            'pi.phone as partner_phone',
            'pi.address as partner_address',
            'pi.description as partner_description',
            DB::raw(
                'CONCAT(\'[\', GROUP_CONCAT(DISTINCT CONCAT(' .
                    '\'{"id":\', s.id, \',"name":"\', REPLACE(s.name, \'"\', \'\\"\'), \'","price":\', s.price, \'}\'' .
                    ')), \']\') as services'
            ),
            DB::raw('GROUP_CONCAT(DISTINCT a.name) as amenities'),
            DB::raw('ROUND(CASE
                    WHEN MIN(CASE WHEN rp.unit = "day" THEN rp.price END) IS NOT NULL
                        AND MIN(CASE WHEN rp.unit = "month" THEN rp.price / 30 END) IS NOT NULL
                    THEN (CASE WHEN MIN(CASE WHEN rp.unit = "day" THEN rp.price END)
                            < MIN(CASE WHEN rp.unit = "month" THEN rp.price / 30 END)
                        THEN MIN(CASE WHEN rp.unit = "day" THEN rp.price END)
                        ELSE MIN(CASE WHEN rp.unit = "month" THEN rp.price / 30 END) END)
                    WHEN MIN(CASE WHEN rp.unit = "day" THEN rp.price END) IS NOT NULL
                    THEN MIN(CASE WHEN rp.unit = "day" THEN rp.price END)
                    ELSE MIN(CASE WHEN rp.unit = "month" THEN rp.price / 30 END)
                END, 0) as cheapest_daily_price'),
            DB::raw($this->allPricesExpression()),
            DB::raw(
                'IFNULL(CONCAT(\'[\', GROUP_CONCAT(DISTINCT CONCAT(' .
                '\'{"type":"\', uf.fee_type, \'", "method":"\', uf.calc_method, ' .
                '\'", "price":\', uf.unit_price, \', "included":\', uf.is_included, \'}\'' .
                ')), \']\'), \'[]\') as utility_fees'
            ),
            DB::raw('(SELECT COUNT(*) FROM reviews WHERE reviews.room_id = rooms.id) as reviews_count'),
            DB::raw('(SELECT ROUND(AVG(rating), 1) FROM reviews ' .
                'WHERE reviews.room_id = rooms.id) as reviews_avg_rating'),
        )->groupBy(
            'rooms.id',
            'rooms.title',
            'rooms.room_type',
            'rooms.people',
            'rooms.description',
            'rooms.area',
            'ri.image_url',
            'b.address_detail',
            'rooms.updated_at',
            'p.name',
            'pt.name',
            'b.property_type_id',
            'u.id',
            'u.name',
            'u.email',
            'pi.company_name',
            'pi.phone',
            'pi.address',
            'pi.description'
        )->where('rooms.id', $id)
            ->first();
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
        $query = $this->model->with([
            'amenities:id,name',
            'services:id,name',
            'prices:id,room_id,price_package_id,unit,price,deposit_amount,minimum_stay',
            'utilityFees',
            'images' => function ($q) {
                $q->where('sort', 1)->select('id', 'room_id', 'image_url');
            }
        ])
            ->join('properties', 'rooms.property_id', '=', 'properties.id')
            ->select(
                'rooms.id',
                'rooms.property_id',
                'rooms.room_number',
                'rooms.title',
                'properties.name as property_name',
                'rooms.room_type',
                'rooms.status',
                'rooms.area',
                'rooms.people',
                DB::raw('(SELECT COUNT(*) FROM reviews WHERE reviews.room_id = rooms.id) as reviews_count'),
                DB::raw('(SELECT ROUND(AVG(rating), 1) FROM reviews ' .
                    'WHERE reviews.room_id = rooms.id) as reviews_avg_rating')
            )
            ->where('properties.user_id', $partnerId);

        $query->orderBy('rooms.id', 'desc');

        if ($request->filled('room_number')) {
            $query->where('room_number', 'like', '%' . $request->room_number . '%');
        }

        if ($request->filled('property_ids') && is_array($request->input('property_ids'))) {
            $ids = array_values(array_unique(array_filter(array_map('intval', $request->input('property_ids')))));
            if ($ids !== []) {
                $query->whereIn('rooms.property_id', $ids);
            }
        } elseif ($request->filled('property_id')) {
            $query->where('property_id', $request->property_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $perPage = $request->input('per_page', config('const.DEFAULT_PER_PAGE'));
        $page = $request->input('page', config('const.DEFAULT_PAGE'));

        return $query->paginate($perPage, ['*'], 'page', $page);
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
            ->select(
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
                'rooms.description',
                'rooms.created_at',
                'rooms.updated_at',
                'properties.name as property_name',
                DB::raw('(SELECT COUNT(*) FROM reviews WHERE reviews.room_id = rooms.id) as reviews_count'),
                DB::raw('(SELECT ROUND(AVG(rating), 1) FROM reviews ' .
                    'WHERE reviews.room_id = rooms.id) as reviews_avg_rating')
            )
            ->where('rooms.id', $id)
            ->where('properties.user_id', $partnerId)
            ->first();
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
    public function getEmptyRoomsForPartner(int $partnerId): int
    {
        $today = now()->toDateString();
        return $this->model->join('properties', 'rooms.property_id', '=', 'properties.id')
            ->where('properties.user_id', $partnerId)
            ->where('rooms.status', RoomStatus::PUBLIC)
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
            ->select(
                'rooms.id',
                'rooms.room_number',
                'rooms.title',
                'rooms.floor_number',
                'rooms.status as room_visibility', // is public or private
                DB::raw("CASE 
                    WHEN rooms.status = 0 THEN 'hidden'
                    WHEN EXISTS (
                        SELECT 1 FROM bookings 
                        WHERE bookings.room_id = rooms.id 
                        AND bookings.status = " . $confirmedStatus . "
                        AND bookings.start_date <= '$today'
                        AND bookings.end_date >= '$today'
                    ) THEN 'occupied'
                    WHEN EXISTS (
                        SELECT 1 FROM room_maintenances 
                        WHERE room_maintenances.room_id = rooms.id 
                        AND room_maintenances.status IN ('planned', 'in_progress')
                        AND DATE(room_maintenances.start_time) <= '$today'
                        AND (room_maintenances.end_time IS NULL OR DATE(room_maintenances.end_time) >= '$today')
                    ) THEN 'maintenance'
                    ELSE 'vacant'
                END as occupancy_status"),
                // Subqueries for customer info (only when occupied)
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
            )
            ->get();
    }
}
