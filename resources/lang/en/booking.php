<?php

return [
    // validation messages
    'validation' => [
        'user_id' => [
            'required' => 'User ID is required',
            'integer'  => 'User ID must be an integer',
            'min'      => 'User ID must be greater than or equal to 1',
        ],
        'room_id' => [
            'required' => 'Room ID is required',
            'integer'  => 'Room ID must be an integer',
            'min'      => 'Room ID must be greater than or equal to 1',
        ],
        'start_date' => [
            'required' => 'Start date is required',
            'date'     => 'Start date is not valid',
        ],
        'end_date' => [
            'date'               => 'End date is not valid',
            'after_or_equal'     => 'End date must be after or equal to the start date',
            'after'              => 'End date must be after the start date',
        ],
        'status' => [
            'string' => 'Status must be a valid string',
            'in'     => 'Invalid status value',
        ],
        'note' => [
            'string' => 'Note must be a valid string',
        ],
        'name' => [
            'required' => 'Name is required',
            'string'   => 'Name must be a valid string',
            'max'      => 'Name must not exceed 255 characters',
        ],
        'email' => [
            'required' => 'Email is required',
            'email'    => 'Email must be a valid email address',
            'max'      => 'Email must not exceed 255 characters',
        ],
        'phone' => [
            'required' => 'Phone is required',
            'string'   => 'Phone must be a valid string',
            'max'      => 'Phone must not exceed 20 characters',
        ],
        'price_id' => [
            'required' => 'Price ID is required',
            'integer'  => 'Price ID must be an integer',
            'min'      => 'Price ID must be greater than or equal to 1',
        ],
    ],

    // Attributes
    'attributes' => [
        'user_id'    => 'User',
        'room_id'    => 'Room',
        'start_date' => 'Start Date',
        'end_date'   => 'End Date',
        'status'     => 'Status',
        'note'       => 'Note',
    ],

    // messages
    'messages' => [
        'invalid_data'           => 'Invalid data!',
        'user_not_found'         => 'User not found!',
        'room_not_found'         => 'Room not found!',
        'room_in_maintenance'    => 'Room is currently under maintenance and cannot be booked!',
        'retrieved_successfully' => 'Booking information retrieved successfully!',
        'retrieved_failed'       => 'Failed to retrieve booking information!',
        'not_found'              => 'This booking does not exist!',
        'found_successfully'     => 'Booking found successfully!',
        'find_failed'            => 'Failed to find booking!',
        'created_successfully'   => 'Booking created successfully!',
        'create_failed'          => 'Failed to create booking!',
        'updated_successfully'   => 'Booking updated successfully!',
        'update_failed'          => 'Failed to update booking!',
        'deleted_successfully'   => 'Booking deleted successfully!',
        'cancelled_successfully' => 'Booking cancelled successfully!',
        'delete_failed'          => 'Failed to delete booking!',
        'room_unavailable'       => 'The room is already booked for this period!',
        'booking_confirmed'      => 'Booking has been confirmed!',
        'booking_cancelled'      => 'Booking has been cancelled!',
        'booking_confirmed_or_cancelled' => 'Booking can only be confirmed or cancelled when pending!',
        'already_cancelled'      => 'This booking has already been cancelled!',
        'not_exist_price'        => 'Price :price_id does not exist for this room!',
        'completed_successfully' => 'Booking has been completed!',
        'unauthorized'           => 'You are not authorized to perform this action!',
        'unauthorized_staff_action' => 'Staff can only manage bookings for their assigned buildings!',
        'user_booking_created_successfully' => 'Booking created successfully! Please check your email for details.',
        'create_user_failed'     => 'Failed to create user for booking!',
        'room_in_private'        => 'This is a private room and cannot be booked!',
    ],
];
