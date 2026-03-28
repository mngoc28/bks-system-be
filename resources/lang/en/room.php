<?php

return [
    /*
        |--------------------------------------------------------------------------
            | Room Language Lines
            |--------------------------------------------------------------------------
            |
            | The following language lines are used during room operations for various
            | messages that we need to display to the user. You are free to modify
            | these language lines according to your application's requirements.
            |
            */
    'validation' => [
        'building_id' => [
            'exists'   => 'The selected building does not exist',
            'integer'  => 'Building ID must be an integer',
            'required' => 'Building ID is required',
        ],
        'title' => [
            'required' => 'Room title is required',
            'string'   => 'Title must be a string',
            'max'      => 'Title must not exceed 100 characters',
        ],
        'room_number' => [
            'integer'  => 'Room number must be an integer',
            'numeric'  => 'Room number must be a number',
            'required' => 'Room number is required',
        ],
        'deposit' => [
            'numeric' => 'Deposit must be a number',
            'min'     => 'Deposit cannot be less than 0',
        ],
        'floor_number' => [
            'required' => 'Floor number is required',
            'integer'  => 'Floor number must be an integer',
            'min'      => 'Floor number cannot be less than 0',
        ],
        'people' => [
            'required' => 'Number of people is required',
            'integer'  => 'Number of people must be an integer',
            'min'      => 'Number of people must be at least 1',
        ],
        'room_type' => [
            'required' => 'Room type is required',
            'in'       => 'Room type must be 1, 2, or 3',
        ],
        'price_min'   => [
            'numeric' => 'Room price must be a number',
            'min'     => 'Room price cannot be less than 0',
        ],
        'price_max'   => [
            'numeric' => 'Room price must be a number',
            'min'     => 'Room price cannot be less than 0',
        ],
        'area_min'    => [
            'integer' => 'Minimum area must be an integer',
            'min'     => 'Minimum area must be at least 1',
        ],
        'area_max'    => [
            'integer' => 'Maximum area must be an integer',
            'min'     => 'Maximum area must be at least 1',
        ],
        'area'        => [
            'required' => 'Area is required',
            'integer'  => 'Area must be an integer',
            'min'      => 'Area must be at least 1',
        ],
        'price'       => [
            'required' => 'Price is required',
            'numeric'  => 'Price must be a number',
            'min'      => 'Price cannot be less than 0',
        ],
        'status'      => [
            'required' => 'Status is required',
            'in'       => 'Status must be 0 or 1',
        ],
        'description' => [
            'string' => 'Description must be a valid string',
            'max'    => 'Description must not exceed 255 characters',
        ],
        'images' => [
            'required' => 'Images are required',
            'array'    => 'Images must be an array',
            'min'      => 'At least 1 image is required',
        ],
        'images.*.image_url' => [
            'required' => 'Image URL is required',
            'url'      => 'Image URL is invalid',
            'max'      => 'Image URL must not exceed 255 characters',
        ],
        'images.*.image_type' => [
            'required' => 'Image type is required',
            'integer'  => 'Image type must be an integer',
            'between'  => 'Image type must be between 0 and 5',
        ],
        'images.*.sort' => [
            'required' => 'Sort order is required',
            'integer'  => 'Sort order must be an integer',
            'min'      => 'Sort order must be at least 1',
        ],
        'amenities' => [
            'required' => 'Amenities are required',
            'array'    => 'Amenities must be an array',
            'min'      => 'At least 1 amenity is required',
        ],
        'amenities.*' => [
            'required' => 'Amenity ID is required',
            'integer'  => 'Amenity ID must be an integer',
            'exists'   => 'Amenity ID does not exist',
        ],
        'services' => [
            'required' => 'Services are required',
            'array'    => 'Services must be an array',
            'min'      => 'At least 1 service is required',
        ],
        'services.*' => [
            'required' => 'Service ID is required',
            'integer'  => 'Service ID must be an integer',
            'exists'   => 'Service ID does not exist',
        ],
        'prices' => [
            'required' => 'Prices are required',
            'array'    => 'Prices must be an array',
            'min'      => 'At least 1 price is required',
            'price_package_id' => [
            'required' => 'Price package ID is required',
            'integer'  => 'Price package ID must be an integer',
            'exists'   => 'Price package ID does not exist',
            ],
            'unit' => [
            'required' => 'Unit is required',
            'string'   => 'Unit must be a string',
            'in'       => 'Unit must be day or month',
            ],
            'unit_price' => [
            'required' => 'Unit price is required',
            'numeric'  => 'Unit price must be a number',
            'min'      => 'Unit price must be greater than or equal to 0',
            ],
        ],
    ],
    'attributes' => [
        'building_id' => 'building ID',
        'title'       => 'title',
        'room_number' => 'room number',
        'deposit'     => 'deposit',
        'floor_number'  => 'floor number',
        'people'      => 'number of people',
        'room_type'   => 'room type',
        'price_min'   => 'minimum price',
        'price_max'   => 'maximum price',
        'area_min'    => 'minimum area',
        'area_max'    => 'maximum area',
        'area'        => 'area',
        'price'       => 'price',
        'status'      => 'status',
        'description' => 'description',
        'id'          => 'room ID',
        'images'      => 'images',
        'images.*.image_url' => 'image URL',
        'images.*.image_type' => 'image type',
        'images.*.sort' => 'sort order',
        'amenities'   => 'amenities',
        'amenities.*' => 'amenity ID',
        'services'    => 'services',
        'services.*'  => 'service ID',
        'prices'      => 'prices',
        'prices.*.price_package_id' => 'package ID',
        'prices.*.unit' => 'unit',
        'prices.*.unit_price' => 'unit price',
    ],
    'messages'   => [
        'retrieved_successfully' => 'Rooms retrieved successfully',
        'retrieved_failed'       => 'Failed to retrieve rooms',
        'found_successfully'     => 'Room found successfully',
        'not_found'              => 'Room not found',
        'find_failed'            => 'Failed to find room',
        'created_successfully'   => 'Room created successfully',
        'create_failed'          => 'Failed to create room',
        'save_prices_failed'     => 'Failed to save room prices',
        'save_images_failed'     => 'Failed to save room images',
        'save_amenities_failed'  => 'Failed to save room amenities',
        'updated_successfully'   => 'Room updated successfully',
        'update_failed'          => 'Failed to update room',
        'deleted_successfully'   => 'Room deleted successfully',
        'delete_failed'          => 'Failed to delete room',
    ],
];
