<?php

return [
    /*
        |--------------------------------------------------------------------------
            | Room Language Lines
            |--------------------------------------------------------------------------
            |
            | The following language lines are used during room operations for various
            | messages that we need to display to the user. You are free to modify
            | these language lines according to your application's requirements.
            |
            */
    'validation' => [
        'id' => [
            'required' => 'ID phòng là bắt buộc',
            'integer'  => 'ID phòng phải là số nguyên',
            'exists'   => 'ID phòng không tồn tại',
            'unique'   => 'ID phòng đã được sử dụng trong đặt phòng',
        ],
        'building_id' => [
            'exists' => 'Tòa nhà đã chọn không tồn tại',
            'integer'  => 'ID tòa nhà phải là số nguyên',
            'required' => 'ID tòa nhà là bắt buộc',
        ],
        'title' => [
            'required' => 'Tiêu đề phòng là bắt buộc',
            'string'   => 'Tiêu đề phải là chuỗi ký tự',
            'max'      => 'Tiêu đề không được vượt quá 100 ký tự',
        ],
        'room_number' => [
            'required' => 'Số phòng là bắt buộc',
            'integer'  => 'Số phòng phải là số nguyên',
            'numeric'  => 'Số phòng phải là số',
        ],
        'deposit' => [
            'numeric' => 'Tiền cọc phải là số',
            'min'     => 'Tiền cọc không được nhỏ hơn 0',
        ],
        'floor_number' => [
            'required' => 'Tầng phòng là bắt buộc',
            'integer'  => 'Tầng phòng phải là số nguyên',
            'min'      => 'Tầng phòng không được nhỏ hơn 0',
        ],
        'people' => [
            'required' => 'Số người ở là bắt buộc',
            'integer'  => 'Số người ở phải là số nguyên',
            'min'      => 'Số người ở phải lớn hơn hoặc bằng 1',
        ],
        'room_type' => [
            'required' => 'Loại phòng là bắt buộc',
            'in'       => 'Loại phòng phải là 1, 2 hoặc 3',
        ],
        'price_min'   => [
            'numeric' => 'Giá phòng phải là số',
            'min'     => 'Giá phòng không được nhỏ hơn 0',
        ],
        'price_max'   => [
            'numeric' => 'Giá phòng phải là số',
            'min'     => 'Giá phòng không được nhỏ hơn 0',
        ],
        'area_min'    => [
            'integer' => 'Diện tích tối thiểu phải là số nguyên',
            'min'     => 'Diện tích tối thiểu phải lớn hơn hoặc bằng 1',
        ],
        'area_max'    => [
            'integer' => 'Diện tích tối đa phải là số nguyên',
            'min'     => 'Diện tích tối đa phải lớn hơn hoặc bằng 1',
        ],
        'area'        => [
            'required' => 'Diện tích là bắt buộc',
            'numeric'  => 'Diện tích phải là số > 0',
            'min'      => 'Diện tích phải lớn hơn hoặc bằng 1',
        ],
        'price'       => [
            'required' => 'Giá phòng là bắt buộc',
            'numeric'  => 'Giá phòng phải là số',
            'min'      => 'Giá phòng không được nhỏ hơn 0',
        ],
        'status'      => [
            'required' => 'Trạng thái là bắt buộc',
            'in'       => 'Trạng thái phải là 0 hoặc 1',
        ],
        'description' => [
            'string' => 'Mô tả phải là chuỗi ký tự hợp lệ',
            'max'    => 'Mô tả không được vượt quá 255 ký tự',
        ],
        'images' => [
            'required' => 'Hình ảnh là bắt buộc',
            'array'    => 'Hình ảnh phải là mảng',
            'min'      => 'Phải có ít nhất 1 hình ảnh',
        ],
        'images.*.image_url' => [
            'required' => 'URL hình ảnh là bắt buộc',
            'url'      => 'URL hình ảnh không hợp lệ',
            'max'      => 'URL hình ảnh không được vượt quá 255 ký tự',
        ],
        'images.*.image_type' => [
            'required' => 'Loại hình ảnh là bắt buộc',
            'integer'  => 'Loại hình ảnh phải là số nguyên',
            'between'  => 'Loại hình ảnh phải từ 0 đến 5',
        ],
        'images.*.sort' => [
            'required' => 'Thứ tự sắp xếp là bắt buộc',
            'integer'  => 'Thứ tự sắp xếp phải là số nguyên',
            'min'      => 'Thứ tự sắp xếp phải lớn hơn hoặc bằng 1',
        ],
        'amenities' => [
            'required' => 'Tiện nghi là bắt buộc',
            'array'    => 'Tiện nghi phải là mảng',
            'min'      => 'Phải có ít nhất 1 tiện nghi',
        ],
        'amenities.*' => [
            'required' => 'ID tiện nghi là bắt buộc',
            'integer'  => 'ID tiện nghi phải là số nguyên',
            'exists'   => 'ID tiện nghi không tồn tại',
        ],
        'services' => [
            'required' => 'Dịch vụ là bắt buộc',
            'array'    => 'Dịch vụ phải là mảng',
            'min'      => 'Phải có ít nhất 1 dịch vụ',
        ],
        'services.*' => [
            'required' => 'ID dịch vụ là bắt buộc',
            'integer'  => 'ID dịch vụ phải là số nguyên',
            'exists'   => 'ID dịch vụ không tồn tại',
        ],
        'prices' => [
            'required' => 'Giá phòng là bắt buộc',
            'array'    => 'Giá phòng phải là mảng',
            'min'      => 'Phải có ít nhất 1 giá phòng',
            'price_package_id' => [
                'required' => 'ID gói giá là bắt buộc',
                'integer'  => 'ID gói giá phải là số nguyên',
                'exists'   => 'ID gói giá đã tồn tại',
                'distinct' => 'ID gói giá không được trùng nhau',
            ],
            'unit' => [
                'required' => 'Đơn vị tính là bắt buộc',
                'string'   => 'Đơn vị tính phải là chuỗi ký tự',
                'in'       => 'Đơn vị tính phải là ngày, tuần, tháng hoặc năm',
            ],
            'unit_price' => [
                'required' => 'Đơn giá là bắt buộc',
                'numeric'  => 'Đơn giá phải là số',
                'min'      => 'Đơn giá phải lớn hơn hoặc bằng 0',
            ],
        ],
    ],
    'attributes' => [
        'building_id' => 'ID tòa nhà',
        'title'       => 'Tiêu đề',
        'room_number' => 'Số phòng',
        'deposit'     => 'Tiền cọc',
        'floor_number'  => 'Tầng phòng',
        'people'      => 'Số người ở',
        'room_type'   => 'Loại phòng',
        'price_min'   => 'Giá phòng tối thiểu',
        'price_max'   => 'Giá phòng tối đa',
        'area_min'    => 'Diện tích tối thiểu',
        'area_max'    => 'Diện tích tối đa',
        'area'        => 'Diện tích',
        'price'       => 'Giá phòng',
        'status'      => 'Trạng thái',
        'description' => 'Mô tả',
        'id'          => 'ID phòng',
        'images'      => 'Hình ảnh',
        'images.*.image_url' => 'URL hình ảnh',
        'images.*.image_type' => 'Loại hình ảnh',
        'images.*.sort' => 'Thứ tự sắp xếp',
        'amenities'   => 'Tiện nghi',
        'amenities.*' => 'ID tiện nghi',
        'services'    => 'Dịch vụ',
        'services.*'  => 'ID dịch vụ',
        'prices'      => 'Bảng giá',
        'prices.*.price_package_id' => 'ID gói giá',
        'prices.*.unit'             => 'Đơn vị tính',
        'prices.*.unit_price'       => 'Đơn giá',
    ],
    'messages'   => [
        'retrieved_successfully' => 'Lấy danh sách phòng thành công',
        'retrieved_failed'       => 'Không thể lấy danh sách phòng',
        'found_successfully'     => 'Tìm thấy phòng thành công',
        'not_found'              => 'Không tìm thấy phòng',
        'find_failed'            => 'Không thể tìm thấy phòng',
        'created_successfully'   => 'Tạo phòng thành công',
        'create_failed'          => 'Không thể tạo phòng',
        'save_prices_failed'    => 'Không thể lưu giá phòng',
        'save_images_failed'    => 'Không thể lưu hình ảnh phòng',
        'saved_amenities_failed' => 'Không thể lưu tiện nghi phòng',
        'updated_successfully'   => 'Cập nhật phòng thành công',
        'update_failed'          => 'Không thể cập nhật phòng',
        'deleted_successfully'   => 'Xóa phòng thành công',
        'delete_failed'          => 'Không thể xóa phòng',
    ],
];
