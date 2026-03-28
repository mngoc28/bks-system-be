<?php

return [
    'messages' => [
        'get_list_failed' => 'Lấy danh sách thông tin đối tác thất bại!',
        'get_list_success' => 'Lấy danh sách thông tin đối tác thành công!',
        'get_list_error' => 'Đã xảy ra lỗi khi lấy danh sách thông tin đối tác!',
        'not_found' => 'Không tìm thấy thông tin đối tác!',
        'get_detail_success' => 'Lấy chi tiết thông tin đối tác thành công!',
        'find_error' => 'Đã xảy ra lỗi khi lấy thông tin đối tác!',
        'get_update_success' => 'Cập nhật thông tin đối tác thành công!',
        'update_error' => 'Đã xảy ra lỗi khi cập nhật thông tin đối tác!',
    ],

    'validation' => [
        'name' => [
            'max' => 'Tên không được vượt quá 255 ký tự.',
        ],
        'ward_name' => [
            'max' => 'Tên phường không được vượt quá 100 ký tự.',
        ],
        'province_name' => [
            'max' => 'Tên tỉnh không được vượt quá 100 ký tự.',
        ],
        'phone' => [
            'max' => 'Số điện thoại không được vượt quá 20 ký tự.',
            'regex' => 'Định dạng số điện thoại không hợp lệ.',
        ],
        'address' => [
            'max' => 'Địa chỉ không được vượt quá 500 ký tự.',
        ],
        'id' => [
            'required' => 'ID đối tác là bắt buộc.',
            'integer' => 'ID đối tác phải là số nguyên.',
            'exists' => 'ID đối tác không tồn tại.',
        ],
        'company_name' => [
            'max' => 'Tên công ty không được vượt quá 255 ký tự.',
        ],
        'website' => [
            'url' => 'Website phải là URL hợp lệ.',
            'max' => 'Website không được vượt quá 255 ký tự.',
        ],
        'description' => [
            'max' => 'Mô tả không được vượt quá 2000 ký tự.',
        ],
        'image_1' => [
            'image' => 'Ảnh 1 phải là file hình ảnh.',
            'mimes' => 'Ảnh 1 phải có định dạng: jpeg, png, jpg, webp.',
            'max' => 'Kích thước ảnh 1 không được vượt quá 5MB.',
        ],
        'image_2' => [
            'image' => 'Ảnh 2 phải là file hình ảnh.',
            'mimes' => 'Ảnh 2 phải có định dạng: jpeg, png, jpg, webp.',
            'max' => 'Kích thước ảnh 2 không được vượt quá 5MB.',
        ],
        'image_3' => [
            'image' => 'Ảnh 3 phải là file hình ảnh.',
            'mimes' => 'Ảnh 3 phải có định dạng: jpeg, png, jpg, webp.',
            'max' => 'Kích thước ảnh 3 không được vượt quá 5MB.',
        ],
    ],

    'attributes' => [
        'id' => 'ID đối tác',
        'name' => 'Tên đối tác',
        'user_name' => 'Tên người dùng',
        'ward_name' => 'Tên phường',
        'province_name' => 'Tên tỉnh',
        'phone' => 'Số điện thoại',
        'address' => 'Địa chỉ',
        'company_name' => 'Tên công ty',
        'website' => 'Website',
        'description' => 'Mô tả',
        'image_1' => 'Ảnh 1',
        'image_2' => 'Ảnh 2',
        'image_3' => 'Ảnh 3',
    ],
];
