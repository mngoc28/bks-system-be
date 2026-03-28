<?php

namespace App\Http\Validations;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CouponValidation
{
    /**
     * Validate list coupons request data.
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function indexValidation(Request $request)
    {
        return Validator::make(
            $request->all(),
            [
                'pagination' => ['nullable', 'integer', 'min:1'],
                'sort_by' => ['nullable', 'string'],
                'direction' => ['nullable', 'string', 'in:asc,desc'],
            ],
            [
                'pagination.integer' => __('coupon.pagination_integer'),
                'pagination.min' => __('coupon.pagination_min'),
                'sort_by.string' => __('coupon.sort_by_string'),
                'direction.string' => __('coupon.sort_direction_string'),
                'direction.in' => __('coupon.sort_direction_invalid'),
            ]
        );
    }

    /**
     * Validate coupon creation payload.
     *
     * @param Request $request
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function createValidation(Request $request)
    {
        return Validator::make(
            $request->all(),
            [
                'code' => ['required', 'string', 'max:50'],
                'type' => ['required', 'in:percent,fixed'],
                'value' => ['required', 'numeric', 'min:0'],
                'min_order_value' => ['nullable', 'numeric', 'min:0'],
                'max_discount_value' => ['nullable', 'numeric', 'min:0'],
                'usage_limit' => ['nullable', 'integer', 'min:0'],
                'start_date' => ['nullable', 'date'],
                'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
                'status' => ['required', 'in:active,inactive'],
            ],
            [
                'code.required' => __('coupon.code_required'),
                'code.string' => __('coupon.code_string'),
                'code.max' => __('coupon.code_max'),
                'type.required' => __('coupon.type_required'),
                'type.in' => __('coupon.type_invalid'),
                'value.required' => __('coupon.value_required'),
                'value.numeric' => __('coupon.value_numeric'),
                'value.min' => __('coupon.value_min'),
                'min_order_value.numeric' => __('coupon.min_order_numeric'),
                'min_order_value.min' => __('coupon.min_order_min'),
                'max_discount_value.numeric' => __('coupon.max_discount_numeric'),
                'max_discount_value.min' => __('coupon.max_discount_min'),
                'usage_limit.integer' => __('coupon.usage_limit_integer'),
                'usage_limit.min' => __('coupon.usage_limit_min'),
                'start_date.date' => __('coupon.start_date_date'),
                'end_date.date' => __('coupon.end_date_date'),
                'end_date.after_or_equal' => __('coupon.end_date_after_start'),
                'status.required' => __('coupon.status_required'),
                'status.in' => __('coupon.status_invalid'),
            ]
        );
    }

    /**
     * Validate coupon update payload.
     *
     * @param int $id
     * @param Request $request
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function updateValidation(int $id, Request $request)
    {
        return Validator::make(
            array_merge($request->all(), ['id' => $id]),
            [
                'id' => ['required', 'integer', 'exists:coupons,id'],
                'code' => ['nullable', 'string', 'max:50'],
                'type' => ['nullable', 'in:percent,fixed'],
                'value' => ['nullable', 'numeric', 'min:0'],
                'min_order_value' => ['nullable', 'numeric', 'min:0'],
                'max_discount_value' => ['nullable', 'numeric', 'min:0'],
                'usage_limit' => ['nullable', 'integer', 'min:0'],
                'start_date' => ['nullable', 'date'],
                'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
                'status' => ['nullable', 'in:active,inactive'],
            ],
            [
                'id.required' => __('coupon.id_required'),
                'id.integer' => __('coupon.id_integer'),
                'id.exists' => __('coupon.id_exists'),
                'code.string' => __('coupon.code_string'),
                'code.max' => __('coupon.code_max'),
                'type.in' => __('coupon.type_invalid'),
                'value.numeric' => __('coupon.value_numeric'),
                'value.min' => __('coupon.value_min'),
                'min_order_value.numeric' => __('coupon.min_order_numeric'),
                'min_order_value.min' => __('coupon.min_order_min'),
                'max_discount_value.numeric' => __('coupon.max_discount_numeric'),
                'max_discount_value.min' => __('coupon.max_discount_min'),
                'usage_limit.integer' => __('coupon.usage_limit_integer'),
                'usage_limit.min' => __('coupon.usage_limit_min'),
                'start_date.date' => __('coupon.start_date_date'),
                'end_date.date' => __('coupon.end_date_date'),
                'end_date.after_or_equal' => __('coupon.end_date_after_start'),
                'status.in' => __('coupon.status_invalid'),
            ]
        );
    }

    /**
     * Validate coupon deletion request.
     *
     * @param int $id
     * @return \Illuminate\Contracts\Validation\Validator
     */
    public function deleteValidation(int $id)
    {
        return Validator::make(
            ['id' => $id],
            [
                'id' => ['required', 'integer', 'exists:coupons,id'],
            ],
            [
                'id.required' => __('coupon.id_required'),
                'id.integer' => __('coupon.id_integer'),
                'id.exists' => __('coupon.id_exists'),
            ]
        );
    }
}
