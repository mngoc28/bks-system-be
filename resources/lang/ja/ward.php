<?php

return [
    'messages' => [
        'get_wards_by_province_id_failed' => '都道府県のIDを指定してください。',
        'get_wards_by_province_id_success' => '区/市町村のリストを正常に取得しました。',
        'get_wards_by_province_id_not_found' => '区/市町村のリストが見つかりません。',
    ],
    'validation' => [
        'province_id' => [
            'required' => '都道府県IDは必須です。',
            'integer' => '都道府県IDは整数でなければなりません。',
            'exists' => '都道府県IDは存在しません。',
        ],
    ],
];
