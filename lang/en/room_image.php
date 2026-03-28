<?php

return [
    'validation' => [
        'room_id' => [
            'required' => 'Room ID is required.',
            'integer' => 'Room ID must be an integer.',
            'exists' => 'Room does not exist.',
        ],
        'id' => [
            'required' => 'ID is required.',
            'integer' => 'ID must be an integer.',
            'exists' => 'Image does not exist.',
        ],
        'image_url' => [
            'required' => 'Image URL is required.',
            'string' => 'Image URL must be a string.',
            'max' => 'Image URL must not exceed 255 characters.',
        ],
        'id_image_cloudinary' => [
            'required' => 'Cloudinary ID is required.',
            'string' => 'Cloudinary ID must be a string.',
            'max' => 'Cloudinary ID must not exceed 255 characters.',
        ],
        'image_type' => [
            'required' => 'Image type is required.',
            'integer' => 'Image type must be an integer.',
            'in' => 'Invalid image type.',
        ],
        'updates' => [
            'required' => 'Updates array is required.',
            'array' => 'Updates must be an array.',
            'min' => 'Updates must contain at least one item.',
            'array_required' => 'Updates array is required.',
        ],
        'ids' => [
            'required' => 'IDs array is required.',
            'array' => 'IDs must be an array.',
            'min' => 'IDs must contain at least one item.',
        ],
    ],
    'messages' => [
        'not_found' => 'Images not found.',
        'retrieved_successfully' => 'Images retrieved successfully.',
        'retrieved_failed' => 'Failed to retrieve images.',
        'create_failed' => 'Failed to create image.',
        'created_successfully' => 'Image created successfully.',
        'update_failed' => 'Failed to update image.',
        'sort_updated_successfully' => 'Sort updated successfully.',
        'sort_update_failed' => 'Failed to update sort.',
        'delete_failed' => 'Failed to delete image.',
        'find_failed' => 'Failed to find image.',
        'found_successfully' => 'Image found successfully.',
        'room_mismatch' => 'Room mismatch.',
        'some_images_failed_to_save' => 'Some images failed to save.',
        'some_images_failed_to_update' => 'Some images failed to update.',
        'some_images_failed_to_delete' => 'Some images failed to delete.',
    ],
];
