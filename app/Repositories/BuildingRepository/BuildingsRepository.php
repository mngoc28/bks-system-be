<?php

declare(strict_types=1);

namespace App\Repositories\BuildingRepository;

use App\Enums\BuildingType;
use App\Enums\UserType;
use App\Models\Building;
use App\Repositories\BaseRepository;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Class BuildingsRepository
 *
 * @package App\Repositories\BuildingRepository
 */
final class BuildingsRepository extends BaseRepository implements BuildingsRepositoryInterface
{
    /**
     * Get the model class name
     *
     * @return string
     */
    public function getModel(): string
    {
        return Building::class;
    }

    /**
     * Get all buildings or search by criteria with pagination
     *
     * @param Request $request
     * @param array $sort
     * @return LengthAwarePaginator
     */
    public function getAllOrSearchBuildings(Request $request, array $sort = []): LengthAwarePaginator
    {
        $user = Auth::user();
        $query = $this->model
            ->select([
                'buildings.*',
                'users.name as user_name',
                'provinces.name as province_name',
                'wards.name as ward_name',
                DB::raw(
                    '(SELECT bi.image_url FROM building_images bi ' .
                    'WHERE bi.building_id = buildings.id ' .
                    'ORDER BY bi.sort ASC, bi.id ASC LIMIT 1) as cover_image_url'
                ),
            ])
            ->leftJoin('users', 'buildings.user_id', '=', 'users.id')
            ->leftJoin('provinces', 'buildings.province_id', '=', 'provinces.id')
            ->leftJoin('wards', 'buildings.ward_id', '=', 'wards.id');

        // WHERE FILTER
        if ($user && $user->role !== UserType::ADMIN) {
            $query->where('buildings.user_id', $user->id);
        }

        if ($request->filled("name")) {
            $query->whereRaw("LOWER(buildings.name) LIKE ?", ["%" . strtolower($request->name) . "%"]);
        }

        if ($request->filled("ward_name")) {
            $query->whereRaw("LOWER(wards.name) LIKE ?", ["%" . strtolower($request->ward_name) . "%"]);
        }

        if ($request->filled("province_name")) {
            $query->whereRaw("LOWER(provinces.name) LIKE ?", ["%" . strtolower($request->province_name) . "%"]);
        }

        if ($request->filled("year_built")) {
            $query->where("buildings.year_built", $request->year_built);
        }

        if ($request->filled("property_type_id")) {
            $query->where("buildings.property_type_id", $request->property_type_id);
        }

        if ($request->filled("rent_category")) {
            $query->where("buildings.rent_category", $request->rent_category);
        }

        if ($request->filled("area_min")) {
            $query->where("buildings.area", ">=", $request->area_min);
        }

        if ($request->filled("area_max")) {
            $query->where("buildings.area", "<=", $request->area_max);
        }

        // SORT
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
                        $query->orderBy("buildings.$filed", $order);
                }
            }
        } else {
            $query->orderBy('buildings.id', 'desc');
        }

        $perPage = (int) ($request->filled("per_page") ?
            $request->per_page : config("const.DEFAULT_PER_PAGE"));
        $page = (int) ($request->filled("page") ? $request->page : config("const.DEFAULT_PAGE"));

        return $query->paginate($perPage, ["*"], "page", $page);
    }

    /**
     * Get all buildings types
     *
     * @return Collection
     */
    public function getAllBuildingsTypes(): Collection
    {
        return \App\Models\PropertyType::where('is_active', true)
            ->select('id as value', 'name as label')
            ->get();
    }

    /**
     * Get building details by ID
     *
     * @param int $id
     * @return object|null
     */
    public function getBuildingById(int $id): object | null
    {
        $user = Auth::user();

        $query = $this->model->with(['province', 'ward', 'user'])
            ->where('id', $id);

        if ($user->role !== UserType::ADMIN) {
            $query->where('user_id', $user->id);
        }

        return $query->first() ?? null;
    }

    /**
     * Get all buildings without pagination
     * @return Collection
     */
    public function getAllBuildingNames(): Collection
    {
        $user = Auth::user();
        $query = $this->model->select('id', 'name');

        if ($user && $user->role !== UserType::ADMIN) {
            $query->where("user_id", $user->id);
        }
        return $query->get();
    }

    // =========================================================================
    // PARTNER METHODS
    // =========================================================================

    /**
     * Get buildings for a specific partner
     *
     * @param int $partnerId
     * @param Request $request
     * @param array $sort
     * @return LengthAwarePaginator
     */
    public function getBuildingsForPartner(int $partnerId, Request $request, array $sort = []): LengthAwarePaginator
    {
        $query = $this->model
            ->withCount('rooms')
            ->select([
                'buildings.*',
                'users.name as user_name',
                'provinces.name as province_name',
                'wards.name as ward_name',
                DB::raw(
                    '(SELECT bi.image_url FROM building_images bi ' .
                    'WHERE bi.building_id = buildings.id ' .
                    'ORDER BY bi.sort ASC, bi.id ASC LIMIT 1) as cover_image_url'
                ),
            ])
            ->leftJoin('users', 'buildings.user_id', '=', 'users.id')
            ->leftJoin('provinces', 'buildings.province_id', '=', 'provinces.id')
            ->leftJoin('wards', 'buildings.ward_id', '=', 'wards.id')
            ->where('buildings.user_id', $partnerId);

        if ($request->filled("name")) {
            $query->whereRaw("LOWER(buildings.name) LIKE ?", ["%" . strtolower($request->name) . "%"]);
        }

        if ($request->filled("ward_name")) {
            $query->whereRaw("LOWER(wards.name) LIKE ?", ["%" . strtolower($request->ward_name) . "%"]);
        }

        if ($request->filled("province_name")) {
            $query->whereRaw("LOWER(provinces.name) LIKE ?", ["%" . strtolower($request->province_name) . "%"]);
        }

        if ($request->filled("year_built")) {
            $query->where("buildings.year_built", $request->year_built);
        }

        if ($request->filled("property_type_id")) {
            $query->where("buildings.property_type_id", $request->property_type_id);
        }

        if ($request->filled("rent_category")) {
            $query->where("buildings.rent_category", $request->rent_category);
        }

        if ($request->filled("area_min")) {
            $query->where("buildings.area", ">=", $request->area_min);
        }

        if ($request->filled("area_max")) {
            $query->where("buildings.area", "<=", $request->area_max);
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
                        $query->orderBy("buildings.$filed", $order);
                }
            }
        } else {
            $query->orderBy('buildings.id', 'desc');
        }

        $perPage = (int) ($request->filled("per_page") ? $request->per_page : config("const.DEFAULT_PER_PAGE"));
        $page = (int) ($request->filled("page") ? $request->page : config("const.DEFAULT_PAGE"));

        return $query->paginate($perPage, ["*"], "page", $page);
    }

    /**
     * Get building by ID for a specific partner
     *
     * @param int $id
     * @param int $partnerId
     * @return object|null
     */
    public function getBuildingByIdForPartner(int $id, int $partnerId): object|null
    {
        return $this->model->with(['province', 'ward', 'user'])
            ->where('id', $id)
            ->where('user_id', $partnerId)
            ->first();
    }

    /**
     * Get all building names for a specific partner
     *
     * @param int $partnerId
     * @return Collection
     */
    public function getBuildingNamesForPartner(int $partnerId): Collection
    {
        return $this->model->select('id', 'name')
            ->where("user_id", $partnerId)
            ->get();
    }
}
