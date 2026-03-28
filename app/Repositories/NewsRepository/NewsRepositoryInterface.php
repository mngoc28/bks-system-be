<?php

namespace App\Repositories\NewsRepository;

use App\Repositories\RepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

interface NewsRepositoryInterface extends RepositoryInterface
{
    /**
     * Get all news
     * @param Request $request
     * @retuen LengthAwarePaginator
     */
    public function getAllNews(Request $request): LengthAwarePaginator;

    /**
     * Get latest news for homepage
     * @param int $limit
     * @return array
     */
    public function getLatestNews(int $limit): array;

    /**
     * Get all news for user
     * @param Request $request
     * @return LengthAwarePaginator
     */
    public function getAllNewsForUser(Request $request): LengthAwarePaginator;
}
