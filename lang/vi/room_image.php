<?php

return [
    'validation' => [
        'room_id' => [
            'required' => 'ID phòng là bắt buộc.',
            'integer' => 'ID phòng phải là số nguyên.',
            'exists' => 'Phòng không tồn tại.',
        ],
        'id' => [
            'required' => 'ID là bắt buộc.',
            'integer' => 'ID phải là số nguyên.',
            'exists' => 'Ảnh không tồn tại.',
        ],
        'image_url' => [
            'required' => 'URL ảnh là bắt buộc.',
            'string' => 'URL ảnh phải là chuỗi.',
            'max' => 'URL ảnh không được vượt quá 255 ký tự.',
        ],
        'id_image_cloudinary' => [
            'required' => 'ID Cloudinary là bắt buộc.',
            'string' => 'ID Cloudinary phải là chuỗi.',
            'max' => 'ID Cloudinary không được vượt quá 255 ký tự.',
        ],
        'image_type' => [
            'required' => 'Loại ảnh là bắt buộc.',
            'integer' => 'Loại ảnh phải là số nguyên.',
            'in' => 'Loại ảnh không hợp lệ.',
        ],
        'updates' => [
            'required' => 'Mảng cập nhật là bắt buộc.',
            'array' => 'Cập nhật phải là mảng.',
            'min' => 'Cập nhật phải chứa ít nhất một mục.',
            'array_required' => 'Mảng cập nhật là bắt buộc.',
        ],
        'ids' => [
            'required' => 'Mảng IDs là bắt buộc.',
            'array' => 'IDs phải là mảng.',
            'min' => 'IDs phải chứa ít nhất một mục.',
        ],
    ],
    'messages' => [
        'not_found' => 'Không tìm thấy ảnh.',
        'retrieved_successfully' => 'Lấy ảnh thành công.',
        'retrieved_failed' => 'Lấy ảnh thất bại.',
        'create_failed' => 'Tạo ảnh thất bại.',
        'created_successfully' => 'Tạo ảnh thành công.',
        'update_failed' => 'Cập nhật ảnh thất bại.',
        'sort_updated_successfully' => 'Cập nhật sắp xếp thành công.',
        'sort_update_failed' => 'Cập nhật sắp xếp thất bại.',
        'delete_failed' => 'Xóa ảnh thất bại.',
        'find_failed' => 'Tìm ảnh thất bại.',
        'found_successfully' => 'Tìm thấy ảnh thành công.',
        'room_mismatch' => 'Phòng không khớp.',
        'some_images_failed_to_save' => 'Một số ảnh lưu thất bại.',
        'some_images_failed_to_update' => 'Một số ảnh cập nhật thất bại.',
        'some_images_failed_to_delete' => 'Một số ảnh xóa thất bại.',
    ],
];
