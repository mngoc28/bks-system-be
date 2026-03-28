<?php

return [
    'validation' => [
        'page' => [
            'integer' => 'ページ番号は整数でなければなりません。',
            'min' => 'ページ番号は1以上でなければなりません。',
        ],
        'per_page' => [
            'integer' => 'ページあたりのアイテム数は整数でなければなりません。',
            'min' => 'ページあたりのアイテム数は1以上でなければなりません。',
        ],
        'id' => [
            'required' => 'IDは必須です。',
            'exists' => 'IDが存在しません。',
            'integer' => 'IDは整数でなければなりません。',
        ],
        'published_at_start' => [
            'date' => '公開日は有効な日付でなければなりません。',
        ],
        'published_at_end' => [
            'date' => '公開日は有効な日付でなければなりません。',
        ],
        'status' => [
            'integer' => 'ステータスは整数でなければなりません。',
        ],
        'user_name' => [
            'string' => 'ユーザー名は文字列でなければなりません。',
            'required' => 'ユーザー名は必須です。',
        ],
        'title' => [
            'string' => 'タイトルは文字列でなければなりません。',
            'required' => 'タイトルは必須です。',
        ],
        'content' => [
            'string' => '内容は文字列でなければなりません。',
            'required' => '内容は必須です。',
        ],
        'sort_field' => [
            'string' => 'ソートフィールドは文字列でなければなりません。',
        ],
        'sort_direction' => [
            'string' => 'ソート方向は文字列でなければなりません。',
        ],
        'check_time' => [
            'error' => '開始時刻は終了時刻より前でなければなりません。',
        ],
        'user_id' => [
            'exists' => 'ユーザーが存在しません。',
            'integer' => 'ユーザーは整数でなければなりません。',
        ],
        'slug' => [
            'string' => 'Slugは文字列でなければなりません。',
            'unique' => 'Slugはすでに存在しています。',
        ],
        'summary' => [
            'string' => '要約は文字列でなければなりません。',
        ],
        'image_url' => [
            'string' => '画像URLは文字列でなければなりません。',
        ],
        'id_image_cloudinary' => [
            'string' => 'Cloudinary IDは文字列でなければなりません。',
        ],
        'published_at' => [
            'date' => '公開日は有効な日付でなければなりません。',
        ],
        'status' => [
            'integer' => 'ステータスは整数でなければなりません。',
        ],
    ],
    'messages' => [
        'fetch_success' => 'ニュースを取得しました。',
        'fetch_failed' => 'ニュースの取得に失敗しました。',
        'not_found' => 'ニュースが見つかりません。',
        'create_success' => 'ニュースを作成しました。',
        'create_failed' => 'ニュースの作成に失敗しました。',
        'update_success' => 'ニュースを更新しました。',
        'update_failed' => 'ニュースの更新に失敗しました。',
        'delete_success' => 'ニュースを削除しました。',
        'delete_failed' => 'ニュースの削除に失敗しました。',
        'get_latest_news_success' => '最新ニュースを取得しました。',
        'get_latest_news_failed' => '最新ニュースの取得に失敗しました。',
    ]
];
