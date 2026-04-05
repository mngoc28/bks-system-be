declare(strict_types=1);

namespace App\Repositories\ServiceRepository;

use App\Repositories\RepositoryInterface;

/**
 * Interface ServiceRepositoryInterface
 *
 * @package App\Repositories\ServiceRepository
 */
interface ServiceRepositoryInterface extends RepositoryInterface
{
    /**
     * Get all services or search services with pagination
     *
     * @param \Illuminate\Http\Request|mixed $request
     * @return mixed
     */
    public function getAllOrSearch($request): mixed;

    /**
     * Get all services without pagination
     *
     * @return \Illuminate\Support\Collection
     */
    public function getAllServices(): \Illuminate\Support\Collection;
}
