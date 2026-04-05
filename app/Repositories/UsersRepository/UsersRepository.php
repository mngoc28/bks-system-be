<?php

declare(strict_types=1);

namespace App\Repositories\UsersRepository;

use App\Models\User;
use App\Repositories\BaseRepository;

/**
 * Class UsersRepository
 *
 * @package App\Repositories\UsersRepository
 */
class UsersRepository extends BaseRepository implements UsersRepositoryInterface
{
    /**
     * Get the model class name
     *
     * @return string
     */
    public function getModel(): string
    {
        return User::class;
    }

    /**
     * Count new users in current month by role
     *
     * @param \Illuminate\Http\Request|mixed $request
     * @param string $role
     * @return int
     */
    public function countNewUserInCurrentMonth($request, $role): int
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
        $endDate   = $request->input('end_date', now()->endOfMonth()->format('Y-m-d'));

        return $this->model
            ->where('role', $role)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();
    }

    /**
     * Get all users with filters and pagination
     *
     * @param \Illuminate\Http\Request|mixed $request
     * @return ?object
     */
    public function getAll($request): ?object
    {
        $query = $this->model->select('id', 'name', 'email', 'phone', 'role', 'status', 'avatar', 'created_at');

        // search multiple fields: name, email
        if ($request->filled('q') && ! empty($request['q'])) {
            $searchTerm = $request['q'];
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', '%' . $searchTerm . '%')
                    ->orWhere('email', 'like', '%' . $searchTerm . '%');
            });
        }

        if ($request->filled('email')) {
            $query->where('email', 'like', '%' . $request['email'] . '%');
        }

        if ($request->filled('role')) {
            $query->where('role', $request['role']);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('phone')) {
            $query->where('phone', 'like', '%' . $request['phone'] . '%');
        }

        if ($request->filled('created_at_from')) {
            $query->whereDate('created_at', '>=', $request['created_at_from']);
        }

        if ($request->filled('created_at_to')) {
            $query->whereDate('created_at', '<=', $request['created_at_to']);
        }

        $sortField     = $request->input('sort_field');
        $sortDirection = $request->input('sort_direction');

        if (
            $sortField
            && in_array($sortField, [
            'id',
            'name',
            'email',
            'phone',
            'role',
            'status',
            'created_at'
            ])
            && in_array($sortDirection, ['asc', 'desc'])
        ) {
            $query->orderBy('users.' .$sortField, $sortDirection);
        } else {
            $query->orderBy('users.id', 'desc');
        }

        $page    = (int) ($request->input('page', config('const.DEFAULT_PAGE', 1)));
        $perPage = (int) ($request->input('per_page', config('const.DEFAULT_PER_PAGE', 10)));

        $paginator = $query->paginate($perPage, ['*'], 'page', $page);
        return $paginator;
    }
}
