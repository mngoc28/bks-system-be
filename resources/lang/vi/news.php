<?php

return [
    'validation' => [
        'page' => [
            'integer' => 'Trang phải là số nguyên',
            'min' => 'Trang phải lớn hơn hoặc bằng 1',
        ],
        'per_page' => [
            'integer' => 'Số lượng mỗi trang phải là số nguyên',
            'min' => 'Số lượng mỗi trang phải lớn hơn hoặc bằng 1',
        ],
        'id' => [
            'required' => 'ID tin tức là bắt buộc',
            'exists' => 'ID tin tức không tồn tại',
            'integer' => 'ID tin tức phải là số nguyên',
        ],
        'published_at_start' => [
            'date' => 'Ngày bắt đầu phải là ngày hợp lệ',
        ],
        'published_at_end' => [
            'date' => 'Ngày kết thúc phải là ngày hợp lệ',
        ],
        'status' => [
            'integer' => 'Trạng thái phải là số nguyên',
        ],
        'user_name' => [
            'string' => 'Tên người dùng phải là chuỗi',
            'required' => 'Tên người dùng là bắt buộc',
        ],
        'title' => [
            'string' => 'Tiêu đề phải là chuỗi',
            'required' => 'Tiêu đề là bắt buộc',
        ],
        'content' => [
            'string' => 'Nội dung phải là chuỗi',
            'required' => 'Nội dung là bắt buộc',
        ],
        'sort_field' => [
            'string' => 'Trường sắp xếp phải là chuỗi',
        ],
        'sort_direction' => [
            'string' => 'Hướng sắp xếp phải là chuỗi',
        ],
        'check_time' => [
            'error' => 'Thời gian bắt đầu phải nhỏ hơn thời gian kết thúc',
        ],
        'user_id' => [
            'exists' => 'Người dùng không tồn tại',
            'integer' => 'Người dùng phải là số nguyên',
        ],
        'slug' => [
            'string' => 'Slug phải là chuỗi',
            'unique' => 'Slug đã tồn tại',
        ],
        'summary' => [
            'string' => 'Tóm tắt phải là chuỗi',
        ],
        'image_url' => [
            'string' => 'URL ảnh phải là chuỗi',
        ],
        'id_image_cloudinary' => [
            'string' => 'ID ảnh cloudinary phải là chuỗi',
        ],
        'published_at' => [
            'date' => 'Ngày phải là ngày hợp lệ',
        ],
        'status' => [
            'integer' => 'Trạng thái phải là số nguyên',
        ],
    ],
    'messages' => [
        'fetch_success' => 'Lấy danh sách tin tức thành công',
        'fetch_failed' => 'Lấy danh sách tin tức thất bại',
        'not_found' => 'Không tìm thấy tin tức',
        'create_success' => 'Tạo tin tức thành công',
        'create_failed' => 'Tạo tin tức thất bại',
        'update_success' => 'Cập nhật tin tức thành công',
        'update_failed' => 'Cập nhật tin tức thất bại',
        'delete_success' => 'Xóa tin tức thành công',
        'delete_failed' => 'Xóa tin tức thất bại',
        'get_latest_news_success' => 'Lấy tin tức mới nhất thành công',
        'get_latest_news_failed' => 'Lấy tin tức mới nhất thất bại',
    ]
];
