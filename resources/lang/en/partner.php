<?php

return [
    'messages' => [
        'get_list_failed' => 'Get list partner information failed!',
        'get_list_success' => 'Get list partner information success!',
        'get_list_error' => 'Get list partner information error!',
        'not_found' => 'Partner information is not found!',
        'get_detail_success' => 'Get detail partner information success!',
        'find_error' => 'Get detail partner information error!',
        'get_update_success' => 'Update partner information success!',
        'update_error' => 'Update partner information error!',
    ],
    'validation' => [
        'name' => [
            'max' => 'Name cannot exceed 255 characters',
        ],
        'ward_name' => [
            'max' => 'Ward name cannot exceed 100 characters',
        ],
        'province_name' => [
            'max' => 'Province name cannot exceed 100 characters',
        ],
        'phone' => [
            'max' => 'Phone cannot exceed 20 characters',
            'regex' => 'Phone format is invalid',
        ],
        'address' => [
            'max' => 'Address cannot exceed 500 characters',
        ],
        'id' => [
            'required' => 'Partner ID is required',
            'integer' => 'Partner ID must be an integer',
            'exists' => 'Partner ID does not exist',
        ],
        'company_name' => [
            'max' => 'Company name cannot exceed 255 characters',
        ],
        'website' => [
            'url' => 'Website must be a valid URL',
            'max' => 'Website cannot exceed 255 characters',
        ],
        'description' => [
            'max' => 'Description cannot exceed 2000 characters',
        ],
        'image_1' => [
            'image' => 'Image 1 must be an image file',
            'mimes' => 'Image 1 must be a file of type: jpeg, png, jpg, webp',
            'max' => 'Image 1 size cannot exceed 5MB',
        ],
        'image_2' => [
            'image' => 'Image 2 must be an image file',
            'mimes' => 'Image 2 must be a file of type: jpeg, png, jpg, webp',
            'max' => 'Image 2 size cannot exceed 5MB',
        ],
        'image_3' => [
            'image' => 'Image 3 must be an image file',
            'mimes' => 'Image 3 must be a file of type: jpeg, png, jpg, webp',
            'max' => 'Image 3 size cannot exceed 5MB',
        ],
    ],
    'attributes' => [
        'id' => 'Partner ID',
        'name' => 'Partner name',
        'user_name' => 'User name',
        'ward_name' => 'Ward name',
        'province_name' => 'Province name',
        'phone' => 'Phone',
        'address' => 'Address',
        'company_name' => 'Company name',
        'website' => 'Website',
        'description' => 'Description',
        'image_1' => 'Image 1',
        'image_2' => 'Image 2',
        'image_3' => 'Image 3',
    ],
];
