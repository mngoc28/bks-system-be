<?php

return [
    'validation_error' => 'Invalid data.',
    'list_success' => 'Maintenance list retrieved successfully.',
    'detail_success' => 'Maintenance detail retrieved successfully.',
    'create_success' => 'Maintenance record created successfully.',
    'create_failed' => 'Failed to create maintenance record.',
    'update_success' => 'Maintenance record updated successfully.',
    'update_failed' => 'Failed to update maintenance record.',
    'not_found' => 'Maintenance record not found.',
    'room_not_found' => 'Room not found.',
    'conflict_preview_success' => 'Calendar conflict preview retrieved successfully.',
    'unauthorized' => 'You are not allowed to access this maintenance record.',
    'invalid_transition' => 'Invalid maintenance status transition.',
    'cancellation_reason_required' => 'Cancellation reason is required.',
    'end_time_required_for_block' => 'End time is required when blocking the calendar.',
    'calendar_conflict' => 'Maintenance period conflicts with an existing booking or block.',
    'block_sync_failed' => 'Failed to sync calendar block for maintenance.',
    'statuses' => [
        'planned' => 'Planned',
        'in_progress' => 'In progress',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
    ],
    'types' => [
        'scheduled' => 'Scheduled maintenance',
        'emergency' => 'Emergency issue',
    ],
];
