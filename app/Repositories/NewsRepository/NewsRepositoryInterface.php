<?php

declare(strict_types=1);

namespace App\Repositories\NewsRepository;

use App\Repositories\RepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Interface NewsRepositoryInterface
 *
 * @package App\Repositories\NewsRepository
 */
interface NewsRepositoryInterface extends RepositoryInterface
{
    /**
     * Get all news with filters and pagination
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getAllNews(Request $request): LengthAwarePaginator;

    /**
     * Get latest news for homepage
     *
     * @param int $limit
     * @return array
     */
    public function getLatestNews(int $limit): array;

    /**
     * Get all news for user with filters and pagination
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getAllNewsForUser(Request $request): LengthAwarePaginator;
}
