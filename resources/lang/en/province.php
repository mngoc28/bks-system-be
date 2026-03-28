<?php

return [
    'validation' => [
        'name' => [
            'required' => 'The province/city name is required.',
            'string' => 'The province/city name must be a valid string.',
            'max' => 'The province/city name may not be greater than 100 characters.',
            'unique' => 'The province/city name already exists.',
        ],
        'name_en' => [
            'required' => 'The English province/city name is required.',
            'string' => 'The English province/city name must be a valid string.',
            'max' => 'The English province/city name may not be greater than 100 characters.',
        ],
        'id' => [
            'required' => 'The province/city ID is required.',
            'integer' => 'The province/city ID must be an integer.',
            'exists' => 'The specified province/city does not exist.',
            'unique' => 'The specified province/city ID has already been used.',
        ],
    ],
    'attributes' => [
        'id' => 'province/city ID',
        'name' => 'province/city name',
        'name_en' => 'English province/city name',
    ],
    'messages' => [
        'create_success' => 'Province/City created successfully.',
        'create_error' => 'Failed to create province/city.',
        'update_success' => 'Province/City updated successfully.',
        'update_error' => 'Failed to update province/city.',
        'show_success' => 'Province/City retrieved successfully.',
        'not_found' => 'Province/City not found.',
        'search_success' => 'Provinces/Cities retrieved successfully.',
        'search_failed' => 'Failed to retrieve provinces/cities.',
        'delete_success' => 'Province/City deleted successfully.',
        'get_all_provinces_types_success' => 'Provinces/Cities types retrieved successfully.',
        'get_all_provinces_types_failed' => 'Failed to retrieve provinces/cities types.',
    ],
];
