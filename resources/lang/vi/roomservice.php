<?php

return [

    'controller' => [
        'create_success' => 'Gán dịch vụ cho phòng thành công.',
        'update_success' => 'Cập nhật dịch vụ phòng thành công.',
        'delete_success' => 'Xóa dịch vụ phòng thành công.',
        'validation_error' => 'Dữ liệu không hợp lệ.',
    ],

    'validation' => [
        'id_required' => 'ID dịch vụ phòng là bắt buộc.',
        'id_integer' => 'ID dịch vụ phòng phải là số nguyên.',
        'id_exists' => 'Dịch vụ phòng không tồn tại.',

        'room_id_required' => 'Mã phòng là bắt buộc.',
        'room_id_integer' => 'Mã phòng phải là số nguyên.',
        'room_id_exists' => 'Phòng không tồn tại.',

        'service_id_required' => 'Mã dịch vụ là bắt buộc.',
        'service_id_integer' => 'Mã dịch vụ phải là số nguyên.',
        'service_id_exists' => 'Dịch vụ không tồn tại.',

        'is_included_required' => 'Trạng thái dịch vụ là bắt buộc.',
        'is_included_boolean' => 'Trạng thái dịch vụ phải là đúng hoặc sai (true/false).',
    ],
    'message' => [
        'create_success' => 'Gán dịch vụ cho phòng thành công.',
        'create_error' => 'Gán dịch vụ cho phòng thất bại.',
        'update_success' => 'Cập nhật dịch vụ phòng thành công.',
        'update_error' => 'Cập nhật dịch vụ phòng thất bại.',
        'deleted_successfully' => 'Xóa dịch vụ phòng thành công.',
        'delete_failed' => 'Xóa dịch vụ phòng thất bại.',
    ],
    'atributes' => [
        'id' => 'ID dịch vụ phòng',
        'room_id' => 'Mã phòng',
        'service_id' => 'Mã dịch vụ',
        'is_included' => 'Trạng thái dịch vụ',
    ],
];
