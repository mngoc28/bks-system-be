<?php

return [
    'validation' => [
        'start_date' => [
            'date'        => 'Start date is not valid',
            'date_format' => 'Start date must be in Y-m-d format (e.g., 2025-01-15)',
        ],
        'end_date' => [
            'date'           => 'End date is not valid',
            'date_format'    => 'End date must be in Y-m-d format (e.g., 2025-01-31)',
            'after_or_equal' => 'End date must be after or equal to start date',
        ],
        'limit'     => [
            'integer' => 'Limit must be an integer',
            'min'     => 'Limit must be at least 1',
            'max'     => 'Limit must not exceed 100',
        ],
    ],
    'attributes' => [
        'start_date' => 'Start Date',
        'end_date'   => 'End Date',
    ],
    'messages' => [
        'stats_fetched_successfully'        => 'Dashboard statistics fetched successfully',
        'stats_fetch_failed'                => 'Failed to fetch dashboard statistics',
        'bookings_per_month_fetched'        => 'Bookings per month fetched successfully',
        'bookings_per_month_fetch_failed'   => 'Failed to fetch bookings per month',
        'revenue_per_month_fetched'         => 'Revenue per month fetched successfully',
        'revenue_per_month_fetch_failed'    => 'Failed to fetch revenue per month',
        'all_buildings_bookings_count_fetched' => 'Bookings count for all buildings fetched successfully',
        'all_buildings_bookings_count_fetch_failed' => 'Failed to fetch bookings count for all buildings',
    ],
];
