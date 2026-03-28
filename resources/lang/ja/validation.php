<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted' => ':attribute フィールドは承認されなければなりません。',
    'accepted_if' => ':attribute フィールドは :other が :value の場合に承認されなければなりません。',
    'active_url' => ':attribute フィールドは有効な URL ではありません。',
    'after' => ':attribute フィールドは :date より後でなければなりません。',
    'after_or_equal' => ':attribute フィールドは :date より後か同じでなければなりません。',
    'alpha' => ':attribute フィールドは英字のみでなければなりません。',
    'alpha_dash' => ':attribute フィールドは英字、数字、ダッシュのみでなければなりません。',
    'alpha_num' => ':attribute フィールドは英字と数字のみでなければなりません。',
    'array' => ':attribute フィールドは配列でなければなりません。',
    'before' => ':attribute フィールドは :date より前でなければなりません。',
    'before_or_equal' => ':attribute フィールドは :date より前か同じでなければなりません。',
    'between' => [
        'numeric' => ':attribute フィールドは :min から :max の間でなければなりません。',
        'file' => ':attribute フィールドは :min から :max kB の間でなければなりません。',
        'string' => ':attribute フィールドは :min から :max 文字の間でなければなりません。',
        'array' => ':attribute フィールドは :min から :max 要素の間でなければなりません。',
    ],
    'boolean' => ':attribute フィールドは true または false でなければなりません。',
    'confirmed' => ':attribute フィールドの確認が一致しません。',
    'current_password' => '現在のパスワードが正しくありません。',
    'date' => ':attribute フィールドは日付の形式ではありません。',
    'date_equals' => ':attribute フィールドは :date と同じでなければなりません。',
    'date_format' => ':attribute フィールドは :format の形式ではありません。',
    'declined' => ':attribute フィールドは拒否されなければなりません。',
    'declined_if' => ':attribute フィールドは :other が :value の場合に拒否されなければなりません。',
    'different' => ':attribute フィールドと :other フィールドは異なる値でなければなりません。',
    'digits' => ':attribute フィールドは :digits 桁でなければなりません。',
    'digits_between' => ':attribute フィールドは :min から :max 桁の間でなければなりません。',
    'dimensions' => ':attribute フィールドは有効な画像サイズではありません。',
    'distinct' => ':attribute フィールドは重複した値を含んでいます。',
    'email' => ':attribute フィールドは有効なメールアドレスではありません。',
    'ends_with' => ':attribute フィールドは :values のいずれかで終わっていなければなりません。',
    'enum' => ':attribute フィールドは有効な値ではありません。',
    'exists' => ':attribute フィールドは有効な値ではありません。',
    'file' => ':attribute フィールドはファイルでなければなりません。',
    'filled' => ':attribute フィールドは空ではないです。',
    'gt' => [
        'numeric' => ':attribute フィールドは :value より大きくなければなりません。',
        'file' => ':attribute フィールドは :value kB より大きくなければなりません。',
        'string' => ':attribute フィールドは :value 文字より大きくなければなりません。',
        'array' => ':attribute フィールドは :value 要素より大きくなければなりません。',
    ],
    'gte' => [
        'numeric' => ':attribute フィールドは :value より大きいか同じでなければなりません。',
        'file' => ':attribute フィールドは :value kB より大きいか同じでなければなりません。',
        'string' => ':attribute フィールドは :value 文字より大きいか同じでなければなりません。',
        'array' => ':attribute フィールドは :value 要素より大きいか同じでなければなりません。',
    ],
    'image' => ':attribute フィールドは画像ファイルでなければなりません。',
    'in' => ':attribute フィールドは有効な値ではありません。',
    'in_array' => ':attribute フィールドは :other のいずれかでなければなりません。',
    'integer' => ':attribute フィールドは整数でなければなりません。',
    'ip' => ':attribute フィールドは IP アドレスでなければなりません。',
    'ipv4' => ':attribute フィールドは IPv4 アドレスでなければなりません。',
    'ipv6' => ':attribute フィールドは IPv6 アドレスでなければなりません。',
    'mac_address' => ':attribute フィールドは MAC アドレスでなければなりません。',
    'json' => ':attribute フィールドは JSON 文字列でなければなりません。',
    'lt' => [
        'numeric' => ':attribute フィールドは :value より小さくなければなりません。',
        'file' => ':attribute フィールドは :value kB より小さくなければなりません。',
        'string' => ':attribute フィールドは :value 文字より小さくなければなりません。',
        'array' => ':attribute フィールドは :value 要素より小さくなければなりません。',
    ],
    'lte' => [
        'numeric' => ':attribute フィールドは :value より小さいか同じでなければなりません。',
        'file' => ':attribute フィールドは :value kB より小さいか同じでなければなりません。',
        'string' => ':attribute フィールドは :value 文字より小さいか同じでなければなりません。',
        'array' => ':attribute フィールドは :value 要素より小さいか同じでなければなりません。',
    ],
    'max' => [
        'numeric' => ':attribute フィールドは :max より小さいか同じでなければなりません。',
        'file' => ':attribute フィールドは :max kB より小さいか同じでなければなりません。',
        'string' => ':attribute フィールドは :max 文字より小さいか同じでなければなりません。',
        'array' => ':attribute フィールドは :max 要素より小さいか同じでなければなりません。',
    ],
    'mimes' => ':attribute フィールドは :values のいずれかの形式でなければなりません。',
    'mimetypes' => ':attribute フィールドは :values のいずれかの形式でなければなりません。',
    'min' => [
        'numeric' => ':attribute フィールドは :min より大きいか同じでなければなりません。',
        'file' => ':attribute フィールドは :min kB より大きいか同じでなければなりません。',
        'string' => ':attribute フィールドは :min 文字より大きいか同じでなければなりません。',
        'array' => ':attribute フィールドは :min 要素より大きいか同じでなければなりません。',
    ],
    'multiple_of' => ':attribute フィールドは :value の倍数でなければなりません。',
    'not_in' => ':attribute フィールドは有効な値ではありません。',
    'not_regex' => ':attribute フィールドは有効な形式ではありません。',
    'numeric' => ':attribute フィールドは数値でなければなりません。',
    'password' => 'パスワードが正しくありません。',
    'present' => ':attribute フィールドは必須です。',
    'prohibited' => ':attribute フィールドは禁止されています。',
    'prohibited_if' => ':attribute フィールドは :other が :value の場合に禁止されています。',
    'prohibited_unless' => ':attribute フィールドは :other が :values のいずれかの場合に禁止されています。',
    'prohibits' => ':attribute フィールドは :other が存在してはいけません。',
    'regex' => ':attribute フィールドは有効な形式ではありません。',
    'required' => ':attribute フィールドは必須です。',
    'required_if' => ':attribute フィールドは :other が :value の場合に必須です。',
    'required_unless' => ':attribute フィールドは :other が :values のいずれかの場合に必須です。',
    'required_with' => ':attribute フィールドは :values が存在する場合に必須です。',
    'required_with_all' => ':attribute フィールドは :values がすべて存在する場合に必須です。',
    'required_without' => ':attribute フィールドは :values が存在しない場合に必須です。',
    'required_without_all' => ':attribute フィールドは :values がすべて存在しない場合に必須です。',
    'same' => ':attribute フィールドと :other フィールドは同じ値でなければなりません。',
    'size' => [
        'numeric' => ':attribute フィールドは :size でなければなりません。',
        'file' => ':attribute フィールドは :size kB でなければなりません。',
        'string' => ':attribute フィールドは :size 文字でなければなりません。',
        'array' => ':attribute フィールドは :size 要素でなければなりません。',
    ],
    'starts_with' => ':attribute フィールドは :values のいずれかで始まっていなければなりません。',
    'string' => ':attribute フィールドは文字列でなければなりません。',
    'timezone' => ':attribute フィールドは有効なタイムゾーンではありません。',
    'unique' => ':attribute フィールドはすでに存在しています。',
    'uploaded' => ':attribute フィールドはアップロードに失敗しました。',
    'url' => ':attribute フィールドは有効な URL ではありません。',
    'uuid' => ':attribute フィールドは有効な UUID ではありません。',


    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'repassword' => [
            'same' => '確認パスワードは新しいパスワードと同じでなければなりません。',
        ],

        'password' => [
            'min' => 'パスワードは8文字以上でなければなりません。',
        ],

        'rent_fee_value_*' => [
            'between' => ':attribute フィールドは 0 から 999,999,999 の間でなければなりません。',
        ],

        'fuel_fee_value_*' => [
            'between' => ':attribute フィールドは 0 から 999,999,999 の間でなければなりません。',
        ],

        'cleaning_fee_value_*' => [
            'between' => ':attribute フィールドは 0 から 999,999,999 の間でなければなりません。',
        ],

        'cost.*' => [
            'between' => ':attribute フィールドは 0 から 999,999,999 の間でなければなりません。',
        ],

        'updated_costs.*.cost' => [
            'between' => ':attribute フィールドは 0 から 999,999,999 の間でなければなりません。',
        ],

        'added_costs.*.cost' => [
            'between' => ':attribute フィールドは 0 から 999,999,999 の間でなければなりません。',
        ],

        'cost' => [
            'between' => ':attribute フィールドは 0 から 999,999,999 の間でなければなりません。',
        ],

        'base_cost.*' => [
            'required_if' => ':attribute フィールドは使用状態が設定されている場合に必須です。',
        ],

        'other_cost.*' => [
            'required_if' => ':attribute フィールドは使用状態が設定されている場合に必須です。',
        ],

        'initial_cost.*' => [
            'required_if' => ':attribute フィールドは使用状態が設定されている場合に必須です。',
        ],

        'optional_cost.*' => [
            'required_if' => ':attribute フィールドは使用状態が設定されている場合に必須です。',
        ],
    ],


    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [
        'email_address' => 'メールアドレス',
    ],
];
