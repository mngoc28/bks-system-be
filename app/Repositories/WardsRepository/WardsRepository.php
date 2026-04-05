declare(strict_types=1);

namespace App\Repositories\WardsRepository;

use App\Models\Ward;
use App\Repositories\BaseRepository;

/**
 * Class WardsRepository
 *
 * @package App\Repositories\WardsRepository
 */
class WardsRepository extends BaseRepository implements WardsRepositoryInterface
{
    /**
     * Get the model class name
     *
     * @return string
     */
    public function getModel(): string
    {
        return Ward::class;
    }

    /**
     * Get wards by province ID
     *
     * @param int $provinceId
     * @return object
     */
    public function getWardsByProvinceId(int $provinceId): object
    {
        return $this->model->where('province_id', $provinceId)->get();
    }
}
