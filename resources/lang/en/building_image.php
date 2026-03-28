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
            'required' => 'Building ID is required',
            'integer' => 'Building ID must be an integer',
            'exists' => 'Selected building does not exist',
        ],
        'id' => [
            'required' => 'Building image ID is required',
            'integer' => 'Building image ID must be an integer',
            'exists' => 'Building image does not exist',
        ],
        'image_url' => [
            'required' => 'Image URL is required',
            'string' => 'Image URL must be a valid string',
            'max' => 'Image URL must not exceed 255 characters',
        ],
        'id_image_cloudinary' => [
            'required' => 'Cloudinary image ID is required',
            'string' => 'Cloudinary image ID must be a valid string',
            'max' => 'Cloudinary image ID must not exceed 255 characters',
        ],
        'image_type' => [
            'required' => 'Image type is required',
            'integer' => 'Image type must be an integer',
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
        'retrieved_successfully' => 'Building images retrieved successfully',
        'retrieved_failed' => 'Failed to retrieve building images',
        'found_successfully' => 'Building image retrieved successfully',
        'not_found' => 'Building image not found',
        'find_failed' => 'Failed to retrieve building image',
        'created_successfully' => 'Building image created successfully',
        'create_failed' => 'Failed to create building image',
        'updated_successfully' => 'Building image updated successfully',
        'update_failed' => 'Failed to update building image',
        'deleted_successfully' => 'Building image deleted successfully',
        'delete_failed' => 'Failed to delete building image',
        'sort_successfully' => 'Building images sorted successfully',
        'sort_failed' => 'Failed to sort building images',
    ],
];
