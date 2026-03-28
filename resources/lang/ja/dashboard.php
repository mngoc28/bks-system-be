<?php

return [
    'validation' => [
        'start_date' => [
            'date'        => '開始日が無効です',
            'date_format' => '開始日はY-m-d形式である必要があります（例：2025-01-15）',
        ],
        'end_date' => [
            'date'           => '終了日が無効です',
            'date_format'    => '終了日はY-m-d形式である必要があります（例：2025-01-31）',
            'after_or_equal' => '終了日は開始日以降である必要があります',
        ],
        'limit'     => [
            'integer' => '制限は整数である必要があります',
            'min'     => '制限は少なくとも1である必要があります',
            'max'     => '制限は100を超えてはいけません',
        ],
    ],
    'attributes' => [
        'start_date' => '開始日',
        'end_date'   => '終了日',
    ],
    'messages' => [
        'stats_fetched_successfully'        => 'ダッシュボード統計の取得に成功しました',
        'stats_fetch_failed'                => 'ダッシュボード統計の取得に失敗しました',
        'bookings_per_month_fetched'        => '月別予約数の取得に成功しました',
        'bookings_per_month_fetch_failed'   => '月別予約数の取得に失敗しました',
        'revenue_per_month_fetched'         => '月別収益の取得に成功しました',
        'revenue_per_month_fetch_failed'    => '月別収益の取得に失敗しました',
        'all_buildings_bookings_count_fetched' => 'すべての建物の予約数の取得に成功しました',
        'all_buildings_bookings_count_fetch_failed' => 'すべての建物の予約数の取得に失敗しました',
    ],
];
