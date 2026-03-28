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
        'revenue_per_month_fetched'                 => 'Lấy doanh thu theo tháng thành công',
        'revenue_per_month_fetch_failed'            => 'Lấy doanh thu theo tháng thất bại',
        'all_buildings_bookings_count_fetched'      => 'Lấy số lượng đặt phòng của tất cả các tòa nhà thành công',
        'all_buildings_bookings_count_fetch_failed' => 'Lấy số lượng đặt phòng của tất cả các tòa nhà thất bại',
    ],
];
