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
        'file_invalid' => 'File is invalid',
        'file_format_not_supported' => 'File format is not supported. Only accepts: JPEG, PNG, JPG, GIF, WEBP',
        'file_size_too_large' => 'File size is too large. Maximum 10MB',
        'upload_success' => 'Image uploaded successfully',
        'upload_error' => 'Error uploading image: :error',
        'upload_multiple_success' => 'Successfully uploaded :count image(s)',
        'upload_multiple_failed' => 'No images were uploaded successfully',
        'delete_success' => 'Image deleted successfully',
        'delete_error' => 'Error deleting image: :error',
        'delete_multiple_success' => 'Successfully deleted :count image(s)',
        'delete_multiple_failed' => 'No images were deleted successfully',
        'delete_failed_with_id' => 'Failed to delete image with ID :id',
    ],

    'validation' => [
        'image' => [
            'required' => 'Please select an image to upload',
            'image' => 'File must be an image',
            'mimes' => 'Invalid image format. Only accepts: JPEG, JPG, PNG, GIF, WEBP',
            'max' => 'Maximum image size is 10MB',
        ],
        'images' => [
            'required' => 'Please select images to upload',
            'array' => 'Images must be an array',
            'min' => 'At least 1 image is required',
            'max' => 'Maximum 10 images per upload',
        ],
        'images.*' => [
            'required' => 'Each image is required',
            'image' => 'File must be an image',
            'mimes' => 'Invalid image format. Only accepts: JPEG, JPG, PNG, GIF, WEBP',
            'max' => 'Maximum image size is 10MB',
        ],
        'folder' => [
            'required' => 'Folder is required',
            'nullable' => 'Folder is optional',
            'string' => 'Folder must be a string',
            'max' => 'Folder name is too long',
        ],
        'public_id' => [
            'required' => 'Public ID is required',
            'string' => 'Public ID must be a string',
        ],
    ],
];
