<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Building Image Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used during building image operations for various
    | messages that we need to display to the user. You are free to modify
    | these language lines according to your application's requirements.
    |
    */

    'validation' => [
        'building_id' => [
            'required' => 'ID tòa nhà là bắt buộc',
            'integer' => 'ID tòa nhà phải là số nguyên',
            'exists' => 'Tòa nhà được chọn không tồn tại',
        ],
        'id' => [
            'required' => 'ID ảnh tòa nhà là bắt buộc',
            'integer' => 'ID ảnh tòa nhà phải là số nguyên',
            'exists' => 'Ảnh tòa nhà không tồn tại',
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
        ],
        'ids' => [
            'required' => 'Danh sách ID ảnh là bắt buộc',
            'array' => 'Danh sách ID ảnh phải là mảng',
        ],
        'ids.*' => [
            'integer' => 'ID ảnh phải là số nguyên',
            'distinct' => 'ID ảnh phải là khác nhau',
            'exists' => 'ID ảnh không tồn tại',
        ],
    ],

    'messages' => [
        'retrieved_successfully' => 'Lấy danh sách ảnh tòa nhà thành công',
        'retrieved_failed' => 'Lấy danh sách ảnh tòa nhà thất bại',
        'found_successfully' => 'Lấy thông tin ảnh tòa nhà thành công',
        'not_found' => 'Ảnh tòa nhà không tồn tại',
        'find_failed' => 'Lấy thông tin ảnh tòa nhà thất bại',
        'created_successfully' => 'Tạo ảnh tòa nhà thành công',
        'create_failed' => 'Tạo ảnh tòa nhà thất bại',
        'updated_successfully' => 'Cập nhật ảnh tòa nhà thành công',
        'update_failed' => 'Cập nhật ảnh tòa nhà thất bại',
        'deleted_successfully' => 'Xóa ảnh tòa nhà thành công',
        'delete_failed' => 'Xóa ảnh tòa nhà thất bại',
        'sort_successfully' => 'Sắp xếp ảnh tòa nhà thành công',
        'sort_failed' => 'Sắp xếp ảnh tòa nhà thất bại',
    ],
];
