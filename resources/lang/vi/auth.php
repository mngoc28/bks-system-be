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
    'success'                          => 'Thành công',
    'error'                            => 'Có lỗi xảy ra',
    'failed'                           => 'Thông tin đăng nhập không chính xác.',
    'password'                         => 'Mật khẩu không chính xác.',
    'throttle'                         => 'Đăng nhập quá nhiều lần. Vui lòng thử lại sau :seconds giây.',
    'unauthorized'                     => 'Bạn không có quyền truy cập.',
    'not_permission'                   => 'Bạn không có quyền truy cập.',
    'invalid_credentials'              => 'Tài khoản hoặc mật khẩu không chính xác.',
    'invalid_data'                     => 'Dữ liệu đầu vào không hợp lệ.',
    'general_error'                    => 'Đã xảy ra lỗi không mong muốn. Vui lòng thử lại.',
    'route_not_found'                  => 'Route được yêu cầu không tồn tại.',
    'unauthenticated'                  => 'Bạn chưa được xác thực. Vui lòng cung cấp token hợp lệ.',

    // Login
    'login_success'                    => 'Đăng nhập thành công.',
    'login_failed'                     => 'Đăng nhập thất bại.',
    'login_forbidden'                  => 'Tài khoản này không được phép đăng nhập vào hệ thống.',
    'subject_mail_login_information'   => 'Thông tin đăng nhập',

    // Logout
    'logout_success'                   => 'Đăng xuất thành công.',
    'logout_error'                     => 'Đăng xuất thất bại.',

    // Token
    'token_require'                    => 'Token là bắt buộc.',
    'token_required'                   => 'Token là bắt buộc.',
    'token_invalid'                    => 'Token không hợp lệ.',
    'token_expired'                    => 'Token đã hết hạn.',
    'token_valid'                      => 'Token hợp lệ.',
    'refresh_success'                  => 'Làm mới token thành công.',
    'refresh_error'                    => 'Làm mới token thất bại.',
    'refresh_token_created'            => 'Tạo refresh token thành công.',
    'create_refresh_token_failed'      => 'Tạo refresh token thất bại.',

    // Account
    'account_required'                 => 'Vui lòng nhập email hoặc số điện thoại.',
    'account_not_found'                => 'Tài khoản không tồn tại.',
    'account_not_verified'             => 'Tài khoản chưa được xác thực.',
    'account_blocked'                  => 'Tài khoản của bạn đã bị khóa.' .
        ' Vui lòng liên hệ quản trị viên để được hỗ trợ.',

    // Email
    'email_required'                   => 'Vui lòng nhập email.',
    'email_email'                      => 'Email không đúng định dạng.',
    'email_unique'                     => 'Email đã tồn tại.',
    'email_max'                        => 'Email phải có ít hơn 255 ký tự.',
    'email_not_exists'                 => 'Email không tồn tại.',
    'email_not_found'                  => 'Email không tồn tại.',
    'success_email'                    => 'Xác thực email thành công!',
    'error_email'                      => 'Đã có lỗi xảy ra khi gửi email xác thực.',
    'notification_email'               => 'Email xác thực mới đã được gửi. Vui lòng kiểm tra hòm thư!',
    'verify_email_invalid_token'       => 'Token xác thực email không hợp lệ.',
    'verify_email_expired_token'       => 'Token xác thực email đã hết hạn.',
    'verify_email_success'             => 'Xác thực email thành công.',
    'verify_email_failed'              => 'Xác thực email thất bại.',
    'verify_email_already_verified'    => 'Email đã được xác thực.',
    'verify_email_token_not_found'     => 'Token xác thực email không tồn tại.',
    'verify_email_token_expired'       => 'Token xác thực email đã hết hạn.',
    'verify_email_token_required'      => 'Token xác thực email là bắt buộc.',

    // Password
    'password_required'                => 'Vui lòng nhập mật khẩu.',
    'password_string'                  => 'Mật khẩu phải là chuỗi.',
    'password_min'                     => 'Mật khẩu phải có ít nhất 8 ký tự.',
    'password_confirmation_required'   => 'Mật khẩu xác nhận là bắt buộc.',
    'password_confirmation_string'     => 'Mật khẩu xác nhận phải là chuỗi.',
    'password_confirmation_not_match'  => 'Mật khẩu không khớp.',
    'send_mail_reset_password_success' => 'Gửi email đặt lại mật khẩu thành công.',
    'send_mail_reset_password_error'   => 'Gửi email đặt lại mật khẩu thất bại.',
    'subject_mail_reset_password'      => 'Đặt lại mật khẩu',
    'reset_password_success'           => 'Đặt lại mật khẩu thành công.',
    'reset_password_error'             => 'Đặt lại mật khẩu thất bại.',
    'reset_password_form_success'      => 'Lấy thông tin đặt lại mật khẩu thành công.',
    'reset_password_form_error'        => 'Lấy thông tin đặt lại mật khẩu thất bại.',
    'reset_password_invalid_token'     => 'Token đặt lại mật khẩu không hợp lệ.',
    'reset_password_token_valid'       => 'Token đặt lại mật khẩu hợp lệ.',

    // Set Password
    'set_password_success'             => 'Đặt mật khẩu thành công. Bạn có thể đăng nhập bằng mật khẩu mới.',
    'set_password_failed'              => 'Đặt mật khẩu thất bại.',

    // Register
    'register_success'                 => 'Đăng ký thành công.',
    'register_error'                   => 'Đăng ký thất bại.',
    'register_api_error'               => 'Lỗi api đăng ký.',

    // User Type
    'user_type_required'               => 'Loại tài khoản là bắt buộc.',
    'user_type_string'                 => 'Loại tài khoản phải là chuỗi.',
    'user_type_in'                     => 'Loại tài khoản không hợp lệ.',
    'user_type_max'                    => 'Loại tài khoản phải có ít hơn 50 ký tự.',
    'user_type_not_found'              => 'Loại tài khoản không tồn tại.',
    'invalid_user_type'                => 'Loại tài khoản không hợp lệ.',
    'user_not_found'                   => 'Thông tin người dùng không hợp lệ.',

    // Name
    'name_required'                    => 'Tên là bắt buộc.',
    'name_string'                      => 'Tên phải là chuỗi.',
    'name_max'                         => 'Tên phải có ít hơn 255 ký tự.',

    // Role
    'role_required'                    => 'Vai trò là bắt buộc.',
    'role_in'                          => 'Vai trò không hợp lệ.',

    // Phone
    'phone_required'                   => 'Số điện thoại là bắt buộc.',
    'phone_string'                     => 'Số điện thoại phải là chuỗi.',
    'phone_max'                        => 'Số điện thoại phải có ít hơn 20 ký tự.',

    // Avatar
    'avatar_string'                    => 'Avatar phải là chuỗi.',
    'avatar_max'                       => 'Avatar phải có ít hơn 255 ký tự.',

    // Status
    'status_in'                        => 'Trạng thái không hợp lệ.',

    // Permission
    'check_permission_success'         => 'Kiểm tra quyền truy cập thành công.',
    'check_permission_failed'          => 'Kiểm tra quyền truy cập thất bại.',
];
