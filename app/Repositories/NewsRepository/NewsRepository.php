<?php

declare(strict_types=1);

namespace App\Repositories\NewsRepository;

use App\Enums\Pagination;
use App\Enums\UserType;
use App\Models\News;
use App\Repositories\BaseRepository;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Enums\Status;
use Illuminate\Support\Facades\Auth;

/**
 * Class NewsRepository
 *
 * @package App\Repositories\NewsRepository
 */
class NewsRepository extends BaseRepository implements NewsRepositoryInterface
{
    /**
     * Get the model class name
     *
     * @return string
     */
    public function getModel(): string
    {
        return News::class;
    }

    /**
     * Get all news with filters and pagination
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getAllNews(Request $request): LengthAwarePaginator
    {
        // Reset query to avoid conditions from previous requests
        $this->query = $this->model->newQuery();

        $user = Auth::user();

        // If user is not admin, only show news of the user
        if ($user && $user->role !== UserType::ADMIN) {
            $this->query->where('news.user_id', $user->id);
        }

        // Join user
        $this->query->leftJoin('users', 'news.user_id', '=', 'users.id')
            ->select(['news.*', 'users.name as user_name']);

        $title          = $request->input('title');
        $content        = $request->input('content');
        $userName       = $request->input('user_name');
        $publishedStart = $request->input('published_at_start');
        $publishedEnd   = $request->input('published_at_end');
        $status         = $request->input('status', null);

        $this->query
            ->when($title, fn($q) => $q->where('news.title', 'like', "%{$title}%"))
            ->when($content, fn($q) => $q->where('news.content', 'like', "%{$content}%"))
            ->when($publishedStart, fn($q) => $q->whereDate('news.published_at', '>=', $publishedStart))
            ->when($publishedEnd, fn($q) => $q->whereDate('news.published_at', '<=', $publishedEnd))
            ->when(!is_null($status), fn($q) => $q->where('news.status', '=', $status))
            ->when($userName, fn($q) => $q->where("users.name", "like", "%{$userName}%"));

        // Sorting
        $allowedSort = ['id', 'title', 'created_at', 'user_name'];

        $sortField = $request->input('sort_field', 'id');
        $sortDirection = $request->input('sort_direction', 'desc');

        if (in_array($sortField, $allowedSort)) {
            if ($sortField === 'user_name') {
                $this->query->orderBy('users.name', $sortDirection);
            } else {
                $this->query->orderBy("news.$sortField", $sortDirection);
            }
        }

        // Pagination
        $page = $request->input('page', Pagination::PAGE->value);
        $perPage = $request->input('per_page', Pagination::PER_PAGE->value);

        return $this->query->paginate(
            $perPage,
            ['news.*', 'users.name as user_name'],
            'page',
            $page
        );
    }

    // ====== The functions below are APIs for the end user ======
    /**
     * Get latest news for homepage
     *
     * @param int $limit
     * @return array
     */
    public function getLatestNews($limit): array
    {
        return $this->model
            ->select('id', 'title', 'slug', 'summary', 'image_url', 'published_at', 'user_id')
            ->where('status', Status::ACTIVE->value)
            ->whereNotNull('published_at')
            ->orderBy('updated_at', 'desc')
            ->take($limit)
            ->get()
            ->toArray();
    }

    /**
     * Get all news for user with filters and pagination
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getAllNewsForUser(Request $request): LengthAwarePaginator
    {
        // Reset query to avoid conditions from previous requests
        $this->query = $this->model->newQuery();

        $title          = $request->input('title');
        $content        = $request->input('content');

        $this->query
            ->when($title, fn($q) => $q->where('news.title', 'like', '%' . $title . '%'))
            ->when($content, fn($q) => $q->where('news.content', 'like', '%' . $content . '%'));

        // Sorting
        $allowedSortFields = ['id', 'title'];
        $sortField = $request->input('sort_field', 'id');
        if (!in_array($sortField, $allowedSortFields)) {
            $sortField = 'id';
        }

        $sortDirection = $request->input('sort_direction', 'desc');
        $sortDirection = in_array($sortDirection, ['asc', 'desc']) ? $sortDirection : 'desc';

        $this->query->orderBy("news.$sortField", $sortDirection);
        // Pagination
        $page = $request->input('page', Pagination::PAGE->value);
        $perPage = $request->input('per_page', Pagination::PER_PAGE->value);

        return $this->query->paginate($perPage, ['*'], 'page', $page);
    }
}
