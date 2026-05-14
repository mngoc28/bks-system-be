<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Ngôn ngữ ảnh cơ sở (property images)
    |--------------------------------------------------------------------------
    */

    'validation' => [
        'property_id' => [
            'required' => 'ID cơ sở là bắt buộc',
            'integer' => 'ID cơ sở phải là số nguyên',
            'exists' => 'Cơ sở được chọn không tồn tại',
        ],
        'id' => [
            'required' => 'ID ảnh cơ sở là bắt buộc',
            'integer' => 'ID ảnh cơ sở phải là số nguyên',
            'exists' => 'Ảnh cơ sở không tồn tại',
        ],
        'image_url' => [
            'required' => 'URL ảnh là bắt buộc',
            'string' => 'URL ảnh phải là chuỗi hợp lệ',
            'max' => 'URL ảnh không được vượt quá 255 ký tự',
            'url' => 'URL ảnh không hợp lệ',
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
        'ids' => [
            'required' => 'Danh sách ID ảnh là bắt buộc',
            'array' => 'Danh sách ID ảnh phải là mảng',
        ],
        'ids.*' => [
            'integer' => 'ID ảnh phải là số nguyên',
            'distinct' => 'ID ảnh phải khác nhau',
            'exists' => 'ID ảnh không tồn tại',
        ],
    ],

    'messages' => [
        'retrieved_successfully' => 'Lấy danh sách ảnh cơ sở thành công',
        'retrieved_failed' => 'Lấy danh sách ảnh cơ sở thất bại',
        'found_successfully' => 'Lấy thông tin ảnh cơ sở thành công',
        'not_found' => 'Ảnh cơ sở không tồn tại',
        'find_failed' => 'Lấy thông tin ảnh cơ sở thất bại',
        'created_successfully' => 'Tạo ảnh cơ sở thành công',
        'create_failed' => 'Tạo ảnh cơ sở thất bại',
        'updated_successfully' => 'Cập nhật ảnh cơ sở thành công',
        'update_failed' => 'Cập nhật ảnh cơ sở thất bại',
        'deleted_successfully' => 'Xóa ảnh cơ sở thành công',
        'delete_failed' => 'Xóa ảnh cơ sở thất bại',
        'sort_successfully' => 'Sắp xếp ảnh cơ sở thành công',
        'sort_failed' => 'Sắp xếp ảnh cơ sở thất bại',
    ],
];
