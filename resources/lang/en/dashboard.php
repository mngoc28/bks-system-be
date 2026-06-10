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
        'property_id' => [
            'integer' => 'Property id must be an integer',
            'min'     => 'Property id is invalid',
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
        'bookings_trend_fetched'            => 'Daily bookings trend fetched successfully',
        'bookings_trend_fetch_failed'       => 'Failed to fetch daily bookings trend',
        'revenue_per_month_fetched'         => 'Revenue per month fetched successfully',
        'revenue_per_month_fetch_failed'    => 'Failed to fetch revenue per month',
        'all_properties_bookings_count_fetched' => 'Bookings count for all properties fetched successfully',
        'all_properties_bookings_count_fetch_failed' => 'Failed to fetch bookings count for all properties',
        'booking_status_breakdown_fetched'           => 'Booking status breakdown fetched successfully',
        'booking_status_breakdown_fetch_failed'      => 'Failed to fetch booking status breakdown',
        'occupancy_chart_fetched'                    => 'Occupancy chart fetched successfully',
        'occupancy_chart_fetch_failed'               => 'Failed to fetch occupancy chart',
        'revenue_performance_fetched'                => 'Revenue performance metrics fetched successfully',
        'revenue_performance_fetch_failed'           => 'Failed to fetch revenue performance metrics',
        'property_not_accessible'                   => 'You do not have access to this property',
        'pending_bookings_fetched'                  => 'Pending bookings fetched successfully',
        'pending_bookings_fetch_failed'             => 'Failed to fetch pending bookings',
    ],
];
