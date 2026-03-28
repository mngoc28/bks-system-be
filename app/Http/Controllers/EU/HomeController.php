<?php

namespace App\Http\Controllers\EU;

use App\Enums\HttpStatus;
use App\Http\Controllers\Controller;
use App\Http\Validations\RoomsValidation;
use App\Services\RoomsService;
use App\Services\NewsService;
use App\Services\PartnerInforServices;
use App\Services\ProvincesService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    protected $roomsValidation;
    protected $roomsService;
    protected $newsService;
    protected $partnerInforServices;
    protected $provinceService;

    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct(
        RoomsValidation $roomsValidation,
        RoomsService $roomsService,
        NewsService $newsService,
        PartnerInforServices $partnerInforServices,
        ProvincesService $provinceService
    ) {
        $this->roomsValidation = $roomsValidation;
        $this->roomsService    = $roomsService;
        $this->newsService = $newsService;
        $this->partnerInforServices = $partnerInforServices;
        $this->provinceService = $provinceService;
    }

    /**
     * Show latest rooms on home page
     * @param Request $request
     * @return JsonResponse
     */
    public function getLatestRooms(Request $request): JsonResponse
    {
        $validator = $this->roomsValidation->searchRoomValidation($request);

        if ($validator->fails()) {
            return $this->validateError(
                $validator->errors(),
                null,
                HttpStatus::VALIDATION_ERROR
            );
        }
        $result = $this->roomsService->getLatestRooms($request);

        if (! $result['success']) {
            return $this->errorResponse(
                $result['message'],
                null,
                HttpStatus::BAD_REQUEST
            );
        }

        return $this->successResponse(
            $result['data'],
            $result['message']
        );
    }

    /**
     * Get all provinces for homepage
     * @return JsonResponse
     */
    public function getProvinces(): JsonResponse
    {
        $result = $this->provinceService->getAllProvinces();

        if (!$result['success']) {
            return $this->errorResponse(
                $result['message'],
                null,
                HttpStatus::BAD_REQUEST
            );
        }

        return $this->successResponse(
            $result['data'],
            $result['message']
        );
    }

    /**
     * Get random partners for homepage
     * @param Request $request
     * @return JsonResponse
     */
    public function getRandomPartners(Request $request): JsonResponse
    {
        $result = $this->partnerInforServices->getRandomPartners($request);

        if (!$result['success']) {
            return $this->errorResponse(
                $result['message'],
                null,
                HttpStatus::BAD_REQUEST
            );
        }

        return $this->successResponse(
            $result['data'],
            $result['message']
        );
    }

    /**
     * Get latest news for homepage
     * @param Request $request
     * @return JsonResponse
     */
    public function getLatestNews(Request $request): JsonResponse
    {
        $result = $this->newsService->getLatestNews($request->limit ?? config('const.DEFAULT_PER_PAGE'));

        if (!$result['success']) {
            return $this->errorResponse(
                $result['message'],
                null,
                HttpStatus::BAD_REQUEST
            );
        }

        return $this->successResponse(
            $result['data'],
            $result['message']
        );
    }
}
