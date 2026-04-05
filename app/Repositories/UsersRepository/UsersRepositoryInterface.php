declare(strict_types=1);

namespace App\Repositories\UsersRepository;

use App\Repositories\RepositoryInterface;

/**
 * Interface UsersRepositoryInterface
 *
 * @package App\Repositories\UsersRepository
 */
interface UsersRepositoryInterface extends RepositoryInterface
{
    /**
     * Get all users with filters and pagination
     *
     * @param \Illuminate\Http\Request|mixed $request
     * @return ?object
     */
    public function getAll($request): ?object;

    /**
     * Count new users in current month by role
     *
     * @param \Illuminate\Http\Request|mixed $request
     * @param string $role
     * @return int
     */
    public function countNewUserInCurrentMonth($request, $role): int;
}
