<?php

return [
    /*
        |--------------------------------------------------------------------------
            | Room Language Lines
            |--------------------------------------------------------------------------
            |
            | The following language lines are used during room operations for various
            | messages that we need to display to the user. You are free to modify
            | these language lines according to your application's requirements.
            |
            */
    'validation' => [
        'building_id' => [
            'exists'   => '選択した建物は存在しません',
            'integer'  => '建物IDは整数でなければなりません',
            'required' => '建物IDは必須です',
        ],
        'title' => [
            'required' => '部屋のタイトルは必須です',
            'string'   => 'タイトルは文字列でなければなりません',
            'max'      => 'タイトルは100文字以内でなければなりません',
        ],
        'room_number' => [
            'integer'  => '部屋番号は整数でなければなりません',
            'numeric'  => '部屋番号は数値でなければなりません',
            'required' => '部屋番号は必須です',
        ],
        'deposit' => [
            'numeric' => '敷金は数値でなければなりません',
            'min'     => '敷金は0以上でなければなりません',
        ],
        'floor_number' => [
            'required' => '階数は必須です',
            'integer'  => '階数は整数でなければなりません',
            'min'      => '階数は0以上でなければなりません',
        ],
        'people' => [
            'required' => '収容人数は必須です',
            'integer'  => '収容人数は整数でなければなりません',
            'min'      => '収容人数は1以上でなければなりません',
        ],
        'room_type' => [
            'required' => '部屋タイプは必須です',
            'in'       => '部屋タイプは1、2、または3でなければなりません',
        ],
        'price_min'   => [
            'numeric' => '部屋の価格は数値でなければなりません',
            'min'     => '部屋の価格は0以上でなければなりません',
        ],
        'price_max'   => [
            'numeric' => '部屋の価格は数値でなければなりません',
            'min'     => '部屋の価格は0以上でなければなりません',
        ],
        'area_min'    => [
            'integer' => '最小面積は整数でなければなりません',
            'min'     => '最小面積は1以上でなければなりません',
        ],
        'area_max'    => [
            'integer' => '最大面積は整数でなければなりません',
            'min'     => '最大面積は1以上でなければなりません',
        ],
        'area'        => [
            'required' => '面積は必須です',
            'integer'  => '面積は整数でなければなりません',
            'min'      => '面積は1以上でなければなりません',
        ],
        'price'       => [
            'required' => '価格は必須です',
            'numeric'  => '価格は数値でなければなりません',
            'min'      => '価格は0以上でなければなりません',
        ],
        'status'      => [
            'required' => 'ステータスは必須です',
            'in'       => 'ステータスは0または1でなければなりません',
        ],
        'description' => [
            'string' => '説明は有効な文字列でなければなりません',
            'max'    => '説明は255文字以内でなければなりません',
        ],
        'images' => [
            'required' => '画像は必須です',
            'array'    => '画像は配列でなければなりません',
            'min'      => '少なくとも1つの画像が必要です',
        ],
        'images.*.image_url' => [
            'required' => '画像URLは必須です',
            'url'      => '画像URLが無効です',
            'max'      => '画像URLは255文字以内でなければなりません',
        ],
        'images.*.image_type' => [
            'required' => '画像タイプは必須です',
            'integer'  => '画像タイプは整数でなければなりません',
            'between'  => '画像タイプは0から5の間でなければなりません',
        ],
        'images.*.sort' => [
            'required' => 'ソート順は必須です',
            'integer'  => 'ソート順は整数でなければなりません',
            'min'      => 'ソート順は1以上でなければなりません',
        ],
        'amenities' => [
            'required' => '設備は必須です',
            'array'    => '設備は配列でなければなりません',
            'min'      => '少なくとも1つの設備が必要です',
        ],
        'amenities.*' => [
            'required' => '設備IDは必須です',
            'integer'  => '設備IDは整数でなければなりません',
            'exists'   => '設備IDが存在しません',
        ],
        'services' => [
            'required' => 'サービスは必須です',
            'array'    => 'サービスは配列でなければなりません',
            'min'      => '少なくとも1つのサービスが必要です',
        ],
        'services.*' => [
            'required' => 'サービスIDは必須です',
            'integer'  => 'サービスIDは整数でなければなりません',
            'exists'   => 'サービスIDが存在しません',
        ],
        'prices' => [
            'required' => '料金は必須です',
            'array'    => '料金は配列でなければなりません',
            'min'      => '少なくとも1つの料金が必要です',
            'price_package_id' => [
            'required' => '価格パッケージIDは必須です',
            'integer'  => '価格パッケージIDは整数でなければなりません',
            'exists'   => '価格パッケージIDが存在しません',
            ],
            'unit' => [
            'required' => '単位は必須です',
            'string'   => '単位は文字列でなければなりません',
            'in'       => '単位は「日」または「月」でなければなりません',
            ],
            'unit_price' => [
            'required' => '単価は必須です',
            'numeric'  => '単価は数値でなければなりません',
            'min'      => '単価は0以上でなければなりません',
            ],
        ],
    ],
    'attributes' => [
        'building_id' => '建物ID',
        'title'       => 'タイトル',
        'room_number' => '部屋番号',
        'deposit'     => '敷金',
        'floor_number'  => '階数',
        'people'      => '収容人数',
        'room_type'   => '部屋タイプ',
        'price_min'   => '最低価格',
        'price_max'   => '最高価格',
        'area_min'    => '最小面積',
        'area_max'    => '最大面積',
        'area'        => '面積',
        'price'       => '価格',
        'status'      => 'ステータス',
        'description' => '説明',
        'id'          => '部屋ID',
        'images'      => '画像',
        'images.*.image_url' => '画像URL',
        'images.*.image_type' => '画像タイプ',
        'images.*.sort' => 'ソート順',
        'amenities'   => '設備',
        'amenities.*' => '設備ID',
        'services'    => 'サービス',
        'services.*'  => 'サービスID',
        'prices'      => '料金',
        'prices.*.price_package_id' => '価格パッケージID',
        'prices.*.unit'             => '単位',
        'prices.*.unit_price'       => '単価',
    ],
    'messages'   => [
        'retrieved_successfully' => '部屋の一覧を正常に取得しました',
        'retrieved_failed'       => '部屋の一覧を取得できませんでした',
        'found_successfully'     => '部屋を正常に取得しました',
        'not_found'              => '部屋が見つかりません',
        'find_failed'            => '部屋を取得できませんでした',
        'created_successfully'   => '部屋を正常に作成しました',
        'create_failed'          => '部屋を作成できませんでした',
        'save_prices_failed'     => '部屋の料金を保存できませんでした',
        'save_images_failed'     => '部屋の画像を保存できませんでした',
        'save_amenities_failed'  => '部屋の設備を保存できませんでした',
        'updated_successfully'   => '部屋を正常に更新しました',
        'update_failed'          => '部屋を更新できませんでした',
        'deleted_successfully'   => '部屋を正常に削除しました',
        'delete_failed'          => '部屋を削除できませんでした',
    ],
];
