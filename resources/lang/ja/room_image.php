<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Room Image Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used during room image operations for various
    | messages that we need to display to the user. You are free to modify
    | these language lines according to your application's requirements.
    |
    */

    'validation' => [
        'room_id' => [
            'required' => 'ルームIDが必要です',
            'integer' => 'ルームIDは整数である必要があります',
            'exists' => '選択されたルームは存在しません',
        ],
        'id' => [
            'required' => 'ルーム画像IDが必要です',
            'integer' => 'ルーム画像IDは整数である必要があります',
            'exists' => 'ルーム画像が存在しません',
        ],
        'image_url' => [
            'required' => '画像URLが必要です',
            'string' => '画像URLは有効な文字列である必要があります',
            'max' => '画像URLは255文字を超えてはいけません',
        ],
        'id_image_cloudinary' => [
            'required' => 'Cloudinary画像IDが必要です',
            'string' => 'Cloudinary画像IDは有効な文字列である必要があります',
            'max' => 'Cloudinary画像IDは255文字を超えてはいけません',
        ],
        'image_type' => [
            'required' => '画像タイプが必要です',
            'integer' => '画像タイプは整数である必要があります',
            'in' => '画像タイプが無効です',
        ],
        'image_id' => [
            'required' => '画像IDが必要です',
            'integer' => '画像IDは整数である必要があります',
            'exists' => '画像が存在しません',
        ],
        'image_id_a' => [
            'required' => '画像ID Aが必要です',
            'integer' => '画像ID Aは整数である必要があります',
            'exists' => '画像Aが存在しません',
        ],
        'image_id_b' => [
            'required' => '画像ID Bが必要です',
            'integer' => '画像ID Bは整数である必要があります',
            'exists' => '画像Bが存在しません',
        ],
        'ids' => [
            'required' => 'IDリストが必要です',
            'array' => 'IDリストは配列である必要があります',
            'min' => '少なくとも1つのIDが必要です',
        ],
    ],

    'messages' => [
        'retrieved_successfully' => 'ルーム画像が正常に取得されました',
        'retrieved_failed' => 'ルーム画像の取得に失敗しました',
        'found_successfully' => 'ルーム画像が正常に見つかりました',
        'not_found' => 'ルーム画像が見つかりません',
        'find_failed' => 'ルーム画像の検索に失敗しました',
        'created_successfully' => 'ルーム画像が正常に作成されました',
        'create_failed' => 'ルーム画像の作成に失敗しました',
        'updated_successfully' => 'ルーム画像が正常に更新されました',
        'update_failed' => 'ルーム画像の更新に失敗しました',
        'deleted_successfully' => 'ルーム画像が正常に削除されました',
        'delete_failed' => 'ルーム画像の削除に失敗しました',
        'room_mismatch' => '画像が同じ部屋に属していません',
        'sort_updated_successfully' => '画像の並び替えが正常に更新されました',
        'sort_update_failed' => '画像の並び替えの更新に失敗しました',
    ],
];
