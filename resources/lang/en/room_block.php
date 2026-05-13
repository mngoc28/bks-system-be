<?php

return [
    'messages' => [
        'created_successfully'   => 'Room block created successfully.',
        'deleted_successfully'   => 'Room block removed successfully.',
        'retrieved_successfully' => 'Room blocks retrieved successfully.',
        'not_found'              => 'Room block not found.',
        'room_not_found'         => 'Room not found.',
        'unauthorized'           => 'You are not allowed to manage room blocks for this room.',
        'invalid_date_range'     => 'End date must be greater than or equal to start date.',
        'conflict'               => 'Selected date range conflicts with an active booking or another room block.',
        'create_failed'          => 'Failed to create room block, please try again.',
        'delete_failed'          => 'Failed to delete room block, please try again.',
    ],

    'validation' => [
        'room_id' => [
            'required' => 'Room id is required.',
            'integer'  => 'Room id must be an integer.',
            'exists'   => 'Room does not exist.',
        ],
        'start_date' => [
            'required' => 'Start date is required.',
            'date'     => 'Start date is invalid.',
        ],
        'end_date' => [
            'required'       => 'End date is required.',
            'date'           => 'End date is invalid.',
            'after_or_equal' => 'End date must be on/after start date.',
        ],
        'block_type' => [
            'required' => 'Block type is required.',
            'string'   => 'Block type must be a string.',
            'in'       => 'Block type must be one of: maintenance, owner_use, off_market.',
        ],
        'reason' => [
            'required' => 'Reason is required.',
            'string'   => 'Reason must be a string.',
            'max'      => 'Reason must be at most 255 characters.',
        ],
        'note' => [
            'string' => 'Note must be a string.',
            'max'    => 'Note must be at most 1000 characters.',
        ],
    ],
];
