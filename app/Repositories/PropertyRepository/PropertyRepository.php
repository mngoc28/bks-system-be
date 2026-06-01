<?php

declare(strict_types=1);

namespace App\Repositories\PropertyRepository;

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

        if ($request->filled('name')) {
            $query->whereRaw('LOWER(properties.name) LIKE ?', ['%' . strtolower($request->name) . '%']);
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

        $propertyReviewAgg = DB::table('reviews')
            ->join('rooms', 'reviews.room_id', '=', 'rooms.id')
            ->select([
                'rooms.property_id',
                DB::raw('COUNT(reviews.id) as property_reviews_count'),
                DB::raw('ROUND(AVG(reviews.rating), 1) as property_reviews_avg_rating'),
            ])
            ->groupBy('rooms.property_id');

        $selectColumns = [
            'properties.*',
            'users.name as user_name',
            'provinces.name as province_name',
            'wards.name as ward_name',
            DB::raw('COALESCE(property_review_agg.property_reviews_count, 0) as reviews_count'),
            DB::raw('property_review_agg.property_reviews_avg_rating as reviews_avg_rating'),
        ];

        if ($includeCover) {
            $selectColumns[] = DB::raw(
                '(SELECT pi.image_url FROM property_images pi ' .
                'WHERE pi.property_id = properties.id ' .
                'ORDER BY pi.sort ASC, pi.id ASC LIMIT 1) as cover_image_url'
            );
        }

        $query = $this->model
            ->select($selectColumns)
            ->leftJoin('users', 'properties.user_id', '=', 'users.id')
            ->leftJoin('provinces', 'properties.province_id', '=', 'provinces.id')
            ->leftJoin('wards', 'properties.ward_id', '=', 'wards.id')
            ->leftJoinSub($propertyReviewAgg, 'property_review_agg', function ($join): void {
                $join->on('property_review_agg.property_id', '=', 'properties.id');
            })
            ->where('properties.user_id', $partnerId)
            ->withCount('rooms');

        $this->applyPartnerRoomsEagerLoad($query, $request, $roomsLoadMode);

        if ($request->filled('id')) {
            $query->where('properties.id', (int) $request->id);
        }

        if ($request->filled('name')) {
            $query->whereRaw('LOWER(properties.name) LIKE ?', ['%' . strtolower($request->name) . '%']);
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

        $perPage = (int) ($request->filled('per_page') ? $request->per_page : config('const.DEFAULT_PER_PAGE'));
        $page = (int) ($request->filled('page') ? $request->page : config('const.DEFAULT_PAGE'));

        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    public function getPropertyByIdForPartner(int $id, int $partnerId): object|null
    {
        return $this->model->with(['province', 'ward', 'user'])
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
}
