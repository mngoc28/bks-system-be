<?php

return [
    'controller' => [
        'create_success' => '部屋にサービスを正常に追加しました。',
        'update_success' => '部屋のサービスが正常に更新されました。',
        'delete_success' => '部屋のサービスが正常に削除されました。',
        'validation_error' => '無効なデータです。',
    ],

    'validation' => [
        'id_required' => '部屋サービスIDは必須です。',
        'id_integer' => '部屋サービスIDは整数でなければなりません。',
        'id_exists' => '部屋サービスが存在しません。',

        'room_id_required' => '部屋IDは必須です。',
        'room_id_integer' => '部屋IDは整数でなければなりません。',
        'room_id_exists' => '部屋が存在しません。',

        'service_id_required' => 'サービスIDは必須です。',
        'service_id_integer' => 'サービスIDは整数でなければなりません。',
        'service_id_exists' => 'サービスが存在しません。',

        'is_included_required' => 'is_included フィールドは必須です。',
        'is_included_boolean' => 'is_included は真偽値でなければなりません。',
    ],
    'message' => [
        'create_success' => '部屋にサービスを正常に追加しました。',
        'create_error' => '部屋へのサービスの追加に失敗しました。',
        'update_success' => '部屋のサービスが正常に更新されました。',
        'update_error' => '部屋のサービスの更新に失敗しました。',
        'deleted_successfully' => '部屋のサービスが正常に削除されました。',
        'delete_failed' => '部屋のサービスの削除に失敗しました。',
    ],
    'atributes' => [
        'id' => '部屋サービスID',
        'room_id' => '部屋ID',
        'service_id' => 'サービスID',
        'is_included' => '含まれているかどうか',
    ],
];
