<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Building Image Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used during building image operations for various
    | messages that we need to display to the user. You are free to modify
    | these language lines according to your application's requirements.
    |
    */

    'validation' => [
        'building_id' => [
            'required' => '建物IDは必須です',
            'integer' => '建物IDは整数である必要があります',
            'exists' => '選択された建物が存在しません',
        ],
        'id' => [
            'required' => '建物画像IDは必須です',
            'integer' => '建物画像IDは整数である必要があります',
            'exists' => '建物画像が存在しません',
        ],
        'image_url' => [
            'required' => '画像URLは必須です',
            'string' => '画像URLは有効な文字列である必要があります',
            'max' => '画像URLは255文字を超えることはできません',
        ],
        'id_image_cloudinary' => [
            'required' => 'Cloudinary画像IDは必須です',
            'string' => 'Cloudinary画像IDは有効な文字列である必要があります',
            'max' => 'Cloudinary画像IDは255文字を超えることはできません',
        ],
        'image_type' => [
            'required' => '画像タイプは必須です',
            'integer' => '画像タイプは整数である必要があります',
        ],
        'ids' => [
            'required' => '画像IDは必須です',
            'array' => '画像IDは配列である必要があります',
        ],
        'ids.*' => [
            'integer' => '画像IDは整数である必要があります',
            'distinct' => '画像IDは一意である必要があります',
            'exists' => '画像IDが存在しません',
        ],
    ],

    'messages' => [
        'retrieved_successfully' => '建物画像の取得に成功しました',
        'retrieved_failed' => '建物画像の取得に失敗しました',
        'found_successfully' => '建物画像の取得に成功しました',
        'not_found' => '建物画像が見つかりません',
        'find_failed' => '建物画像の取得に失敗しました',
        'created_successfully' => '建物画像の作成に成功しました',
        'create_failed' => '建物画像の作成に失敗しました',
        'updated_successfully' => '建物画像の更新に成功しました',
        'update_failed' => '建物画像の更新に失敗しました',
        'deleted_successfully' => '建物画像の削除に成功しました',
        'delete_failed' => '建物画像の削除に失敗しました',
        'sort_successfully' => '建物画像の並べ替えに成功しました',
        'sort_failed' => '建物画像の並べ替えに失敗しました',
    ],
];
