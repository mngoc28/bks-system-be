<?php

return [
    'validation_error' => 'Dữ liệu không hợp lệ.',
    'list_success' => 'Lấy danh sách bảo trì phòng thành công.',
    'detail_success' => 'Lấy chi tiết bảo trì phòng thành công.',
    'create_success' => 'Tạo lịch bảo trì phòng thành công.',
    'create_failed' => 'Tạo lịch bảo trì phòng thất bại.',
    'update_success' => 'Cập nhật bảo trì phòng thành công.',
    'update_failed' => 'Cập nhật bảo trì phòng thất bại.',
    'not_found' => 'Không tìm thấy phiếu bảo trì.',
    'room_not_found' => 'Không tìm thấy phòng.',
    'conflict_preview_success' => 'Kiểm tra xung đột lịch thành công.',
    'unauthorized' => 'Bạn không có quyền thao tác phiếu bảo trì này.',
    'invalid_transition' => 'Không thể chuyển trạng thái phiếu bảo trì theo yêu cầu.',
    'cancellation_reason_required' => 'Vui lòng nhập lý do hủy bảo trì.',
    'end_time_required_for_block' => 'Vui lòng nhập thời gian kết thúc khi khóa lịch bảo trì.',
    'calendar_conflict' => 'Khoảng thời gian bảo trì trùng với booking hoặc lịch chặn khác.',
    'block_sync_failed' => 'Không thể đồng bộ khóa lịch cho phiếu bảo trì.',
    'statuses' => [
        'planned' => 'Chờ xử lý',
        'in_progress' => 'Đang xử lý',
        'completed' => 'Đã hoàn thành',
        'cancelled' => 'Đã hủy',
    ],
    'types' => [
        'scheduled' => 'Bảo trì định kỳ',
        'emergency' => 'Sự cố khẩn cấp',
    ],
];
