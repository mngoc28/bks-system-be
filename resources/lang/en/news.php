<?php

return [
    'validation' => [
        'page' => [
            'integer' => 'Page must be an integer',
            'min' => 'Page must be at least 1',
        ],
        'per_page' => [
            'integer' => 'Per page must be an integer',
            'min' => 'Per page must be at least 1',
        ],
        'id' => [
            'required' => 'ID is required',
            'exists' => 'ID does not exist',
            'integer' => 'ID must be an integer',
        ],
        'published_at_start' => [
            'date' => 'Published at start must be a valid date',
        ],
        'published_at_end' => [
            'date' => 'Published at end must be a valid date',
        ],
        'status' => [
            'integer' => 'Status must be an integer',
        ],
        'user_name' => [
            'string' => 'User name must be a string',
            'required' => 'User name is required',
        ],
        'title' => [
            'string' => 'Title must be a string',
            'required' => 'Title is required',
        ],
        'content' => [
            'string' => 'Content must be a string',
            'required' => 'Content is required',
        ],
        'sort_field' => [
            'string' => 'Sort field must be a string',
        ],
        'sort_direction' => [
            'string' => 'Sort direction must be a string',
        ],
        'check_time' => [
            'error' => 'Start time must be less than end time',
        ],
        'user_id' => [
            'exists' => 'User does not exist',
            'integer' => 'User must be an integer',
        ],
        'slug' => [
            'string' => 'Slug must be a string',
            'unique' => 'Slug already exists',
        ],
        'summary' => [
            'string' => 'Summary must be a string',
        ],
        'image_url' => [
            'string' => 'Image URL must be a string',
        ],
        'id_image_cloudinary' => [
            'string' => 'Cloudinary ID must be a string',
        ],
        'published_at' => [
            'date' => 'Published at must be a valid date',
        ],
        'status' => [
            'integer' => 'Status must be an integer',
        ],
    ],
    'messages' => [
        'fetch_success' => 'News fetched successfully',
        'fetch_failed' => 'Failed to fetch news',
        'not_found' => 'News not found',
        'create_success' => 'News created successfully',
        'create_failed' => 'Failed to create news',
        'update_success' => 'News updated successfully',
        'update_failed' => 'Failed to update news',
        'delete_success' => 'News deleted successfully',
        'delete_failed' => 'Failed to delete news',
        'get_latest_news_success' => 'Get latest news successfully',
        'get_latest_news_failed' => 'Failed to get latest news',
    ]
];
