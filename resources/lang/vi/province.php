<?php

return [
    'validation' => [
        'name' => [
            'required' => 'tên tỉnh/thành phố không được để trống',
            'string' => 'tên tỉnh/thành phố phải là chuỗi ký tự hợp lệ',
            'max' => 'tên tỉnh/thành phố không được quá 100 ký tự',
            'unique' => 'tên tỉnh/thành phố đã tồn tại',
        ],
        'name_en' => [
            'required' => 'tên tỉnh/thành phố (tiếng Anh) không được để trống',
            'string' => 'tên tỉnh/thành phố (tiếng Anh) phải là chuỗi ký tự hợp lệ',
            'max' => 'tên tỉnh/thành phố (tiếng Anh) không được quá 100 ký tự',
        ],
        'id' => [
            'required' => 'ID tỉnh/thành phố không được để trống',
            'integer' => 'ID tỉnh/thành phố phải là số nguyên',
            'exists' => 'tỉnh/thành phố được chỉ định không tồn tại',
            'unique' => 'ID tỉnh/thành phố đã được sử dụng',
        ],
    ],
    'attributes' => [
        'name' => 'tên tỉnh/thành phố',
        'name_en' => 'tên tỉnh/thành phố (tiếng Anh)',
        'id' => 'ID tỉnh/thành phố',
    ],
    'messages' => [
        'create_success' => 'Tỉnh/Thành phố đã được tạo thành công.',
        'create_error'   => 'Tạo Tỉnh/Thành phố thất bại.',
        'update_success' => 'Tỉnh/Thành phố đã được cập nhật thành công.',
        'update_error'   => 'Cập nhật Tỉnh/Thành phố thất bại.',
        'show_success'   => 'Lấy thông tin Tỉnh/Thành phố thành công.',
        'not_found'      => 'Không tìm thấy Tỉnh/Thành phố.',
        'search_success' => 'Danh sách Tỉnh/Thành phố đã được truy xuất thành công.',
        'search_failed'  => 'Truy xuất danh sách Tỉnh/Thành phố thất bại.',
        'delete_success' => 'Tỉnh/Thành phố đã được xóa thành công.',
        'get_all_provinces_types_success' => 'Lấy danh sách loại Tỉnh/Thành phố thành công.',
        'get_all_provinces_types_failed' => 'Lấy danh sách loại Tỉnh/Thành phố thất bại.',
        'get_all_provinces_failed' => 'Lấy danh sách Tỉnh/Thành phố thất bại.',
        'get_all_provinces_success' => 'Lấy danh sách Tỉnh/Thành phố thành công.',
    ],
];
