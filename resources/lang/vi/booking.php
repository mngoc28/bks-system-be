<?php

return [
    // validation messages
    'validation' => [
        'user_id' => [
            'required' => 'ID người dùng là bắt buộc',
            'integer'  => 'ID người dùng phải là số nguyên',
            'min'      => 'ID người dùng phải lớn hơn hoặc bằng 1',
        ],
        'room_id' => [
            'required' => 'ID phòng là bắt buộc',
            'integer'  => 'ID phòng phải là số nguyên',
            'min'      => 'ID phòng phải lớn hơn hoặc bằng 1',
        ],
        'start_date' => [
            'required' => 'Ngày bắt đầu là bắt buộc',
            'date'     => 'Ngày bắt đầu không hợp lệ',
        ],
        'end_date' => [
            'date'               => 'Ngày kết thúc không hợp lệ',
            'after'               => 'Ngày kết thúc phải sau ngày bắt đầu',
            'after_or_equal'     => 'Ngày kết thúc phải sau hoặc bằng ngày bắt đầu',
        ],
        'status' => [
            'string' => 'Trạng thái phải là chuỗi ký tự hợp lệ',
            'in'     => 'Trạng thái không hợp lệ',
        ],
        'note' => [
            'string' => 'Ghi chú phải là chuỗi ký tự hợp lệ',
        ],
        'name' => [
            'required' => 'Tên là bắt buộc',
            'string'   => 'Tên phải là chuỗi ký tự hợp lệ',
            'max'      => 'Tên không được vượt quá 255 ký tự',
        ],
        'email' => [
            'required' => 'Email là bắt buộc',
            'email'    => 'Email phải là địa chỉ email hợp lệ',
            'max'      => 'Email không được vượt quá 255 ký tự',
        ],
        'phone' => [
            'required' => 'Số điện thoại là bắt buộc',
            'string'   => 'Số điện thoại phải là chuỗi ký tự hợp lệ',
            'max'      => 'Số điện thoại không được vượt quá 20 ký tự',
        ],
        'price_id' => [
            'required' => 'ID giá là bắt buộc',
            'integer'  => 'ID giá phải là số nguyên',
            'min'      => 'ID giá phải lớn hơn hoặc bằng 1',
        ],
    ],

    // Attributes
    'attributes' => [
        'user_id'    => 'Người đặt phòng',
        'room_id'    => 'Phòng',
        'start_date' => 'Ngày bắt đầu',
        'end_date'   => 'Ngày kết thúc',
        'status'     => 'Trạng thái',
        'note'       => 'Ghi chú',
    ],

    // messages
    'messages' => [
        'invalid_data'           => 'Dữ liệu không hợp lệ!',
        'user_not_found'         => 'Người dùng không tồn tại!',
        'room_not_found'         => 'Phòng không tồn tại!',
        'room_in_private'        => 'Phòng này là phòng private!',
        'retrieved_successfully' => 'Lấy thông tin đặt phòng thành công!',
        'retrieved_failed'       => 'Lấy thông tin đặt phòng thất bại!',
        'not_found'              => 'Đặt phòng này không tồn tại!',
        'found_successfully'     => 'Tìm thấy đặt phòng thành công!',
        'find_failed'            => 'Tìm đặt phòng thất bại!',
        'created_successfully'   => 'Tạo đặt phòng thành công!',
        'create_failed'          => 'Tạo đặt phòng thất bại!',
        'updated_successfully'   => 'Cập nhật đặt phòng thành công!',
        'update_failed'          => 'Cập nhật đặt phòng thất bại!',
        'changed_status_successfully' => 'Thay đổi trạng thái đặt phòng thành công!',
        'change_status_failed'   => 'Thay đổi trạng thái đặt phòng thất bại!',
        'deleted_successfully'   => 'Xóa đặt phòng thành công!',
        'cancelled_successfully' => 'Đã hủy đặt phòng thành công!',
        'delete_failed'          => 'Hủy đặt phòng thất bại!',
        'room_unavailable'      => 'Phòng đã được đặt trong khoảng thời gian này!',
        'booking_confirmed'      => 'Đặt phòng đã được xác nhận!',
        'booking_cancelled'      => 'Đặt phòng đã được hủy!',
        'booking_confirmed_or_cancelled' => 'Đặt phòng chỉ có thể được xác nhận hoặc hủy khi ở trạng thái chờ xử lý!',
        'already_cancelled'      => 'Đặt phòng này đã được hủy!',
        'not_exist_price'        => 'Không tồn tại giá có id = :price_id cho phòng này!',
        'completed_successfully' => 'Đặt phòng đã hoàn thành. Hiện tại phòng trống!',
        'unauthorized'    => 'Không được phép!',
        'unauthorized_staff_action' => 'Nhân viên không thể thao tác với booking của tòa nhà khác!',
        'user_booking_created_successfully' => "Đặt phòng thành công! Vui lòng kiểm tra email để xem chi tiết.",
        'create_user_failed'     => 'Tạo tài khoản người dùng cho đặt phòng thất bại!',
        'room_in_private'        => 'Đây là phòng riêng và không thể đặt phòng!',
    ],
];
