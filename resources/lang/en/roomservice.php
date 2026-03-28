<?php

return [
    'controller' => [
        'create_success' => 'Room service assigned successfully.',
        'update_success' => 'Room service updated successfully.',
        'delete_success' => 'Room service deleted successfully.',
        'validation_error' => 'Invalid data provided.',
    ],

    'validation' => [
        'id_required' => 'Room service ID is required.',
        'id_integer' => 'Room service ID must be an integer.',
        'id_exists' => 'Room service not found.',

        'room_id_required' => 'Room ID is required.',
        'room_id_integer' => 'Room ID must be an integer.',
        'room_id_exists' => 'Room does not exist.',

        'service_id_required' => 'Service ID is required.',
        'service_id_integer' => 'Service ID must be an integer.',
        'service_id_exists' => 'Service does not exist.',

        'is_included_required' => 'is_included field is required.',
        'is_included_boolean' => 'is_included must be a boolean value.',
    ],
    'message' => [
        'create_success' => 'Room service assigned successfully.',
        'create_error' => 'Failed to assign room service.',
        'update_success' => 'Room service updated successfully.',
        'update_error' => 'Failed to update room service.',
        'deleted_successfully' => 'Room service deleted successfully.',
        'delete_failed' => 'Failed to delete room service.',
    ],
    'atributes' => [
        'id' => 'Room Service ID',
        'room_id' => 'Room ID',
        'service_id' => 'Service ID',
        'is_included' => 'Is Included',
    ],
];
