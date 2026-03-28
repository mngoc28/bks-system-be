<?php

return [
    'validation' => [
        'name' => [
            'required' => '都道府県名を入力してください。',
            'string' => '都道府県名は有効な文字列である必要があります。',
            'max' => '都道府県名は100文字以内で入力してください。',
            'unique' => 'この都道府県名は既に存在しています。',
        ],
        'name_en' => [
            'required' => '英語の都道府県名を入力してください。',
            'string' => '英語の都道府県名は有効な文字列である必要があります。',
            'max' => '英語の都道府県名は100文字以内で入力してください。',
        ],
        'id' => [
            'required' => '都道府県IDを入力してください。',
            'integer' => '都道府県IDは整数である必要があります。',
            'exists' => '指定された都道府県は存在しません。',
            'unique' => '指定された都道府県IDは既に使用されています。',
        ],
    ],
    'attributes' => [
        'name' => '都道府県名',
        'name_en' => '英語の都道府県名',
        'id' => '都道府県ID',
    ],
    'messages' => [
        'create_success_ja' => '都道府県が正常に作成されました。',
        'create_error_ja'   => '都道府県の作成に失敗しました。',
        'update_success_ja' => '都道府県が正常に更新されました。',
        'update_error_ja'   => '都道府県の更新に失敗しました。',
        'show_success_ja'   => '都道府県情報を正常に取得しました。',
        'not_found_ja'      => '都道府県が見つかりません。',
        'search_success_ja' => '都道府県リストを正常に取得しました。',
        'search_failed_ja'  => '都道府県リストの取得に失敗しました。',
        'delete_success_ja' => '都道府県が正常に削除されました。',
        'get_all_provinces_types_success_ja' => '都道府県の種類を正常に取得しました。',
        'get_all_provinces_types_failed_ja' => '都道府県の種類の取得に失敗しました。',
    ],
];
