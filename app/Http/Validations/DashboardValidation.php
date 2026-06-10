<?php

declare(strict_types=1);

namespace App\Http\Validations;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

final class DashboardValidation
{
    /**
     * Validate date range for dashboard queries
     *
     * @param Request $request
     * @return \Illuminate\Validation\Validator
     */
    public function validateDateRange(Request $request): \Illuminate\Validation\Validator
    {
        return Validator::make(
            $request->all(),
            [
                'start_date' => [
                    'nullable',
                    'date',
                    'date_format:Y-m-d',
                ],
                'end_date'   => [
                    'nullable',
                    'date',
                    'date_format:Y-m-d',
                    'after_or_equal:start_date',
                ],
            ],
            [
                'start_date.date'         => __('dashboard.validation.start_date.date'),
                'start_date.date_format'  => __('dashboard.validation.start_date.date_format'),
                'end_date.date'           => __('dashboard.validation.end_date.date'),
                'end_date.date_format'    => __('dashboard.validation.end_date.date_format'),
                'end_date.after_or_equal' => __('dashboard.validation.end_date.after_or_equal'),
            ]
        );
    }

    /**
     * Validate limit for recent bookings
     *
     * @param Request $request
     * @return \Illuminate\Validation\Validator
     */
    /**
     * Optional scope for partner dashboard endpoints (property filter, list limit).
     *
     * @param Request $request
     * @return \Illuminate\Validation\Validator
     */
    public function validateDashboardScope(Request $request): \Illuminate\Validation\Validator
    {
        return Validator::make(
            $request->all(),
            [
                'property_id' => ['nullable', 'integer', 'min:1'],
                'limit'       => ['nullable', 'integer', 'min:1', 'max:20'],
            ],
            [
                'property_id.integer' => __('dashboard.validation.property_id.integer'),
                'property_id.min'   => __('dashboard.validation.property_id.min'),
                'limit.integer'     => __('dashboard.validation.limit.integer'),
                'limit.min'         => __('dashboard.validation.limit.min'),
                'limit.max'         => __('dashboard.validation.limit.max'),
            ],
        );
    }

    public function validateLimit(Request $request): \Illuminate\Validation\Validator
    {
        return Validator::make(
            $request->all(),
            [
                'limit' => [
                    'nullable',
                    'integer',
                    'min:' . config('const.RECENT_BOOKINGS_MIN_LIMIT', 1),
                    'max:' . config('const.RECENT_BOOKINGS_MAX_LIMIT', 100),
                ],
            ],
            [
                'limit.integer' => __('dashboard.validation.limit.integer'),
                'limit.min'    => __('dashboard.validation.limit.min'),
                'limit.max'    => __('dashboard.validation.limit.max'),
            ]
        );
    }
}
