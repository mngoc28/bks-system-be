declare(strict_types=1);

namespace App\Repositories\PropertyTypeRepository;

use App\Models\PropertyType;
use App\Repositories\BaseRepository;

/**
 * Class PropertyTypeRepository
 *
 * @package App\Repositories\PropertyTypeRepository
 */
class PropertyTypeRepository extends BaseRepository implements PropertyTypeRepositoryInterface
{
    /**
     * Get the model class name
     *
     * @return string
     */
    public function getModel(): string
    {
        return PropertyType::class;
    }

    /**
     * Get property types list with optional pagination
     *
     * @param array $filters
     * @return mixed
     */
    public function getList(array $filters = [])
    {
        $query = $this->model->newQuery()->orderByDesc('created_at');

        if (! empty($filters['pagination'])) {
            return $query->paginate((int) $filters['pagination']);
        }

        return $query->get();
    }
}
