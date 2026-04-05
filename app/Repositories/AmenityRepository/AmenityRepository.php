declare(strict_types=1);

namespace App\Repositories\AmenityRepository;

use App\Models\Amenity;
use App\Repositories\BaseRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Class AmenityRepository
 *
 * @package App\Repositories\AmenityRepository
 */
class AmenityRepository extends BaseRepository implements AmenityRepositoryInterface
{
    /**
     * Get the model class name
     *
     * @return string
     */
    public function getModel(): string
    {
        return Amenity::class;
    }

    /**
     * Get all amenities or search by criteria with pagination
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getAllOrSearch(Request $request): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        if ($request->filled('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        // Sorting logic
        $sortField = $request->input('sort_field');
        $sortDirection = $request->input('sort_direction');

        if (
            $sortField
            && in_array($sortField, ['id', 'name', 'created_at', 'updated_at'])
            && in_array($sortDirection, ['asc', 'desc'])
        ) {
            $query->orderBy($sortField, $sortDirection);
        } else {
            // Default sorting
            $query->orderBy('id', 'desc');
        }

        // Pagination parameters
        $perPage = (int) $request->input('per_page') ?? config('const.DEFAULT_PER_PAGE');
        $page    = (int) $request->input('page') ?? config('const.DEFAULT_PAGE');

        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Get all amenities without pagination
     *
     * @return \Illuminate\Support\Collection
     */
    public function getAllAmenities(): \Illuminate\Support\Collection
    {
        return $this->model->select('id', 'name')->get();
    }
}
