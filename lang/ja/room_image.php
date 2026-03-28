<?php

return [
    'validation' => [
        'room_id' => [
            'required' => 'ルームIDが必要です。',
            'integer' => 'ルームIDは整数である必要があります。',
            'exists' => 'ルームが存在しません。',
        ],
        'id' => [
            'required' => 'IDが必要です。',
            'integer' => 'IDは整数である必要があります。',
            'exists' => '画像が存在しません。',
        ],
        'image_url' => [
            'required' => '画像URLが必要です。',
            'string' => '画像URLは文字列である必要があります。',
            'max' => '画像URLは255文字を超えてはいけません。',
        ],
        'id_image_cloudinary' => [
            'required' => 'Cloudinary IDが必要です。',
            'string' => 'Cloudinary IDは文字列である必要があります。',
            'max' => 'Cloudinary IDは255文字を超えてはいけません。',
        ],
        'image_type' => [
            'required' => '画像タイプが必要です。',
            'integer' => '画像タイプは整数である必要があります。',
            'in' => '無効な画像タイプです。',
        ],
        'updates' => [
            'required' => '更新配列が必要です。',
            'array' => '更新は配列である必要があります。',
            'min' => '更新には少なくとも1つの項目が含まれている必要があります。',
            'array_required' => '更新配列が必要です。',
        ],
        'ids' => [
            'required' => 'ID配列が必要です。',
            'array' => 'IDは配列である必要があります。',
            'min' => 'IDには少なくとも1つの項目が含まれている必要があります。',
        ],
    ],
    'messages' => [
        'not_found' => '画像が見つかりません。',
        'retrieved_successfully' => '画像の取得に成功しました。',
        'retrieved_failed' => '画像の取得に失敗しました。',
        'create_failed' => '画像の作成に失敗しました。',
        'created_successfully' => '画像の作成に成功しました。',
        'update_failed' => '画像の更新に失敗しました。',
        'sort_updated_successfully' => '並べ替えの更新に成功しました。',
        'sort_update_failed' => '並べ替えの更新に失敗しました。',
        'delete_failed' => '画像の削除に失敗しました。',
        'find_failed' => '画像の検索に失敗しました。',
        'found_successfully' => '画像が見つかりました。',
        'room_mismatch' => 'ルームが一致しません。',
        'some_images_failed_to_save' => '一部の画像の保存に失敗しました。',
        'some_images_failed_to_update' => '一部の画像の更新に失敗しました。',
        'some_images_failed_to_delete' => '一部の画像の削除に失敗しました。',
    ],
];
