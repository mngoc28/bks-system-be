<?php

namespace App\Repositories\UsersRepository;

use App\Repositories\RepositoryInterface;

interface UsersRepositoryInterface extends RepositoryInterface
{
    /**
     * Summary of getAll
     * @param \Illuminate\Http\Request $request
     * @return ?object|null
     */
    public function getAll($request): ?object;

    /**
     * Number of users by month
     * @param \Illuminate\Http\Request $request
     * @param string $role
     * @return int
     */
    public function countNewUserInCurrentMonth($request, $role): int;
}
