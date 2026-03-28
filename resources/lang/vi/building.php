<?php

return [
    /*
        |--------------------------------------------------------------------------
            | Building Language Lines
            |--------------------------------------------------------------------------
            |
            | The following language lines are used during building operations for various
            | messages that we need to display to the user. You are free to modify
            | these language lines according to your application's requirements.
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
        'name'             => [
            'required' => 'Tên tòa nhà là bắt buộc.',
            'max'      => 'Tên tòa nhà không được vượt quá 255 ký tự.',
            'unique'   => 'Tên tòa nhà đã tồn tại.',
            'string'   => 'Tên tòa nhà phải là chuỗi hợp lệ.',
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
        'building_type'    => [
            'integer' => 'Loại tòa nhà phải là số nguyên.',
            'in'      => 'Loại tòa nhà phải là một trong các giá trị: 1, 2, 3, 4, 5, 6, 7, 8, 9.',
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
            'required'     => 'ID tòa nhà là bắt buộc.',
            'integer'      => 'ID tòa nhà phải là số nguyên.',
            'exists'       => 'ID tòa nhà không tồn tại.',
            'has_rooms'    => 'Không thể xóa tòa nhà có phòng.',
            'has_bookings' => 'Không thể xóa tòa nhà có đặt phòng.',
        ],
    ],
    'attributes' => [
        'user_id'          => 'ID người dùng',
        'province_id'      => 'ID tỉnh/thành phố',
        'ward_id'          => 'ID phường/xã',
        'name'             => 'tên tòa nhà',
        'address_detail'   => 'địa chỉ chi tiết',
        'number_of_floors' => 'số tầng',
        'number_of_units'  => 'số lượng phòng',
        'year_built'       => 'năm xây dựng',
        'building_type'    => 'loại tòa nhà',
        'area'             => 'diện tích',
        'description'      => 'mô tả',
        'created_by'       => 'người tạo',
        'updated_by'       => 'người cập nhật',
        'id'               => 'ID tòa nhà',
    ],
    'messages'   => [
        'retrieved_successfully'                              => 'Lấy danh sách tòa nhà thành công.',
        'retrieved_failed'                                    => 'Không thể lấy danh sách tòa nhà.',
        'found_successfully'                                  => 'Lấy thông tin tòa nhà thành công.',
        'not_found'                                           => 'Không tìm thấy tòa nhà.',
        'find_failed'                                         => 'Không thể lấy thông tin tòa nhà.',
        'created_successfully'                                => 'Tạo tòa nhà thành công.',
        'create_failed'                                       => 'Không thể tạo tòa nhà.',
        'updated_successfully'                                => 'Cập nhật tòa nhà thành công.',
        'update_failed'                                       => 'Không thể cập nhật tòa nhà.',
        'deleted_successfully'                                => 'Xóa tòa nhà thành công.',
        'delete_failed'                                       => 'Không thể xóa tòa nhà.',
        'bookings_retrieved_successfully'                     => 'Lấy danh sách đặt phòng theo tòa nhà thành công.',
        'bookings_retrieved_failed'                           => 'Không thể lấy danh sách đặt phòng theo tòa nhà.',
        'buildings_types_retrieved_successfully'               => 'Lấy danh sách loại tòa nhà thành công.',
        'buildings_types_retrieved_failed'                      => 'Không thể lấy danh sách loại tòa nhà.',
        'all_buildings_bookings_count_retrieved_successfully' =>
        'Lấy số lượng đặt phòng của tất cả các tòa nhà thành công.',
        'all_buildings_bookings_count_retrieved_failed'       =>
        'Không thể lấy số lượng đặt phòng của tất cả các tòa nhà.',
    ],
];
