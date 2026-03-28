<?php

return [
    /*
        |--------------------------------------------------------------------------
            | Building Language Lines
            |--------------------------------------------------------------------------
            |
            | The following language lines are used during building operations for various
            | messages that we need to display to the user. You are free to modify
            | these language lines according to your application's requirements.
            |
            */
    'validation' => [
        'user_id'          => [
            'required' => 'User ID is required',
            'integer'  => 'User ID must be an integer',
            'exists'   => 'Selected user does not exist',
        ],
        'province_id'      => [
            'required' => 'Province ID is required',
            'integer'  => 'Province ID must be an integer',
            'exists'   => 'Selected province does not exist',
        ],
        'ward_id'          => [
            'required' => 'Ward ID is required',
            'integer'  => 'Ward ID must be an integer',
            'exists'   => 'Selected ward does not exist',
        ],
        'name'             => [
            'required' => 'Building name is required',
            'max'      => 'Building name must not exceed 255 characters',
            'unique'   => 'Building name already exists',
            'string'   => 'Building name must be a valid string',
        ],
        'address_detail'   => [
            'max'    => 'Address detail must not exceed 255 characters',
            'string' => 'Address detail must be a valid string',
        ],
        'number_of_floors' => [
            'integer' => 'Number of floors must be an integer',
            'min'     => 'Number of floors must be at least 1',
        ],
        'number_of_units'  => [
            'integer' => 'Number of units must be an integer',
            'min'     => 'Number of units must be at least 0',
        ],
        'year_built'       => [
            'integer' => 'Year built must be an integer',
            'min'     => 'Year built must be at least 1900',
            'max'     => 'Year built must not exceed ' . (date('Y') + 10),
        ],
        'building_type'    => [
            'integer' => 'Building type must be an integer',
            'in'      => 'Building type must be one of: 1, 2, 3, 4, 5, 6, 7, 8, 9',
        ],
        'area'             => [
            'numeric' => 'Area must be a number',
            'min'     => 'Area must be at least 0',
        ],
        'description'      => [
            'string' => 'Description must be a valid string',
        ],
        'created_by'       => [
            'integer' => 'Creator ID must be an integer',
            'exists'  => 'Selected creator does not exist',
        ],
        'updated_by'       => [
            'integer' => 'Updater ID must be an integer',
            'exists'  => 'Selected updater does not exist',
        ],
        'id'               => [
            'required'     => 'Building ID is required',
            'integer'      => 'Building ID must be an integer',
            'exists'       => 'Building ID does not exist',
            'has_rooms'    => 'Cannot delete building that has rooms',
            'has_bookings' => 'Cannot delete building that has bookings',
        ],
    ],
    'attributes' => [
        'user_id'          => 'user ID',
        'province_id'      => 'province ID',
        'ward_id'          => 'ward ID',
        'name'             => 'building name',
        'address_detail'   => 'address detail',
        'number_of_floors' => 'number of floors',
        'number_of_units'  => 'number of units',
        'year_built'       => 'year built',
        'building_type'    => 'building type',
        'area'             => 'area',
        'description'      => 'description',
        'created_by'       => 'creator',
        'updated_by'       => 'updater',
        'id'               => 'building ID',
    ],
    'messages'   => [
        'retrieved_successfully'                              => 'Buildings retrieved successfully',
        'retrieved_failed'                                    => 'Failed to retrieve buildings',
        'found_successfully'                                  => 'Building retrieved successfully',
        'not_found'                                           => 'Building not found',
        'find_failed'                                         => 'Failed to retrieve building',
        'created_successfully'                                => 'Building created successfully',
        'create_failed'                                       => 'Failed to create building',
        'updated_successfully'                                => 'Building updated successfully',
        'update_failed'                                       => 'Failed to update building',
        'deleted_successfully'                                => 'Building deleted successfully',
        'delete_failed'                                       => 'Failed to delete building',
        'bookings_retrieved_successfully'                     => 'Bookings retrieved successfully for this building',
        'bookings_retrieved_failed'                           => 'Failed to retrieve bookings for this building',
        'buildings_types_retrieved_successfully'               => 'Building types retrieved successfully',
        'buildings_types_retrieved_failed'                    => 'Failed to retrieve building types',
        'all_buildings_bookings_count_retrieved_successfully' =>
        'Bookings count retrieved successfully for all buildings',
        'all_buildings_bookings_count_retrieved_failed'       => 'Failed to retrieve bookings count for all buildings',
    ],
];
