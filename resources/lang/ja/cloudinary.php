<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Cloudinary Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used during cloudinary operations for various
    | messages that we need to display to the user. You are free to modify
    | these language lines according to your application's requirements.
    |
    */

    'messages' => [
        'file_invalid' => 'ファイルが無効です',
        'file_format_not_supported' => 'ファイル形式がサポートされていません。JPEG、PNG、JPG、GIF、WEBPのみ受け付けます',
        'file_size_too_large' => 'ファイルサイズが大きすぎます。最大10MB',
        'upload_success' => '画像のアップロードに成功しました',
        'upload_error' => '画像のアップロード中にエラーが発生しました: :error',
        'upload_multiple_success' => ':count枚の画像のアップロードに成功しました',
        'upload_multiple_failed' => 'アップロードに成功した画像がありません',
        'delete_success' => '画像の削除に成功しました',
        'delete_error' => '画像の削除中にエラーが発生しました: :error',
        'delete_multiple_success' => ':count枚の画像の削除に成功しました',
        'delete_multiple_failed' => '削除に成功した画像がありません',
        'delete_failed_with_id' => 'ID :id の画像を削除できませんでした',
    ],

    'validation' => [
        'image' => [
            'required' => 'アップロードする画像を選択してください',
            'image' => 'ファイルは画像である必要があります',
            'mimes' => '無効な画像形式です。JPEG、JPG、PNG、GIF、WEBPのみ受け付けます',
            'max' => '画像の最大サイズは10MBです',
        ],
        'images' => [
            'required' => 'アップロードする画像を選択してください',
            'array' => '画像は配列である必要があります',
            'min' => '少なくとも1枚の画像が必要です',
            'max' => '1回のアップロードで最大10枚の画像',
        ],
        'images.*' => [
            'required' => '各画像は必須です',
            'image' => 'ファイルは画像である必要があります',
            'mimes' => '無効な画像形式です。JPEG、JPG、PNG、GIF、WEBPのみ受け付けます',
            'max' => '画像の最大サイズは10MBです',
        ],
        'folder' => [
            'required' => 'フォルダは必須です',
            'nullable' => 'フォルダは省略可能です',
            'string' => 'フォルダは文字列である必要があります',
            'max' => 'フォルダ名が長すぎます',
        ],
        'public_id' => [
            'required' => 'Public IDは必須です',
            'string' => 'Public IDは文字列である必要があります',
        ],
    ],
];
