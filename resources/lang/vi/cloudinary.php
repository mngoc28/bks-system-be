<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Cloudinary Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used during cloudinary operations for various
    | messages that we need to display to the user. You are free to modify
    | these language lines according to your application's requirements.
    |
    */

    'messages' => [
        'file_invalid' => 'File không hợp lệ',
        'file_format_not_supported' => 'Định dạng file không được hỗ trợ. Chỉ chấp nhận: JPEG, PNG, JPG, GIF, WEBP',
        'file_size_too_large' => 'Kích thước file quá lớn. Tối đa 10MB',
        'upload_success' => 'Upload ảnh thành công',
        'upload_error' => 'Lỗi khi upload ảnh: :error',
        'upload_multiple_success' => 'Upload thành công :count ảnh',
        'upload_multiple_failed' => 'Không có ảnh nào được upload thành công',
        'delete_success' => 'Xóa ảnh thành công',
        'delete_error' => 'Lỗi khi xóa ảnh: :error',
        'delete_multiple_success' => 'Xóa thành công :count ảnh',
        'delete_multiple_failed' => 'Không có ảnh nào được xóa thành công',
        'delete_failed_with_id' => 'Không thể xóa ảnh với ID :id',
    ],

    'validation' => [
        'image' => [
            'required' => 'Vui lòng chọn ảnh để upload',
            'image' => 'File phải là hình ảnh',
            'mimes' => 'Định dạng ảnh không hợp lệ. Chỉ chấp nhận: JPEG, JPG, PNG, GIF, WEBP',
            'max' => 'Kích thước ảnh tối đa là 10MB',
        ],
        'images' => [
            'required' => 'Vui lòng chọn ảnh để upload',
            'array' => 'Images phải là mảng',
            'min' => 'Phải có ít nhất 1 ảnh',
            'max' => 'Tối đa 10 ảnh mỗi lần upload',
        ],
        'images.*' => [
            'required' => 'Mỗi ảnh là bắt buộc',
            'image' => 'File phải là hình ảnh',
            'mimes' => 'Định dạng ảnh không hợp lệ. Chỉ chấp nhận: JPEG, JPG, PNG, GIF, WEBP',
            'max' => 'Kích thước ảnh tối đa là 10MB',
        ],
        'folder' => [
            'required' => 'Folder là bắt buộc',
            'nullable' => 'Folder là tùy chọn',
            'string' => 'Folder phải là chuỗi',
            'max' => 'Tên folder quá dài',
        ],
        'public_id' => [
            'required' => 'Public ID là bắt buộc',
            'string' => 'Public ID phải là chuỗi',
        ],
    ],
];
