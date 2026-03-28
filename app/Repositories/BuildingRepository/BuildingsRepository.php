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

final class BuildingsRepository extends BaseRepository implements
    BuildingsRepositoryInterface
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

        if ($request->filled("building_type")) {
            $query->where("buildings.building_type", $request->building_type);
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
            $query->orderBy('buildings.id', 'asc');
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
        return $this->model
            ->select("building_type")
            ->distinct()
            ->pluck("building_type")
            ->map(
                fn($id) => [
                    "value" => $id,
                    "label" => BuildingType::labels()[$id] ?? "Other",
                ]
            );
    }

    /**
     * Get building by id
     * @param int $id
     * @return object | null
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
}
