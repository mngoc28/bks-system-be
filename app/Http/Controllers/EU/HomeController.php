<?php

namespace App\Http\Controllers\EU;

use App\Enums\HttpStatus;
use App\Http\Controllers\Controller;
use App\Http\Validations\RoomsValidation;
use App\Services\RoomsService;
use App\Services\NewsService;
use App\Services\PartnerInforServices;
use App\Services\ProvincesService;
use App\Services\TouristSpotService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    protected $roomsValidation;
    protected $roomsService;
    protected $newsService;
    protected $partnerInforServices;
    protected $provinceService;
    protected TouristSpotService $touristSpotService;

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
        ProvincesService $provinceService,
        TouristSpotService $touristSpotService,
    ) {
        $this->roomsValidation = $roomsValidation;
        $this->roomsService    = $roomsService;
        $this->newsService = $newsService;
        $this->partnerInforServices = $partnerInforServices;
        $this->provinceService = $provinceService;
        $this->touristSpotService = $touristSpotService;
    }

    /**
     * Show top rated rooms on home page
     * @param Request $request
     * @return JsonResponse
     */
    public function getTopRatedRooms(Request $request): JsonResponse
    {
        $validator = $this->roomsValidation->searchRoomValidation($request);

        if ($validator->fails()) {
            return $this->validateError(
                $validator->errors(),
                null,
                HttpStatus::VALIDATION_ERROR
            );
        }
        $result = $this->roomsService->getTopRatedRooms($request);

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
     * Get suggested rooms grouped by province for homepage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getRoomsByProvince(Request $request): JsonResponse
    {
        $validator = $this->roomsValidation->suggestedRoomsByProvinceValidation($request);

        if ($validator->fails()) {
            return $this->validateError(
                $validator->errors(),
                null,
                HttpStatus::VALIDATION_ERROR
            );
        }

        $result = $this->roomsService->handleSuggestedRoomsByProvince($request);

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
     * Get suggested rooms grouped by tourist spot for homepage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getRoomsByTouristSpot(Request $request): JsonResponse
    {
        $validator = $this->roomsValidation->suggestedRoomsByTouristSpotValidation($request);

        if ($validator->fails()) {
            return $this->validateError(
                $validator->errors(),
                null,
                HttpStatus::VALIDATION_ERROR
            );
        }

        $result = $this->roomsService->handleSuggestedRoomsByTouristSpot($request);

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
     * List tourist spots for public search suggestions.
     */
    public function getTouristSpots(Request $request): JsonResponse
    {
        $validator = $this->roomsValidation->publicTouristSpotsValidation($request);

        if ($validator->fails()) {
            return $this->validateError(
                $validator->errors(),
                null,
                HttpStatus::VALIDATION_ERROR
            );
        }

        $result = $this->touristSpotService->listPublicSuggestions($request);

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

    /**
     * Register email to get active coupon code
     * @param Request $request
     * @return JsonResponse
     */
    public function registerCoupon(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $coupon = \App\Models\Coupon::where('status', 'active')
            ->where(function ($query) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', now());
            })
            ->orderBy('id', 'asc')
            ->first();

        $code = $coupon ? $coupon->code : 'BKSSUMMER10';

        return $this->successResponse(
            ['code' => $code],
            'Đăng ký nhận coupon thành công!'
        );
    }
}
