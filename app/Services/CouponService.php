<?php

namespace App\Services;

use App\Http\Resources\CouponResource;
use App\Repositories\CouponRepository\CouponRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CouponService
{
    public function __construct(private CouponRepositoryInterface $couponRepository)
    {
    }

    /**
     * Retrieve coupons list based on provided filters.
     *
     * @param Request $request
     * @return array{success: bool, data: mixed, message: string}
     */
    public function list(Request $request): array
    {
        try {
            $filters = $request->only(['pagination', 'sort_by', 'direction']);

            $coupons = $this->couponRepository->paginateWithFilters($filters);

            return [
                'success' => true,
                'data' => CouponResource::collection($coupons)->response()->getData(true),
                'message' => __('coupon.messages.fetch_success'),
            ];
        } catch (\Throwable $exception) {
            Log::error('Failed to fetch coupons list', [
                'filters' => $request->all(),
                'exception' => $exception,
            ]);

            return [
                'success' => false,
                'data' => null,
                'message' => __('coupon.messages.fetch_error'),
            ];
        }
    }

    /**
     * Create a new coupon record.
     *
     * @param array $data
     * @return array{success: bool, data: mixed, message: string}
     */
    public function create(array $data): array
    {
        DB::beginTransaction();

        try {
            $coupon = $this->couponRepository->create($data);

            DB::commit();

            return [
                'success' => true,
                'data' => CouponResource::make($coupon)->resolve(),
                'message' => __('coupon.messages.create_success'),
            ];
        } catch (\Throwable $exception) {
            DB::rollBack();

            Log::error('Failed to create coupon', [
                'payload' => $data,
                'exception' => $exception,
            ]);

            return [
                'success' => false,
                'data' => null,
                'message' => __('coupon.messages.create_error'),
            ];
        }
    }

    /**
     * Update an existing coupon record.
     *
     * @param int $id
     * @param array $data
     * @return array{success: bool, data: mixed, message: string}
     */
    public function update(int $id, array $data): array
    {
        DB::beginTransaction();

        try {
            $coupon = $this->couponRepository->find($id);

            if (! $coupon) {
                return [
                    'success' => false,
                    'data' => null,
                    'message' => __('coupon.messages.not_found'),
                ];
            }

            $coupon->update($data);

            DB::commit();

            return [
                'success' => true,
                'data' => CouponResource::make($coupon->fresh())->resolve(),
                'message' => __('coupon.messages.update_success'),
            ];
        } catch (\Throwable $exception) {
            DB::rollBack();

            Log::error('Failed to update coupon', [
                'coupon_id' => $id,
                'payload' => $data,
                'exception' => $exception,
            ]);

            return [
                'success' => false,
                'data' => null,
                'message' => __('coupon.messages.update_error'),
            ];
        }
    }

    /**
     * Delete a coupon record.
     *
     * @param int $id
     * @return array{success: bool, data: mixed, message: string}
     */
    public function delete(int $id): array
    {
        DB::beginTransaction();

        try {
            $coupon = $this->couponRepository->find($id);

            if (! $coupon) {
                return [
                    'success' => false,
                    'data' => null,
                    'message' => __('coupon.messages.not_found'),
                ];
            }

            $this->couponRepository->delete($id);

            DB::commit();

            return [
                'success' => true,
                'data' => null,
                'message' => __('coupon.messages.delete_success'),
            ];
        } catch (\Throwable $exception) {
            DB::rollBack();

            Log::error('Failed to delete coupon', [
                'coupon_id' => $id,
                'exception' => $exception,
            ]);

            return [
                'success' => false,
                'data' => null,
                'message' => __('coupon.messages.delete_error'),
            ];
        }
    }
}
