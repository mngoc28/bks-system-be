<?php

return [
    // validation messages
    'validation' => [
        'user_id' => [
            'required' => 'ユーザーIDは必須です。',
            'integer'  => 'ユーザーIDは整数でなければなりません。',
            'min'      => 'ユーザーIDは1以上でなければなりません。',
        ],
        'room_id' => [
            'required' => '部屋IDは必須です。',
            'integer'  => '部屋IDは整数でなければなりません。',
            'min'      => '部屋IDは1以上でなければなりません。',
        ],
        'start_date' => [
            'required' => '開始日は必須です。',
            'date'     => '開始日が無効です。',
        ],
        'end_date' => [
            'date'               => '終了日が無効です。',
            'after_or_equal'     => '終了日は開始日以降でなければなりません。',
            'after'              => '終了日は開始日より後の日付である必要があります。',
        ],
        'status' => [
            'string' => 'ステータスは有効な文字列でなければなりません。',
            'in'     => 'ステータスの値が無効です。',
        ],
        'note' => [
            'string' => 'メモは有効な文字列でなければなりません。',
        ],
        'name' => [
            'required' => '名前は必須です。',
            'string'   => '名前は有効な文字列である必要があります。',
            'max'      => '名前は255文字を超えてはいけません。',
        ],
        'email' => [
            'required' => 'メールアドレスは必須です。',
            'email'    => 'メールアドレスは有効なメールアドレスである必要があります。',
            'max'      => 'メールアドレスは255文字を超えてはいけません。',
        ],
        'phone' => [
            'required' => '電話番号は必須です。',
            'string'   => '電話番号は有効な文字列である必要があります。',
            'max'      => '電話番号は20文字を超えてはいけません。',
        ],
        'price_id' => [
            'required' => '価格IDは必須です。',
            'integer'  => '価格IDは整数でなければなりません。',
            'min'      => '価格IDは1以上でなければなりません。',
        ],
    ],

    // Attributes
    'attributes' => [
        'user_id'    => 'ユーザー',
        'room_id'    => '部屋',
        'start_date' => '開始日',
        'end_date'   => '終了日',
        'status'     => 'ステータス',
        'note'       => 'メモ',
    ],

    // messages
    'messages' => [
        'invalid_data'           => '無効なデータです！',
        'user_not_found'         => 'ユーザーが見つかりません！',
        'room_not_found'         => '部屋が見つかりません！',
        'room_in_maintenance'    => '部屋は現在メンテナンス中で予約できません！',
        'retrieved_successfully' => '予約情報の取得に成功しました！',
        'retrieved_failed'       => '予約情報の取得に失敗しました！',
        'not_found'              => 'この予約は存在しません！',
        'found_successfully'     => '予約が正常に見つかりました！',
        'find_failed'            => '予約の検索に失敗しました！',
        'created_successfully'   => '予約が正常に作成されました！',
        'create_failed'          => '予約の作成に失敗しました！',
        'updated_successfully'   => '予約が正常に更新されました！',
        'update_failed'          => '予約の更新に失敗しました！',
        'deleted_successfully'   => '予約が正常に削除されました！',
        'cancelled_successfully' => '予約が正常にキャンセルされました！',
        'delete_failed'          => '予約の削除に失敗しました！',
        'room_unavailable'       => 'この期間、部屋はすでに予約されています！',
        'booking_confirmed'      => '予約が確認されました！',
        'booking_cancelled'      => '予約がキャンセルされました！',
        'booking_confirmed_or_cancelled' => '予約は保留中のみ確認またはキャンセルできます！',
        'already_cancelled'      => 'この予約はすでにキャンセルされています！',
        'not_exist_price'        => 'この部屋の価格 :price_id は存在しません！',
        'completed_successfully' => '予約が完了しました！',
        'unauthorized'           => 'この操作を実行する権限がありません！',
        'unauthorized_staff_action' => 'スタッフは自分の担当する建物の予約のみ管理できます！',
        'user_booking_created_successfully' => '予約が正常に作成されました。確認の詳細についてはメールを確認してください。',
        'create_user_failed'     => '予約用のユーザーアカウント作成に失敗しました！',
        'room_in_private'        => 'これはプライベートルームであり、予約することはできません！',
    ],
];
