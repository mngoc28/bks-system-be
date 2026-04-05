declare(strict_types=1);

namespace App\Repositories\RoomPriceRepository;

use App\Models\RoomPrice;
use App\Repositories\BaseRepository;

/**
 * Class RoomPriceRepository
 *
 * @package App\Repositories\RoomPriceRepository
 */
class RoomPriceRepository extends BaseRepository implements RoomPriceRepositoryInterface
{
    /**
     * Get the model class name
     *
     * @return string
     */
    public function getModel(): string
    {
        return RoomPrice::class;
    }
}
