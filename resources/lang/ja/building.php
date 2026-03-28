<?php

return [
    /*
        |--------------------------------------------------------------------------
            | Building Language Lines
            |--------------------------------------------------------------------------
            |
            | The following language lines are used during building operations for various
            | messages that we need to display to the user. You are free to modify
            | these language lines according to your application's requirements.
            |
            */
    'validation' => [
        'user_id'          => [
            'required' => 'ユーザーIDは必須です。',
            'integer'  => 'ユーザーIDは整数でなければなりません。',
            'exists'   => '選択したユーザーは存在しません。',
        ],
        'province_id'      => [
            'required' => '都道府県IDは必須です。',
            'integer'  => '都道府県IDは整数でなければなりません。',
            'exists'   => '選択した都道府県は存在しません。',
        ],
        'ward_id'          => [
            'required' => '区/市町村IDは必須です。',
            'integer'  => '区/市町村IDは整数でなければなりません。',
            'exists'   => '選択した区/市町村は存在しません。',
        ],
        'name'             => [
            'required' => '建物名は必須です。',
            'max'      => '建物名は255文字以内で入力してください。',
            'unique'   => 'この建物名はすでに存在します。',
            'string'   => '建物名は有効な文字列でなければなりません。',
        ],
        'address_detail'   => [
            'max'    => '詳細住所は255文字以内で入力してください。',
            'string' => '詳細住所は有効な文字列でなければなりません。',
        ],
        'number_of_floors' => [
            'integer' => '階数は整数でなければなりません。',
            'min'     => '階数は1以上でなければなりません。',
        ],
        'number_of_units'  => [
            'integer' => '部屋数は整数でなければなりません。',
            'min'     => '部屋数は0以上でなければなりません。',
        ],
        'year_built'       => [
            'integer' => '建設年は整数でなければなりません。',
            'min'     => '建設年は1900年以上でなければなりません。',
            'max'     => '建設年は' . (date('Y') + 10) . '年以下でなければなりません。',
        ],
        'building_type'    => [
            'integer' => '建物タイプは整数でなければなりません。',
            'in'      => '建物タイプは1、2、3、4、5、6、7、8、9のいずれかでなければなりません。',
        ],
        'area'             => [
            'numeric' => '面積は数値でなければなりません。',
            'min'     => '面積は0以上でなければなりません。',
        ],
        'description'      => [
            'string' => '説明は有効な文字列でなければなりません。',
        ],
        'created_by'       => [
            'integer' => '作成者IDは整数でなければなりません。',
            'exists'  => '選択した作成者は存在しません。',
        ],
        'updated_by'       => [
            'integer' => '更新者IDは整数でなければなりません。',
            'exists'  => '選択した更新者は存在しません。',
        ],
        'id'               => [
            'required'     => '建物IDは必須です。',
            'integer'      => '建物IDは整数でなければなりません。',
            'exists'       => '指定された建物IDは存在しません。',
            'has_rooms'    => '部屋がある建物は削除できません。',
            'has_bookings' => '予約がある建物は削除できません。',
        ],
    ],
    'attributes' => [
        'user_id'          => 'ユーザーID',
        'province_id'      => '都道府県ID',
        'ward_id'          => '区/市町村ID',
        'name'             => '建物名',
        'address_detail'   => '詳細住所',
        'number_of_floors' => '階数',
        'number_of_units'  => '部屋数',
        'year_built'       => '建設年',
        'building_type'    => '建物タイプ',
        'area'             => '面積',
        'description'      => '説明',
        'created_by'       => '作成者',
        'updated_by'       => '更新者',
        'id'               => '建物ID',
    ],
    'messages'   => [
        'retrieved_successfully' => '建物リストを正常に取得しました。',
        'retrieved_failed'       => '建物リストの取得に失敗しました。',
        'found_successfully'     => '建物を正常に取得しました。',
        'not_found'              => '建物が見つかりません。',
        'find_failed'            => '建物の取得に失敗しました。',
        'created_successfully'   => '建物を正常に作成しました。',
        'create_failed'          => '建物の作成に失敗しました。',
        'updated_successfully'   => '建物を正常に更新しました。',
        'update_failed'          => '建物の更新に失敗しました。',
        'deleted_successfully'   => '建物を正常に削除しました。',
        'delete_failed'          => '建物の削除に失敗しました。',
        'bookings_retrieved_successfully' => 'この建物の予約リストを正常に取得しました。',
        'bookings_retrieved_failed'       => 'この建物の予約リストの取得に失敗しました。',
        'buildings_types_retrieved_successfully' => '建物タイプを正常に取得しました。',
        'buildings_types_retrieved_failed'       => '建物タイプの取得に失敗しました。',
        'all_buildings_bookings_count_retrieved_successfully' => 'すべての建物の予約数を正常に取得しました。',
        'all_buildings_bookings_count_retrieved_failed'       => 'すべての建物の予約数の取得に失敗しました。',
    ],
];
