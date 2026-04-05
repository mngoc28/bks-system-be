declare(strict_types=1);

namespace App\Repositories\AmenityRepository;

use App\Repositories\RepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

/**
 * Interface AmenityRepositoryInterface
 *
 * @package App\Repositories\AmenityRepository
 */
interface AmenityRepositoryInterface extends RepositoryInterface
{
    /**
     * Get all amenities or search by criteria with pagination
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getAllOrSearch(Request $request): LengthAwarePaginator;

    /**
     * Get all amenities without pagination
     *
     * @return \Illuminate\Support\Collection
     */
    public function getAllAmenities(): \Illuminate\Support\Collection;
}
