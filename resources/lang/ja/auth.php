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
    'success'                          => '成功',
    'error'                            => 'エラーが発生しました',
    'failed'                           => '認証情報が一致しません。',
    'password'                         => 'パスワードが正しくありません。',
    'throttle'                         => 'ログイン試行が多すぎます。:seconds秒後に再試行してください。',
    'unauthorized'                     => 'アクセス権がありません。',
    'not_permission'                   => 'アクセス権がありません。',
    'invalid_credentials'              => 'アカウントまたはパスワードが正しくありません。',
    'invalid_data'                     => '入力データが無効です。',
    'general_error'                    => '予期しないエラーが発生しました。もう一度お試しください。',
    'route_not_found'                  => '要求されたルートは存在しません。',
    'unauthenticated'                  => '認証されていません。有効なトークンを提供してください。',

    // Login
    'login_success'                    => 'ログインに成功しました。',
    'login_failed'                     => 'ログインに失敗しました。',
    'login_forbidden'                  => 'このアカウントはシステムにログインすることが許可されていません。',
    'subject_mail_login_information'   => 'ログイン情報',

    // Logout
    'logout_success'                   => 'ログアウトしました。',
    'logout_error'                     => 'ログアウトに失敗しました。',

    // Token
    'token_require'                    => 'トークンは必須です。',
    'token_required'                   => 'トークンは必須です。',
    'token_invalid'                    => 'トークンが無効です。',
    'token_expired'                    => 'トークンの有効期限が切れています。',
    'token_valid'                      => 'トークンは有効です。',
    'refresh_success'                  => 'トークンの更新に成功しました。',
    'refresh_error'                    => 'トークンの更新に失敗しました。',
    'refresh_token_created'            => 'リフレッシュトークンの作成に成功しました。',
    'create_refresh_token_failed'      => 'リフレッシュトークンの作成に失敗しました。',

    // Account
    'account_required'                 => 'メールまたは電話番号を入力してください。',
    'account_not_found'                => 'アカウントが見つかりません。',
    'account_not_verified'             => 'アカウントが確認されていません。',
    'account_blocked'                  => 'あなたのアカウントはブロックされています。管理者に連絡してください。',

    // Email
    'email_required'                   => 'メールを入力してください。',
    'email_email'                         => 'メールの形式が正しくありません。',
    'email_unique'                     => 'メールは既に存在します。',
    'email_max'                        => 'メールは255文字未満である必要があります。',
    'email_not_exists'                 => 'メールが存在しません。',
    'email_not_found'                  => 'メールが存在しません。',
    'success_email'                    => 'メール確認に成功しました！',
    'error_email'                      => '確認メールの送信中にエラーが発生しました。',
    'notification_email'               => '新しい確認メールが送信されました。受信トレイを確認してください！',
    'verify_email_invalid_token'       => 'メール確認用のトークンが無効です。',
    'verify_email_expired_token'       => 'メール確認用のトークンの有効期限が切れています。',
    'verify_email_success'             => 'メールの確認に成功しました。',
    'verify_email_failed'              => 'メールの確認に失敗しました。',
    'verify_email_already_verified'    => 'メールは既に確認されています。',
    'verify_email_token_not_found'     => 'メール確認用のトークンが存在しません。',
    'verify_email_token_expired'       => 'メール確認用のトークンの有効期限が切れています。',
    'verify_email_token_required'      => 'メール確認用のトークンは必須です。',

    // Password
    'password_required'                => 'パスワードを入力してください。',
    'password_string'                  => 'パスワードは文字列である必要があります。',
    'password_min'                     => 'パスワードは8文字以上である必要があります。',
    'password_confirmation_required'   => 'パスワード確認は必須です。',
    'password_confirmation_string'     => 'パスワード確認は文字列である必要があります。',
    'password_confirmation_not_match'  => 'パスワードが一致しません。',
    'send_mail_reset_password_success' => 'パスワードリセットのメールを送信しました。',
    'send_mail_reset_password_error'   => 'パスワードリセットのメール送信に失敗しました。',
    'subject_mail_reset_password'      => 'パスワードリセット',
    'reset_password_success'           => 'パスワードをリセットしました。',
    'reset_password_error'             => 'パスワードのリセットに失敗しました。',
    'reset_password_form_success'      => 'パスワードリセット情報の取得に成功しました。',
    'reset_password_form_error'        => 'パスワードリセット情報の取得に失敗しました。',
    'reset_password_invalid_token'     => 'パスワードリセット用のトークンが無効です。',
    'reset_password_token_valid'       => 'パスワードリセット用のトークンは有効です。',

    // Set Password
    'set_password_success'             => 'パスワードが正常に設定されました。新しいパスワードでログインできます。',
    'set_password_failed'              => 'パスワードの設定に失敗しました。',

    // Register
    'register_success'                 => '登録が成功しました。',
    'register_error'                   => '登録に失敗しました。',
    'register_api_error'               => '登録APIエラー。',

    // User Type
    'user_type_required'               => 'ユーザー種別は必須です。',
    'user_type_string'                 => 'ユーザー種別は文字列である必要があります。',
    'user_type_in'                     => '無効なユーザー種別です。',
    'user_type_max'                    => 'ユーザー種別は50文字未満である必要があります。',
    'user_type_not_found'              => 'ユーザー種別が存在しません。',
    'invalid_user_type'                => '無効なユーザー種別です。',
    'user_not_found'                   => 'ユーザー情報が無効です。',

    // Name
    'name_required'                    => '名前は必須です。',
    'name_string'                      => '名前は文字列である必要があります。',
    'name_max'                         => '名前は255文字未満である必要があります。',

    // Role
    'role_required'                    => '役割は必須です。',
    'role_in'                          => '無効な役割です。',

    // Phone
    'phone_required'                   => '電話番号は必須です。',
    'phone_string'                     => '電話番号は文字列である必要があります。',
    'phone_max'                        => '電話番号は20文字未満である必要があります。',

    // Avatar
    'avatar_string'                    => 'アバターは文字列である必要があります。',
    'avatar_max'                       => 'アバターは255文字未満である必要があります。',

    // Status
    'status_in'                        => '無効なステータスです。',

    // Permission
    'check_permission_success'         => '権限の確認に成功しました。',
    'check_permission_failed'          => '権限の確認に失敗しました。',
];
