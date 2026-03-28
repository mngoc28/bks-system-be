<?php

namespace App\Services;

use App\Enums\HttpStatus;
use App\Enums\UserType;
use App\Repositories\NewsRepository\NewsRepositoryInterface;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

final class NewsService
{
    private NewsRepositoryInterface $newsRepository;
    private CloudinaryService $cloudinaryService;
    /**
     * Repository layer that handles data operations for news.
     * @param NewsRepositoryInterface $newsRepository
     * @param CloudinaryService $cloudinaryService
     */
    public function __construct(
        NewsRepositoryInterface $newsRepository,
        CloudinaryService $cloudinaryService
    ) {
        $this->newsRepository = $newsRepository;
        $this->cloudinaryService = $cloudinaryService;
    }

    /**
     * Check if the user can access the news
     * @param object $news
     * @param object $user
     * @return bool
     */
    private function canAccessNews($news, $user): bool
    {
        if (!$news || !$user) {
            return false;
        }

        return $user->role === UserType::ADMIN || $news->user_id === $user->id;
    }

    /**
     * Get all news
     * @param Request $request
     * @return object | null
     */
    public function getAllNews(Request $request): object | null
    {
        try {
            return $this->newsRepository->getAllNews($request);
        } catch (Exception $e) {
            Log::error('Error fetching all news: ' . $e->getMessage());
            return null;
        }
    }

    /**
     *  get news by id
     *  @param int $id
     *  @return array
     */
    public function getNewsById(int $id): array
    {
        try {
            $user = Auth::user();
            $news = $this->newsRepository->find($id);

            if (!$this->canAccessNews($news, $user)) {
                return [
                    'success' => false,
                    'code' => HttpStatus::FORBIDDEN
                ];
            }
            return [
                'success' => true,
                'code' => HttpStatus::OK,
                'data' => $news
            ];
        } catch (Exception $e) {
            Log::error('Error fetching news by id: ' . $e->getMessage());
            return [
                'success' => false,
                'code' => HttpStatus::INTERNAL_SERVER_ERROR
            ];
        }
    }

    /**
     * create news
     * @param Request $request
     * @return array
     */
    public function createNews(Request $request): array
    {
        try {
            $user = Auth::user();
            $create = $this->newsRepository->create([
                'user_id' => $user->id,
                'title' => $request->input('title'),
                'slug' => Str::slug($request->input('title')),
                'summary' => $request->input('summary'),
                'content' => $request->input('content'),
                'status' => $request->input('status'),
                'published_at' => $request->input('published_at'),
                'image_url' => $request->input('image_url'),
                'id_image_cloudinary' => $request->input('id_image_cloudinary'),
                'created_by' => $user->id,
                'updated_by' => $user->id,
            ]);
            if (!$create) {
                return [
                    'success' => false,
                    'code' => HttpStatus::BAD_REQUEST,
                    'message' => __('news.messages.create_failed')
                ];
            }
            return [
                'success' => true,
                'code' => HttpStatus::CREATED,
                'data' => $create,
                'message' => __('news.messages.create_success')
            ];
        } catch (Exception $e) {
            Log::error('Error creating news: ' . $e->getMessage());
            return [
                'success' => false,
                'code' => HttpStatus::INTERNAL_SERVER_ERROR,
                'message' => __('news.messages.create_failed')
            ];
        }
    }

    /**
     *  update news
     *  @param Request $request
     *  @param int $id
     *  @return array
     */
    public function updateNews(Request $request, int $id): array
    {
        try {
            $user = Auth::user();
            $news = $this->newsRepository->find($id);

            if (!$this->canAccessNews($news, $user)) {
                return [
                    'success' => false,
                    'code' => HttpStatus::FORBIDDEN
                ];
            }

            $updated = $this->newsRepository->update($id, [
                'title' => $request->title,
                'slug' =>  Str::slug($request->input('title')),
                'summary' => $request->summary,
                'content' => $request->input('content'),
                'status' => $request->input('status'),
                'published_at' => $request->published_at ?
                    Carbon::parse($request->published_at)->format('Y-m-d H:i:s')
                    : null,
                'image_url' => $request->image_url,
                'id_image_cloudinary' => $request->id_image_cloudinary,
                'updated_by' => Auth::id(),
            ]);
            if (!$updated) {
                return [
                    'success' => false,
                    'code' => HttpStatus::BAD_REQUEST
                ];
            }
            return [
                'success' => true,
                'code' => HttpStatus::OK,
                'data' => $updated
            ];
        } catch (Exception $e) {
            Log::error('Error update news: ' . $e->getMessage());
            return [
                'success' => false,
                'code' => HttpStatus::INTERNAL_SERVER_ERROR
            ];
        }
    }

    /**
     *  destroy news
     *  @param int $id
     *  @return array
     */
    public function destroyNews(int $id): array
    {
        try {
            $user = Auth::user();
            $news = $this->newsRepository->find($id);

            if (!$this->canAccessNews($news, $user)) {
                return [
                    'success' => false,
                    'code' => HttpStatus::FORBIDDEN
                ];
            }
            if ($news->id_image_cloudinary) {
                $this->cloudinaryService->deleteImage($news->id_image_cloudinary);
            }
            $deleted = $this->newsRepository->delete($id);
            if ($deleted === false) {
                return [
                    'success' => false,
                    'code' => HttpStatus::BAD_REQUEST
                ];
            }
            return [
                'success' => true,
                'code' => HttpStatus::OK,
                'data' => $deleted
            ];
        } catch (Exception $e) {
            Log::error('Error destroy news: ' . $e->getMessage());
            return [
                'success' => false,
                'code' => HttpStatus::INTERNAL_SERVER_ERROR
            ];
        }
    }

    // ====== The functions below are APIs for the end user ======

    /**
     * Get latest news for homepage
     * @param int $limit
     * @param int $skip
     * @return array
     */
    public function getLatestNews($limit): array
    {
        try {
            $news = $this->newsRepository->getLatestNews($limit);

            return [
                'success' => true,
                'data' => $news,
                'message' => __('news.messages.get_latest_news_success')
            ];
        } catch (Exception $e) {
            Log::error('Error fetching latest news: ' . $e->getMessage());
            return [
                'success' => false,
                'data' => null,
                'message' => __('news.messages.get_latest_news_failed')
            ];
        }
    }

    /**
     *  get news by id for user
     *  @param int $id
     *  @return object | null
     */
    public function getNewsDetailForUser(int $id): ?object
    {
        try {
            return $this->newsRepository->find($id);
        } catch (Exception $e) {
            Log::error('Error fetching news by id: ' . $e->getMessage());

            return null;
        }
    }

    /**
     * Get all news for user
     * @param Request $request
     * @return object | null
     */
    public function getlistNewsForUser(Request $request): object | null
    {
        try {
            return $this->newsRepository->getAllNewsForUser($request);
        } catch (Exception $e) {
            Log::error('Error fetching all news: ' . $e->getMessage());
            return null;
        }
    }
}
