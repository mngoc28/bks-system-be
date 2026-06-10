<?php

return [
    'validation' => [
        'start_date' => [
            'date'        => 'Ngày bắt đầu không hợp lệ',
            'date_format' => 'Ngày bắt đầu phải có định dạng Y-m-d (ví dụ: 2025-01-15)',
        ],
        'end_date'   => [
            'date'           => 'Ngày kết thúc không hợp lệ',
            'date_format'    => 'Ngày kết thúc phải có định dạng Y-m-d (ví dụ: 2025-01-31)',
            'after_or_equal' => 'Ngày kết thúc phải sau hoặc bằng ngày bắt đầu',
        ],
        'limit'      => [
            'integer' => 'Số lượng phải là số nguyên',
            'min'     => 'Số lượng phải lớn hơn hoặc bằng 1',
            'max'     => 'Số lượng không được vượt quá 100',
        ],
        'property_id' => [
            'integer' => 'Mã tài sản phải là số nguyên',
            'min'     => 'Mã tài sản không hợp lệ',
        ],
    ],
    'attributes' => [
        'start_date' => 'Ngày bắt đầu',
        'end_date'   => 'Ngày kết thúc',
    ],
    'messages'   => [
        'stats_fetched_successfully'                => 'Lấy thống kê dashboard thành công',
        'stats_fetch_failed'                        => 'Lấy thống kê dashboard thất bại',
        'bookings_per_month_fetched'                => 'Lấy số lượng đặt phòng theo tháng thành công',
        'bookings_per_month_fetch_failed'           => 'Lấy số lượng đặt phòng theo tháng thất bại',
        'bookings_trend_fetched'                    => 'Lấy xu hướng booking theo ngày thành công',
        'bookings_trend_fetch_failed'               => 'Lấy xu hướng booking theo ngày thất bại',
        'revenue_per_month_fetched'                 => 'Lấy doanh thu theo tháng thành công',
        'revenue_per_month_fetch_failed'            => 'Lấy doanh thu theo tháng thất bại',
        'all_properties_bookings_count_fetched'      => 'Lấy số lượng đặt phòng của tất cả cơ sở thành công',
        'all_properties_bookings_count_fetch_failed' => 'Lấy số lượng đặt phòng của tất cả cơ sở thất bại',
        'booking_status_breakdown_fetched'           => 'Lấy phân bổ trạng thái booking thành công',
        'booking_status_breakdown_fetch_failed'      => 'Lấy phân bổ trạng thái booking thất bại',
        'occupancy_chart_fetched'                    => 'Lấy xu hướng lấp phòng thành công',
        'occupancy_chart_fetch_failed'               => 'Lấy xu hướng lấp phòng thất bại',
        'revenue_performance_fetched'                => 'Lấy chỉ số ADR/RevPAR thành công',
        'revenue_performance_fetch_failed'           => 'Lấy chỉ số ADR/RevPAR thất bại',
        'property_not_accessible'                   => 'Bạn không có quyền truy cập tài sản này',
        'pending_bookings_fetched'                  => 'Lấy danh sách booking chờ duyệt thành công',
        'pending_bookings_fetch_failed'             => 'Lấy danh sách booking chờ duyệt thất bại',
    ],
];
