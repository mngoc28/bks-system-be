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
        'booking_code' => [
            'required' => 'Booking code is required',
            'string'   => 'Booking code is invalid',
            'max'      => 'Booking code is invalid',
            'regex'    => 'Booking code must look like RM-YYYY-XXXXXX (as in your confirmation email).',
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
        'already_confirmed'      => 'This booking has already been confirmed!',
        'confirm_blocked_pending_cancellation' => 'Cannot confirm while a guest cancellation request is '
            . 'pending partner review.',
        'confirmed_successfully' => 'Booking confirmed successfully!',
        'bulk_confirm_completed' => 'Bulk confirm processed.',
        'bulk_cancel_completed'  => 'Bulk cancel processed.',
        'cancellation_reason_required' => 'Please provide a cancellation reason.',
        'no_show_successfully'   => 'Booking marked as no-show!',
        'no_show_only_for_confirmed' => 'Only confirmed bookings can be marked as no-show!',
        'no_show_not_started_yet' => 'Cannot mark no-show before the check-in date!',
        'confirm_conflict'       => 'This booking conflicts with an active booking or room block.',
        'move_conflict'          => 'New date range/room conflicts with another booking or room block.',
        'move_only_for_active'   => 'Only pending or confirmed bookings can be moved.',
        'moved_successfully'     => 'Booking schedule updated successfully.',
        'not_exist_price'        => 'Price :price_id does not exist for this room!',
        'completed_successfully' => 'Booking has been completed!',
        'unauthorized'           => 'You are not authorized to perform this action!',
        'unauthorized_staff_action' => 'Staff can only manage bookings for their assigned properties!',
        'user_booking_created_successfully' => 'Booking created successfully! Please check your email for details.',
        'lookup_not_found'       => 'No booking matches the email and code entered.',
        'lookup_retrieved'       => 'Booking information retrieved.',
        'create_user_failed'     => 'Failed to create user for booking!',
        'room_in_private'        => 'This is a private room and cannot be booked!',
        'partner_cancel_blocked_pending_cancellation' => 'This booking has a pending guest cancellation request; '
            . 'use the cancellation inbox flow instead of direct cancel.',
    ],

    'sync_local' => [
        'success'                   => 'Local bookings merged successfully.',
        'forbidden_role'            => 'Only guest (user) accounts can sync local bookings here.',
        'fingerprint_mismatch'      => 'Fingerprint does not match room, dates, and account email.',
        'email_mismatch'            => 'Each item email must match your logged-in email.',
        'slot_fingerprint_conflict' => 'This stay already has different client metadata; cannot merge.',
        'price_not_found'           => 'No valid price package found for one of the rooms.',
        'create_failed'             => 'Could not create one of the bookings.',
        'note_auto'                 => 'Imported from device (T6 sync-local).',
    ],

    'bcp' => [
        'reasons_loaded'                => 'Cancellation reason codes loaded.',
        'reason_text_required'          => 'Please enter details for the selected reason code.',
        'stay_in_progress_no_cancel'    => 'Cancellation is not allowed after check-in, checkout, or no-show.',
        'direct_cancel_invalid_status'  => 'Direct cancel is only allowed while the booking is pending '
            . 'partner confirmation.',
        'cancel_request_invalid_status' => 'A cancellation request can only be submitted for confirmed bookings.',
        'cancel_request_already_pending'=> 'A cancellation request is already pending partner review.',
        'cancel_request_submitted'      => 'Cancellation request submitted. The partner will review it.',
        'cancel_request_cooldown'       => 'You recently submitted a cancellation request; please wait '
            . 'before sending another.',
        'idempotency_key_reuse'         => 'This idempotency key was already used; generate a new key.',
        'partner_inbox_loaded'          => 'Cancellation requests loaded.',
        'partner_request_approved'      => 'Cancellation request approved; booking is cancelled.',
        'partner_request_rejected'    => 'Cancellation request rejected; booking status restored.',
        'partner_request_not_pending' => 'This cancellation request is no longer pending.',
        'partner_booking_not_pending_cancellation' => 'Booking is not in pending cancellation state.',
    ],
];
