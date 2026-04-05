declare(strict_types=1);

namespace App\Repositories\PropertyTypeRepository;

use App\Repositories\RepositoryInterface;

/**
 * Interface PropertyTypeRepositoryInterface
 *
 * @package App\Repositories\PropertyTypeRepository
 */
interface PropertyTypeRepositoryInterface extends RepositoryInterface
{
    /**
     * Get property types list with optional pagination
     *
     * @param array $filters
     * @return mixed
     */
    public function getList(array $filters = []);
}
