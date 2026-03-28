<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Room Image Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used during room image operations for various
    | messages that we need to display to the user. You are free to modify
    | these language lines according to your application's requirements.
    |
    */

    'validation' => [
        'room_id' => [
            'required' => 'Room ID is required',
            'integer' => 'Room ID must be an integer',
            'exists' => 'The selected room does not exist',
        ],
        'id' => [
            'required' => 'Room image ID is required',
            'integer' => 'Room image ID must be an integer',
            'exists' => 'Room image does not exist',
        ],
        'image_url' => [
            'required' => 'Image URL is required',
            'string' => 'Image URL must be a valid string',
            'max' => 'Image URL may not be greater than 255 characters',
        ],
        'id_image_cloudinary' => [
            'required' => 'Cloudinary image ID is required',
            'string' => 'Cloudinary image ID must be a valid string',
            'max' => 'Cloudinary image ID may not be greater than 255 characters',
        ],
        'image_type' => [
            'required' => 'Image type is required',
            'integer' => 'Image type must be an integer',
            'in' => 'Image type is invalid',
        ],
        'image_id' => [
            'required' => 'Image ID is required',
            'integer' => 'Image ID must be an integer',
            'exists' => 'Image does not exist',
        ],
        'image_id_a' => [
            'required' => 'Image ID A is required',
            'integer' => 'Image ID A must be an integer',
            'exists' => 'Image A does not exist',
        ],
        'image_id_b' => [
            'required' => 'Image ID B is required',
            'integer' => 'Image ID B must be an integer',
            'exists' => 'Image B does not exist',
        ],
        'ids' => [
            'required' => 'List of IDs is required',
            'array' => 'List of IDs must be an array',
            'min' => 'At least 1 ID is required',
        ],
    ],

    'messages' => [
        'retrieved_successfully' => 'Room images retrieved successfully',
        'retrieved_failed' => 'Failed to retrieve room images',
        'found_successfully' => 'Room image found successfully',
        'not_found' => 'Room image not found',
        'find_failed' => 'Failed to find room image',
        'created_successfully' => 'Room image created successfully',
        'create_failed' => 'Failed to create room image',
        'updated_successfully' => 'Room image updated successfully',
        'update_failed' => 'Failed to update room image',
        'deleted_successfully' => 'Room image deleted successfully',
        'delete_failed' => 'Failed to delete room image',
        'room_mismatch' => 'Images do not belong to the same room',
        'sort_updated_successfully' => 'Image sort updated successfully',
        'sort_update_failed' => 'Failed to update image sort',
    ],
];
