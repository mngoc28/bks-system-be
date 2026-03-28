<?php

namespace App\Http\Controllers;

use App\Enums\HttpStatus;
use App\Http\Validations\CouponValidation;
use App\Services\CouponService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function __construct(
        private readonly CouponService $couponService,
        private readonly CouponValidation $couponValidation
    ) {
    }

    /**
     * Retrieve coupons with optional filters.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $validator = $this->couponValidation->indexValidation($request);

        if ($validator->fails()) {
            return $this->errorResponse(
                __('coupon.validation_error'),
                $validator->errors(),
                HttpStatus::VALIDATION_ERROR
            );
        }

        $result = $this->couponService->list($request);

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
     * Create a new coupon.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validator = $this->couponValidation->createValidation($request);

        if ($validator->fails()) {
            return $this->errorResponse(
                __('coupon.validation_error'),
                $validator->errors(),
                HttpStatus::VALIDATION_ERROR
            );
        }

        $result = $this->couponService->create($validator->validated());

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
     * Update an existing coupon.
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function update(int $id, Request $request): JsonResponse
    {
        $validator = $this->couponValidation->updateValidation($id, $request);

        if ($validator->fails()) {
            return $this->errorResponse(
                __('coupon.validation_error'),
                $validator->errors(),
                HttpStatus::VALIDATION_ERROR
            );
        }

        $result = $this->couponService->update($id, $validator->validated());

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
     * Delete a coupon.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $validator = $this->couponValidation->deleteValidation($id);

        if ($validator->fails()) {
            return $this->errorResponse(
                __('coupon.validation_error'),
                $validator->errors(),
                HttpStatus::VALIDATION_ERROR
            );
        }

        $result = $this->couponService->delete($id);

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
}
