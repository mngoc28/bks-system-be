<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Property language lines (domain: property / cơ sở lưu trú)
    |--------------------------------------------------------------------------
    |
    | Replaces legacy file key "property". Used for property CRUD messages and validation.
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
        'ward_name'        => [
            'max' => 'Ward name must not exceed 255 characters',
        ],
        'province_name'    => [
            'max' => 'Province name must not exceed 255 characters',
        ],
        'name'             => [
            'required' => 'Property name is required',
            'max'      => 'Property name must not exceed 255 characters',
            'unique'   => 'Property name already exists',
            'string'   => 'Property name must be a valid string',
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
        'property_type'    => [
            'integer' => 'Structure kind must be an integer',
            'in'      => 'Structure kind must be one of: 1, 2, 3, 4, 5, 6, 7, 8, 9',
        ],
        'property_type_id' => [
            'required' => 'Property type is required',
            'integer'  => 'Property type must be an integer',
            'exists'   => 'Selected property type does not exist',
        ],
        'rent_category'    => [
            'required' => 'Rent category is required',
            'integer'  => 'Rent category must be an integer',
            'in'       => 'Rent category is invalid',
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
            'required'     => 'Property ID is required',
            'integer'      => 'Property ID must be an integer',
            'exists'       => 'Property ID does not exist',
            'has_rooms'    => 'Cannot delete a property that still has rooms',
            'has_bookings' => 'Cannot delete a property that still has bookings',
        ],
    ],
    'attributes' => [
        'user_id'          => 'user ID',
        'province_id'      => 'province ID',
        'ward_id'          => 'ward ID',
        'ward_name'        => 'ward name',
        'province_name'    => 'province name',
        'name'             => 'property name',
        'address_detail'   => 'address detail',
        'number_of_floors' => 'number of floors',
        'number_of_units'  => 'number of units',
        'year_built'       => 'year built',
        'property_type'    => 'structure kind',
        'property_type_id' => 'property type',
        'rent_category'    => 'rent category',
        'area'             => 'area',
        'description'      => 'description',
        'created_by'       => 'creator',
        'updated_by'       => 'updater',
        'id'               => 'property ID',
    ],
    'messages'   => [
        'retrieved_successfully'                 => 'Properties retrieved successfully',
        'retrieved_failed'                       => 'Failed to retrieve properties',
        'found_successfully'                     => 'Property retrieved successfully',
        'not_found'                              => 'Property not found',
        'find_failed'                            => 'Failed to retrieve property',
        'created_successfully'                   => 'Property created successfully',
        'create_failed'                          => 'Failed to create property',
        'updated_successfully'                   => 'Property updated successfully',
        'update_failed'                          => 'Failed to update property',
        'deleted_successfully'                   => 'Property deleted successfully',
        'delete_failed'                          => 'Failed to delete property',
        'bookings_retrieved_successfully'        => 'Bookings retrieved successfully for this property',
        'bookings_retrieved_failed'              => 'Failed to retrieve bookings for this property',
        'property_types_retrieved_successfully'  => 'Property structure kinds retrieved successfully',
        'property_types_retrieved_failed'        => 'Failed to retrieve property structure kinds',
    ],

    /*
    | Display labels for PropertyStructureKind enum. Use __('property.structure_kind.N').
    */
    'structure_kind' => [
        1 => 'Apartment property',
        2 => 'Property / tower',
        3 => 'Villa',
        4 => 'Townhouse',
        5 => 'Serviced apartment',
        6 => 'Boarding house / homestay',
        7 => 'Hotel',
        8 => 'Office',
        9 => 'Other',
    ],
];
