<?php

return [
    'messages' => [
        'get_wards_by_province_id_failed' => 'Không thể lấy danh sách phường/xã theo tỉnh/thành phố.',
        'get_wards_by_province_id_success' => 'Lấy danh sách phường/xã theo tỉnh/thành phố thành công.',
        'get_wards_by_province_id_not_found' => 'Không tìm thấy danh sách phường/xã theo tỉnh/thành phố.',
    ],
    'validation' => [
        'province_id' => [
            'required' => 'Tỉnh/Thành phố ID là bắt buộc.',
            'integer' => 'Tỉnh/Thành phố ID phải là số nguyên.',
            'exists' => 'Tỉnh/Thành phố ID không tồn tại.',
        ],
    ],
];
