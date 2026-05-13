<?php

return [
    'messages' => [
        'created_successfully'   => 'Đã chặn lịch phòng thành công.',
        'deleted_successfully'   => 'Đã gỡ chặn lịch phòng thành công.',
        'retrieved_successfully' => 'Lấy danh sách chặn lịch thành công.',
        'not_found'              => 'Không tìm thấy bản ghi chặn lịch.',
        'room_not_found'         => 'Không tìm thấy phòng tương ứng.',
        'unauthorized'           => 'Bạn không có quyền thực hiện thao tác này trên phòng đã chọn.',
        'invalid_date_range'     => 'Ngày kết thúc phải lớn hơn hoặc bằng ngày bắt đầu.',
        'conflict'               => 'Khoảng ngày bị trùng với booking đang hiệu lực hoặc bản ghi chặn khác.',
        'create_failed'          => 'Không thể chặn lịch, vui lòng thử lại.',
        'delete_failed'          => 'Không thể gỡ chặn lịch, vui lòng thử lại.',
    ],

    'validation' => [
        'room_id' => [
            'required' => 'ID phòng là bắt buộc.',
            'integer'  => 'ID phòng phải là số nguyên.',
            'exists'   => 'Phòng không tồn tại.',
        ],
        'start_date' => [
            'required' => 'Ngày bắt đầu là bắt buộc.',
            'date'     => 'Ngày bắt đầu không hợp lệ.',
        ],
        'end_date' => [
            'required'       => 'Ngày kết thúc là bắt buộc.',
            'date'           => 'Ngày kết thúc không hợp lệ.',
            'after_or_equal' => 'Ngày kết thúc phải sau hoặc bằng ngày bắt đầu.',
        ],
        'block_type' => [
            'required' => 'Loại chặn lịch là bắt buộc.',
            'string'   => 'Loại chặn lịch không hợp lệ.',
            'in'       => 'Loại chặn lịch chỉ nhận: maintenance, owner_use, off_market.',
        ],
        'reason' => [
            'required' => 'Lý do chặn lịch là bắt buộc.',
            'string'   => 'Lý do chặn lịch phải là chuỗi ký tự.',
            'max'      => 'Lý do chặn lịch không được vượt quá 255 ký tự.',
        ],
        'note' => [
            'string' => 'Ghi chú phải là chuỗi ký tự.',
            'max'    => 'Ghi chú không được vượt quá 1000 ký tự.',
        ],
    ],
];
