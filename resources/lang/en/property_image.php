<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Property image language lines
    |--------------------------------------------------------------------------
    |
    | Validation and messages for property (gallery) images — admin/partner APIs.
    |
    */

    'validation' => [
        'property_id' => [
            'required' => 'Property ID is required',
            'integer' => 'Property ID must be an integer',
            'exists' => 'Selected property does not exist',
        ],
        'id' => [
            'required' => 'Property image ID is required',
            'integer' => 'Property image ID must be an integer',
            'exists' => 'Property image does not exist',
        ],
        'image_url' => [
            'required' => 'Image URL is required',
            'string' => 'Image URL must be a valid string',
            'max' => 'Image URL must not exceed 255 characters',
            'url' => 'Image URL must be a valid URL',
        ],
        'id_image_cloudinary' => [
            'required' => 'Cloudinary image ID is required',
            'string' => 'Cloudinary image ID must be a valid string',
            'max' => 'Cloudinary image ID must not exceed 255 characters',
        ],
        'image_type' => [
            'required' => 'Image type is required',
            'integer' => 'Image type must be an integer',
            'in' => 'Image type is invalid',
        ],
        'ids' => [
            'required' => 'Image IDs are required',
            'array' => 'Image IDs must be an array',
        ],
        'ids.*' => [
            'integer' => 'Image ID must be an integer',
            'distinct' => 'Image IDs must be distinct',
            'exists' => 'Image ID does not exist',
        ],
    ],

    'messages' => [
        'retrieved_successfully' => 'Property images retrieved successfully',
        'retrieved_failed' => 'Failed to retrieve property images',
        'found_successfully' => 'Property image retrieved successfully',
        'not_found' => 'Property image not found',
        'find_failed' => 'Failed to retrieve property image',
        'created_successfully' => 'Property image created successfully',
        'create_failed' => 'Failed to create property image',
        'updated_successfully' => 'Property image updated successfully',
        'update_failed' => 'Failed to update property image',
        'deleted_successfully' => 'Property image deleted successfully',
        'delete_failed' => 'Failed to delete property image',
        'sort_successfully' => 'Property images sorted successfully',
        'sort_failed' => 'Failed to sort property images',
    ],
];
