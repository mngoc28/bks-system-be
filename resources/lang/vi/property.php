<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Ngôn ngữ miền Property (cơ sở / bất động sản cho thuê)
    |--------------------------------------------------------------------------
    |
    | Thay thế file legacy "property". Dùng cho thông báo và validation property.
    |
    */

    'validation' => [
        'user_id'          => [
            'required' => 'ID người dùng là bắt buộc.',
            'integer'  => 'ID người dùng phải là số nguyên.',
            'exists'   => 'Người dùng đã chọn không tồn tại.',
        ],
        'province_id'      => [
            'required' => 'ID tỉnh/thành phố là bắt buộc.',
            'integer'  => 'ID tỉnh/thành phố phải là số nguyên.',
            'exists'   => 'Tỉnh/thành phố đã chọn không tồn tại.',
        ],
        'ward_id'          => [
            'required' => 'ID phường/xã là bắt buộc.',
            'integer'  => 'ID phường/xã phải là số nguyên.',
            'exists'   => 'Phường/xã đã chọn không tồn tại.',
        ],
        'ward_name'        => [
            'max' => 'Tên phường/xã không được vượt quá 255 ký tự.',
        ],
        'province_name'    => [
            'max' => 'Tên tỉnh/thành không được vượt quá 255 ký tự.',
        ],
        'name'             => [
            'required' => 'Tên cơ sở là bắt buộc.',
            'max'      => 'Tên cơ sở không được vượt quá 255 ký tự.',
            'unique'   => 'Tên cơ sở đã tồn tại.',
            'string'   => 'Tên cơ sở phải là chuỗi hợp lệ.',
        ],
        'address_detail'   => [
            'max'    => 'Địa chỉ chi tiết không được vượt quá 255 ký tự.',
            'string' => 'Địa chỉ chi tiết phải là chuỗi hợp lệ.',
        ],
        'number_of_floors' => [
            'integer' => 'Số tầng phải là số nguyên.',
            'min'     => 'Số tầng phải ít nhất là 1.',
        ],
        'number_of_units'  => [
            'integer' => 'Số lượng phòng phải là số nguyên.',
            'min'     => 'Số lượng phòng phải ít nhất là 0.',
        ],
        'year_built'       => [
            'integer' => 'Năm xây dựng phải là số nguyên.',
            'min'     => 'Năm xây dựng phải ít nhất là 1900.',
            'max'     => 'Năm xây dựng không được vượt quá ' . (date('Y') + 10) . '.',
        ],
        'property_type'    => [
            'integer' => 'Loại hình công trình phải là số nguyên.',
            'in'      => 'Loại hình công trình phải là một trong các giá trị: 1, 2, 3, 4, 5, 6, 7, 8, 9.',
        ],
        'property_type_id' => [
            'required' => 'Loại hình lưu trú là bắt buộc.',
            'integer'  => 'Loại hình lưu trú phải là số nguyên.',
            'exists'   => 'Loại hình lưu trú không tồn tại.',
        ],
        'rent_category'    => [
            'required' => 'Hình thức cho thuê là bắt buộc.',
            'integer'  => 'Hình thức cho thuê phải là số nguyên.',
            'in'       => 'Hình thức cho thuê không hợp lệ.',
        ],
        'occupancy_filter' => [
            'in' => 'Bộ lọc trạng thái phòng không hợp lệ.',
        ],
        'min_rating'       => [
            'numeric' => 'Điểm đánh giá tối thiểu phải là số.',
            'min'     => 'Điểm đánh giá tối thiểu phải từ 0 trở lên.',
            'max'     => 'Điểm đánh giá tối thiểu không được vượt quá 5.',
        ],
        'has_rooms'        => [
            'integer' => 'Bộ lọc có phòng phải là số nguyên.',
            'in'      => 'Bộ lọc có phòng phải là 0 hoặc 1.',
        ],
        'area'             => [
            'numeric' => 'Diện tích phải là số.',
            'min'     => 'Diện tích phải ít nhất là 0.',
        ],
        'description'      => [
            'string' => 'Mô tả phải là chuỗi hợp lệ.',
        ],
        'created_by'       => [
            'integer' => 'ID người tạo phải là số nguyên.',
            'exists'  => 'Người tạo được chọn không tồn tại.',
        ],
        'updated_by'       => [
            'integer' => 'ID người cập nhật phải là số nguyên.',
            'exists'  => 'Người cập nhật được chọn không tồn tại.',
        ],
        'id'               => [
            'required'     => 'ID cơ sở là bắt buộc.',
            'integer'      => 'ID cơ sở phải là số nguyên.',
            'exists'       => 'ID cơ sở không tồn tại.',
            'has_rooms'    => 'Không thể xóa cơ sở còn phòng.',
            'has_bookings' => 'Không thể xóa cơ sở còn đặt phòng.',
        ],
    ],
    'attributes' => [
        'user_id'          => 'ID người dùng',
        'province_id'      => 'ID tỉnh/thành phố',
        'ward_id'          => 'ID phường/xã',
        'ward_name'        => 'tên phường/xã',
        'province_name'    => 'tên tỉnh/thành',
        'name'             => 'tên cơ sở',
        'keyword'          => 'từ khóa tìm kiếm',
        'address_detail'   => 'địa chỉ chi tiết',
        'number_of_floors' => 'số tầng',
        'number_of_units'  => 'số lượng phòng',
        'year_built'       => 'năm xây dựng',
        'property_type'    => 'loại hình công trình',
        'property_type_id' => 'loại hình lưu trú',
        'rent_category'    => 'hình thức cho thuê',
        'occupancy_filter' => 'trạng thái phòng',
        'min_rating'       => 'điểm đánh giá tối thiểu',
        'has_rooms_filter' => 'bộ lọc có phòng',
        'area'             => 'diện tích',
        'description'      => 'mô tả',
        'created_by'       => 'người tạo',
        'updated_by'       => 'người cập nhật',
        'id'               => 'ID cơ sở',
    ],
    'messages'   => [
        'retrieved_successfully'                 => 'Lấy danh sách cơ sở thành công.',
        'retrieved_failed'                       => 'Không thể lấy danh sách cơ sở.',
        'found_successfully'                     => 'Lấy thông tin cơ sở thành công.',
        'not_found'                              => 'Không tìm thấy cơ sở.',
        'find_failed'                            => 'Không thể lấy thông tin cơ sở.',
        'created_successfully'                   => 'Tạo cơ sở thành công.',
        'create_failed'                          => 'Không thể tạo cơ sở.',
        'updated_successfully'                   => 'Cập nhật cơ sở thành công.',
        'update_failed'                          => 'Không thể cập nhật cơ sở.',
        'deleted_successfully'                   => 'Xóa cơ sở thành công.',
        'delete_failed'                          => 'Không thể xóa cơ sở.',
        'bookings_retrieved_successfully'        => 'Lấy danh sách đặt phòng theo cơ sở thành công.',
        'bookings_retrieved_failed'              => 'Không thể lấy danh sách đặt phòng theo cơ sở.',
        'property_types_retrieved_successfully'  => 'Lấy danh sách loại hình công trình thành công.',
        'property_types_retrieved_failed'        => 'Không thể lấy danh sách loại hình công trình.',
    ],

    'structure_kind' => [
        1 => 'Chung cư / căn hộ chung cư',
        2 => 'Tòa nhà / tháp',
        3 => 'Biệt thự',
        4 => 'Nhà phố / nhà liền kề',
        5 => 'Căn hộ / Căn hộ dịch vụ',
        6 => 'Nhà trọ / homestay',
        7 => 'Khách sạn',
        8 => 'Văn phòng',
        9 => 'Khác',
    ],
];
