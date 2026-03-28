<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Room Image Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used during room image operations for various
    | messages that we need to display to the user. You are free to modify
    | these language lines according to your application's requirements.
    |
    */

    'validation' => [
        'room_id' => [
            'required' => 'ID phòng là bắt buộc',
            'integer' => 'ID phòng phải là số nguyên',
            'exists' => 'Phòng được chọn không tồn tại',
        ],
        'id' => [
            'required' => 'ID ảnh phòng là bắt buộc',
            'integer' => 'ID ảnh phòng phải là số nguyên',
            'exists' => 'Ảnh phòng không tồn tại',
        ],
        'image_url' => [
            'required' => 'URL ảnh là bắt buộc',
            'string' => 'URL ảnh phải là chuỗi hợp lệ',
            'max' => 'URL ảnh không được vượt quá 255 ký tự',
        ],
        'id_image_cloudinary' => [
            'required' => 'ID ảnh Cloudinary là bắt buộc',
            'string' => 'ID ảnh Cloudinary phải là chuỗi hợp lệ',
            'max' => 'ID ảnh Cloudinary không được vượt quá 255 ký tự',
        ],
        'image_type' => [
            'required' => 'Loại ảnh là bắt buộc',
            'integer' => 'Loại ảnh phải là số nguyên',
            'in' => 'Loại ảnh không hợp lệ',
        ],
        'image_id' => [
            'required' => 'ID ảnh là bắt buộc',
            'integer' => 'ID ảnh phải là số nguyên',
            'exists' => 'Ảnh không tồn tại',
        ],
        'image_id_a' => [
            'required' => 'ID ảnh A là bắt buộc',
            'integer' => 'ID ảnh A phải là số nguyên',
            'exists' => 'Ảnh A không tồn tại',
        ],
        'image_id_b' => [
            'required' => 'ID ảnh B là bắt buộc',
            'integer' => 'ID ảnh B phải là số nguyên',
            'exists' => 'Ảnh B không tồn tại',
        ],
        'ids' => [
            'required' => 'Danh sách ID là bắt buộc',
            'array' => 'Danh sách ID phải là mảng',
            'min' => 'Phải có ít nhất 1 ID',
        ],
    ],

    'messages' => [
        'retrieved_successfully' => 'Lấy danh sách ảnh phòng thành công',
        'retrieved_failed' => 'Lấy danh sách ảnh phòng thất bại',
        'found_successfully' => 'Lấy thông tin ảnh phòng thành công',
        'not_found' => 'Ảnh phòng không tồn tại',
        'find_failed' => 'Lấy thông tin ảnh phòng thất bại',
        'created_successfully' => 'Tạo ảnh phòng thành công',
        'create_failed' => 'Tạo ảnh phòng thất bại',
        'updated_successfully' => 'Cập nhật ảnh phòng thành công',
        'update_failed' => 'Cập nhật ảnh phòng thất bại',
        'deleted_successfully' => 'Xóa ảnh phòng thành công',
        'delete_failed' => 'Xóa ảnh phòng thất bại',
        'room_mismatch' => 'Ảnh không thuộc cùng phòng',
        'sort_updated_successfully' => 'Cập nhật thứ tự ảnh thành công',
        'sort_update_failed' => 'Cập nhật thứ tự ảnh thất bại',
    ],
];
