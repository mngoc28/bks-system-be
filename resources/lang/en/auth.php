<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used during authentication for various
    | messages that we need to display to the user. You are free to modify
    | these language lines according to your application's requirements.
    |
    */

    // General
    'success'                          => 'Success',
    'error'                            => 'An error occurred',
    'failed'                           => 'These credentials do not match our records.',
    'password'                         => 'The provided password is incorrect.',
    'throttle'                         => 'Too many login attempts. Please try again in :seconds seconds.',
    'unauthorized'                     => 'You do not have access.',
    'not_permission'                   => 'You do not have permission to access.',
    'invalid_credentials'              => 'Invalid account or password.',
    'invalid_data'                     => 'Invalid input data.',
    'general_error'                    => 'An unexpected error occurred. Please try again.',
    'route_not_found'                  => 'The requested route does not exist.',
    'unauthenticated'                  => 'You are unauthenticated. Please provide a valid token.',

    // Login
    'login_success'                    => 'Login successful.',
    'login_failed'                     => 'Login failed.',
    'login_forbidden'                  => 'This account is not allowed to log in to the system.',
    'subject_mail_login_information'   => 'Login information',

    // Logout
    'logout_success'                   => 'Logout successful.',
    'logout_error'                     => 'Logout failed.',

    // Token
    'token_require'                    => 'Token is required.',
    'token_required'                   => 'Token is required.',
    'token_invalid'                    => 'Token is invalid.',
    'token_expired'                    => 'Token has expired.',
    'token_valid'                      => 'Token is valid.',
    'refresh_success'                  => 'Token refreshed successfully.',
    'refresh_error'                    => 'Token refresh failed.',
    'refresh_token_created'            => 'Refresh token created successfully.',
    'create_refresh_token_failed'      => 'Failed to create refresh token.',

    // Account
    'account_required'                 => 'Please enter an email or phone number.',
    'account_not_found'                => 'Account not found.',
    'account_not_verified'             => 'Account is not verified.',
    'account_blocked'                  => 'Your account has been blocked.' .
        'Please contact the administrator for assistance.',

    // Email
    'email_required'                   => 'Please enter an email.',
    'email_email'                      => 'The email format is invalid.',
    'email_unique'                     => 'The email has already been taken.',
    'email_max'                        => 'Email must be less than 255 characters.',
    'email_not_exists'                 => 'Email does not exist.',
    'email_not_found'                  => 'Email does not exist.',
    'success_email'                    => 'Email verification successful!',
    'error_email'                      => 'An error occurred while sending verification email.',
    'notification_email'               => 'A new verification email has been sent. Please check your inbox!',
    'verify_email_invalid_token'       => 'Email verification token is invalid.',
    'verify_email_expired_token'       => 'Email verification token has expired.',
    'verify_email_success'             => 'Email verified successfully.',
    'verify_email_failed'              => 'Email verification failed.',
    'verify_email_already_verified'    => 'Email has already been verified.',
    'verify_email_token_not_found'     => 'Email verification token does not exist.',
    'verify_email_token_expired'       => 'Email verification token has expired.',
    'verify_email_token_required'      => 'Email verification token is required.',

    // Password
    'password_required'                => 'Please enter a password.',
    'password_string'                  => 'Password must be a string.',
    'password_min'                     => 'Password must be at least 8 characters.',
    'password_confirmation_required'   => 'Password confirmation is required.',
    'password_confirmation_string'     => 'Password confirmation must be a string.',
    'password_confirmation_not_match'  => 'Password does not match.',
    'send_mail_reset_password_success' => 'Password reset email sent successfully.',
    'send_mail_reset_password_error'   => 'Failed to send password reset email.',
    'subject_mail_reset_password'      => 'Reset Password',
    'reset_password_success'           => 'Password reset successfully.',
    'reset_password_error'             => 'Password reset failed.',
    'reset_password_form_success'      => 'Retrieved password reset information successfully.',
    'reset_password_form_error'        => 'Failed to retrieve password reset information.',
    'reset_password_invalid_token'     => 'Reset password token is invalid.',
    'reset_password_token_valid'       => 'Reset password token is valid.',

    // Set Password
    'set_password_success'             => 'Password set successfully. You can now log in with your new password.',
    'set_password_failed'              => 'Failed to set password.',

    // Register
    'register_success'                 => 'Registration successful.',
    'register_error'                   => 'Registration failed.',
    'register_api_error'               => 'Registration API error.',

    // User Type
    'user_type_required'               => 'User type is required.',
    'user_type_string'                 => 'User type must be a string.',
    'user_type_in'                     => 'Invalid user type.',
    'user_type_max'                    => 'User type must be less than 50 characters.',
    'user_type_not_found'              => 'User type does not exist.',
    'invalid_user_type'                => 'Invalid user type.',
    'user_not_found'                   => 'Invalid user information.',

    // Name
    'name_required'                    => 'Name is required.',
    'name_string'                      => 'Name must be a string.',
    'name_max'                         => 'Name must be less than 255 characters.',

    // Role
    'role_required'                    => 'Role is required.',
    'role_in'                          => 'Role is invalid. Only accepts: admin, partner, user.',

    // Phone
    'phone_required'                   => 'Phone is required.',
    'phone_string'                     => 'Phone must be a string.',
    'phone_max'                        => 'Phone must not exceed 20 characters.',

    // Avatar
    'avatar_string'                    => 'Avatar must be a string.',
    'avatar_max'                       => 'Avatar must be less than 255 characters.',

    // Status
    'status_in'                        => 'Status is invalid. Only accepts: 0 (pending), 1 (active), 2 (block).',

    // Permission
    'check_permission_success'         => 'Check permission successful.',
    'check_permission_failed'          => 'Check permission failed.',
];
