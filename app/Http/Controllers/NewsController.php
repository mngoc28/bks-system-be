<?php

namespace App\Http\Controllers;

use App\Enums\HttpStatus;
use App\Http\Validations\NewsValidation;
use App\Services\NewsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class NewsController extends Controller
{
    /**
     * Service layer that handles business logic for news.
     * Validation layer that handles request data validation for news.
     * @param NewsService $newsService
     * @param NewsValidation $newsValidation
     */
    public function __construct(
        private NewsService $newsService,
        private NewsValidation $newsValidation
    ) {
    }

    /**
     * Get all news
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $validator = $this->newsValidation->indexValidation($request);
        if ($validator->fails()) {
            return $this->validateError(
                $validator->errors(),
                null,
                HttpStatus::VALIDATION_ERROR,
                null
            );
        }
        $result = $this->newsService->getAllNews($request);
        if ($result === null) {
            return $this->errorResponse(
                __('news.messages.fetch_failed'),
                null,
                HttpStatus::BAD_REQUEST,
                null
            );
        }
        return $this->successResponse(
            $result,
            __('news.messages.fetch_success')
        );
    }

    /**
     *  get news by id
     *  @param int $id
     *  @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $validator = $this->newsValidation->showValidation($id);

        if ($validator->fails()) {
            return $this->validateError(
                $validator->errors(),
                null,
                HttpStatus::VALIDATION_ERROR,
                null
            );
        }

        $result = $this->newsService->getNewsById($id);

        if ($result['success'] === false) {
            return $this->errorResponse(
                __('news.messages.fetch_failed'),
                null,
                $result['code'],
                null
            );
        }

        return $this->successResponse(
            $result['data'],
            __('news.messages.fetch_success')
        );
    }

    /**
     *  create news
     *  @param Request $request
     *  @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validator = $this->newsValidation->storeValidation($request);

        if ($validator->fails()) {
            return $this->validateError(
                $validator->errors(),
                null,
                HttpStatus::VALIDATION_ERROR,
                null
            );
        }

        $result = $this->newsService->createNews($request);

        if ($result['success'] === false) {
            return $this->errorResponse(
                __('news.messages.create_failed'),
                null,
                $result['code'],
                null
            );
        }

        return $this->successResponse(
            $result['data'],
            __('news.messages.create_success')
        );
    }

    /**
     *  update news
     *  @param Request $request
     *  @param int $id
     *  @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $validator = $this->newsValidation->updateValidation(array_merge($request->all(), ['id' => $id]));

        if ($validator->fails()) {
            return $this->validateError(
                $validator->errors(),
                null,
                HttpStatus::VALIDATION_ERROR,
                null
            );
        }

        $result = $this->newsService->updateNews($request, $id);
        if ($result['success'] === false) {
            return $this->errorResponse(
                __('news.messages.update_failed'),
                null,
                $result['code'],
                null
            );
        }

        return $this->successResponse(
            $result['data'],
            __('news.messages.update_success')
        );
    }

    /**
     *  destroy news
     *  @param int $id
     *  @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $validator = $this->newsValidation->destroyValidation($id);
        if ($validator->fails()) {
            return $this->validateError(
                $validator->errors(),
                null,
                HttpStatus::VALIDATION_ERROR,
                null
            );
        }
        $result = $this->newsService->destroyNews($id);
        if ($result['success'] === false) {
            return $this->errorResponse(
                __('news.messages.delete_failed'),
                null,
                $result['code'],
                null
            );
        }
        return $this->successResponse(
            $result['data'],
            __('news.messages.delete_success')
        );
    }

    /**
     * Get all news for user
     * @param Request $request
     * @return JsonResponse
     */
    public function listNews(Request $request): JsonResponse
    {
        $validator = $this->newsValidation->indexValidation($request);
        if ($validator->fails()) {
            return $this->validateError(
                $validator->errors(),
                null,
                HttpStatus::VALIDATION_ERROR,
                null
            );
        }

        $result = $this->newsService->getlistNewsForUser($request);
        if ($result === null) {
            return $this->errorResponse(
                __('news.messages.fetch_failed'),
                null,
                HttpStatus::BAD_REQUEST,
                null
            );
        }

        return $this->successResponse(
            $result,
            __('news.messages.fetch_success')
        );
    }

    /**
     *  get news by id for user
     *  @param int $id
     *  @return JsonResponse
     */
    public function detailNews(int $id): JsonResponse
    {
        $validator = $this->newsValidation->showValidation($id);

        if ($validator->fails()) {
            return $this->validateError(
                $validator->errors(),
                null,
                HttpStatus::VALIDATION_ERROR,
                null
            );
        }

        $result = $this->newsService->getNewsDetailForUser($id);

        if ($result['success'] === false) {
            return $this->errorResponse(
                __('news.messages.fetch_failed'),
                null,
                $result['code'],
                null
            );
        }

        return $this->successResponse(
            $result,
            __('news.messages.fetch_success')
        );
    }
}
