declare(strict_types=1);

namespace App\Repositories\ProvincesRepository;

use App\Models\Province;
use App\Repositories\BaseRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * Class ProvincesRepository
 *
 * @package App\Repositories\ProvincesRepository
 */
class ProvincesRepository extends BaseRepository implements ProvincesRepositoryInterface
{
    /**
     * Get the model class name
     *
     * @return string
     */
    public function getModel(): string
    {
        return Province::class;
    }
    /**
     * Get paginated list of provinces
     *
     * @param \Illuminate\Http\Request|mixed $request
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function listProvinces($request): LengthAwarePaginator
    {
        $countWard = DB::table('wards')
            ->select('province_id', DB::raw('COUNT(*) as ward_count'))
            ->groupBy('province_id');

        $countRoom = DB::table('buildings')
            ->select('province_id', DB::raw('COUNT(*) as room_count'))
            ->leftJoin('rooms as rm', 'rm.building_id', '=', 'buildings.id')
            ->groupBy('province_id');

        $query = $this->model->select(
            'provinces.id',
            'provinces.name',
            'provinces.name_en',
            DB::raw('COALESCE(wc.ward_count, 0) as ward_count'),
            DB::raw('COALESCE(rc.room_count, 0) as room_count')
        )

            ->leftJoinSub($countWard, "wc", function ($join) {
                $join->on('provinces.id', '=', 'wc.province_id');
            })
            ->leftJoinSub($countRoom, "rc", function ($join) {
                $join->on('provinces.id', '=', 'rc.province_id');
            });

        if ($request->filled('name')) {
            $query->where('name', 'LIKE', '%' . $request->name . '%');
        }

        // Sorting logic
        $sortField = $request->input('sort_field');
        $sortDirection = $request->input('sort_direction');

        if (
            $sortField
            && in_array($sortField, ['id', 'name'])
            && in_array($sortDirection, ['asc', 'desc'])
        ) {
            $query->orderBy($sortField, $sortDirection);
        } else {
            $query->orderBy('id', 'desc');
        }
        // Pagination parameters
        $perPage = (int) $request->input('per_page', config('const.DEFAULT_PER_PAGE'));
        $page    = (int) $request->input('page', config('const.DEFAULT_PAGE'));

        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Get province details by ID
     *
     * @param int $id
     * @return object
     */
    public function detailProvince(int $id): object
    {
        $province = DB::table('provinces')
            ->where('id', $id)
            ->first();

        $wards = DB::table('wards')
            ->where('province_id', $id)
            ->select('id', 'name', 'province_id')
            ->get();

        $wardCount = $wards->count();

        $rooms = DB::table('rooms')
            ->join('buildings', 'rooms.building_id', '=', 'buildings.id')
            ->where('buildings.province_id', $id)
            ->select(
                'rooms.id',
                'rooms.title',
                'rooms.room_number',
            )
            ->get();

        $roomCount = $rooms->count();

        return (object)[
            'province' => $province,
            'ward_count' => $wardCount,
            'room_count' => $roomCount,
            'wards' => $wards,
            'rooms' => $rooms
        ];
    }

    /**
     * Get all province types
     *
     * @return object
     */
    public function getAllProvincesTypes(): object
    {
        return $this->model->select('id', 'name', 'name_en')->get();
    }

    // ====== The functions below are APIs for the end user ======
    /**
     * Get all provinces without pagination
     *
     * @return array
     */
    public function getAllProvinces(): array
    {
        return $this->model
            ->with('wards:id,name,province_id')
            ->select('provinces.id', 'provinces.name', 'provinces.name_en')
            ->get()
            ->toArray();
    }
}
