declare(strict_types=1);

namespace App\Repositories\CouponRepository;

use App\Repositories\RepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * Interface CouponRepositoryInterface
 *
 * @package App\Repositories\CouponRepository
 */
interface CouponRepositoryInterface extends RepositoryInterface
{
    /**
     * Get paginated coupons with filters
     *
     * @param array $filters
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function paginateWithFilters(array $filters): LengthAwarePaginator;
}
