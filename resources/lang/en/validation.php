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

    'accepted' => ':attributeを承認してください。',
    'accepted_if' => ':otherが:valueの場合、:attributeを承認してください。',
    'active_url' => ':attributeは有効なURLではありません。',
    'after' => ':attributeは:date以降の日付である必要があります。',
    'after_or_equal' => ':attributeは:date以降の日付であるか、同じ日付である必要があります。',
    'alpha' => ':attributeにはアルファベットのみを含めることができます。',
    'alpha_dash' => ':attributeにはアルファベット、数字、ダッシュ、アンダースコアのみを含めることができます。',
    'alpha_num' => ':attributeにはアルファベットと数字のみを含めることができます。',
    'array' => ':attributeは配列でなければなりません。',
    'before' => ':attributeは:dateより前の日付でなければなりません。',
    'before_or_equal' => ':attributeは:dateより前の日付であるか、同じ日付である必要があります。',
    'between' => [
        'numeric' => ':attributeは:min〜:maxの間でなければなりません。',
        'file' => ':attributeは:min〜:maxキロバイトの間でなければなりません。',
        'string' => ':attributeは:min〜:max文字の間でなければなりません。',
        'array' => ':attributeは:min〜:max個のアイテムを持っている必要があります。',
    ],
    'boolean' => ':attributeフィールドはtrueまたはfalseでなければなりません。',
    'confirmed' => ':attributeの確認が一致しません。',
    'current_password' => 'パスワードが正しくありません。',
    'date' => ':attributeは有効な日付ではありません。',
    'date_equals' => ':attributeは:dateと等しい日付でなければなりません。',
    'date_format' => ':attributeが:formatと一致しません。',
    'declined' => ':attributeを拒否する必要があります。',
    'declined_if' => ':otherが:valueの場合、:attributeを拒否する必要があります。',
    'different' => ':attributeと:otherは異なる必要があります。',
    'digits' => ':attributeは:digits桁でなければなりません。',
    'digits_between' => ':attributeは:min〜:max桁の間でなければなりません。',
    'dimensions' => ':attributeには無効な画像の寸法があります。',
    'distinct' => ':attributeフィールドに重複した値があります。',
    'email' => ':attributeは有効なメールアドレスでなければなりません。',
    'ends_with' => ':attributeは次のいずれかで終わらなければなりません：:values。',
    'enum' => '選択した:attributeは無効です。',
    'exists' => '選択した:attributeは無効です。',
    'file' => ':attributeはファイルでなければなりません。',
    'filled' => ':attributeフィールドに値が必要です。',
    'gt' => [
        'numeric' => ':attributeは:valueより大きくなければなりません。',
        'file' => ':attributeは:valueキロバイトより大きくなければなりません。',
        'string' => ':attributeは:value文字より大きくなければなりません。',
        'array' => ':attributeは:value以上のアイテムを持っている必要があります。',
    ],
    'gte' => [
        'numeric' => ':attributeは:value以上でなければなりません。',
        'file' => ':attributeは:valueキロバイト以上でなければなりません。',
        'string' => ':attributeは:value文字以上でなければなりません。',
        'array' => ':attributeは:value個以上のアイテムを持つ必要があります。',
    ],
    'image' => ':attributeは画像でなければなりません。',
    'in' => '選択した:attributeは無効です。',
    'in_array' => ':attributeフィールドは:otherに存在しません。',
    'integer' => ':attributeは整数でなければなりません。',
    'ip' => ':attributeは有効なIPアドレスでなければなりません。',
    'ipv4' => ':attributeは有効なIPv4アドレスでなければなりません。',
    'ipv6' => ':attributeは有効なIPv6アドレスでなければなりません。',
    'mac_address' => ':attributeは有効なMACアドレスでなければなりません。',
    'json' => ':attributeは有効なJSON文字列でなければなりません。',
    'lt' => [
        'numeric' => ':attributeは:value未満でなければなりません。',
        'file' => ':attributeは:valueキロバイト未満でなければなりません。',
        'string' => ':attributeは:value文字未満でなければなりません。',
        'array' => ':attributeは:value個未満のアイテムを持つ必要があります。',
    ],
    'lte' => [
        'numeric' => ':attributeは:value以下でなければなりません。',
        'file' => ':attributeは:valueキロバイト以下でなければなりません。',
        'string' => ':attributeは:value文字以下でなければなりません。',
        'array' => ':attributeは:value個以上のアイテムを持つことはできません。',
    ],
    'max' => [
        'numeric' => ':attributeは:max以下でなければなりません。',
        'file' => ':attributeは:maxキロバイト以下でなければなりません。',
        'string' => ':attributeは:max文字以下でなければなりません。',
        'array' => ':attributeは:max個以下のアイテムを持つことはできません。',
    ],
    'mimes' => ':attributeは次のタイプのファイルでなければなりません: :values。',
    'mimetypes' => ':attributeは次のタイプのファイルでなければなりません: :values。',
    'min' => [
        'numeric' => ':attributeは少なくとも:minでなければなりません。',
        'file' => ':attributeは少なくとも:minキロバイトでなければなりません。',
        'string' => ':attributeは少なくとも:min文字でなければなりません。',
        'array' => ':attributeは少なくとも:min個のアイテムを持つ必要があります。',
    ],
    'multiple_of' => ':attributeは:valueの倍数でなければなりません。',
    'not_in' => '選択した:attributeは無効です。',
    'not_regex' => ':attributeの形式が無効です。',
    'numeric' => ':attributeは数字でなければなりません。',
    'password' => 'パスワードが正しくありません。',
    'present' => ':attributeフィールドは存在する必要があります。',
    'prohibited' => ':attributeフィールドは禁止されています。',
    'prohibited_if' => ':otherが:valueの場合、:attributeフィールドは禁止されています。',
    'prohibited_unless' => ':otherが:valuesにない場合、:attributeフィールドは禁止されています。',
    'prohibits' => ':attributeフィールドは、:otherの存在を禁止します。',
    'regex' => ':attributeの形式が無効です。',
    'required' => ':attributeは必須の項目です。',
    'required_if' => ':otherが:valueの場合、:attributeは必須の項目です。',
    'required_unless' => ':otherが:valuesにない場合、:attributeフィールドは必須です。',
    'required_with' => ':valuesが存在する場合、:attributeフィールドは必須です。',
    'required_with_all' => ':valuesがすべて存在する場合、:attributeフィールドは必須です。',
    'required_without' => ':valuesが存在しない場合、:attributeフィールドは必須です。',
    'required_without_all' => ':valuesがすべて存在しない場合、:attributeフィールドは必須です。',
    'same' => ':attributeと:otherは一致する必要があります。',
    'size' => [
        'numeric' => ':attributeは:sizeでなければなりません。',
        'file' => ':attributeは:sizeキロバイトでなければなりません。',
        'string' => ':attributeは:size文字でなければなりません。',
        'array' => ':attributeは:size個のアイテムを含む必要があります。',
    ],
    'starts_with' => ':attributeは次のいずれかで始まる必要があります: :values。',
    'string' => ':attributeは文字列でなければなりません。',
    'timezone' => ':attributeは有効なタイムゾーンでなければなりません。',
    'unique' => ':attributeはすでに存在します。',
    'uploaded' => ':attributeのアップロードに失敗しました。',
    'url' => ':attributeは有効なURLでなければなりません。',
    'uuid' => ':attributeは有効なUUIDでなければなりません。',


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
            'same' => '確認パスワードは新しいパスワードと一致する必要があります。',
        ],

        'password' => [
            'min' => 'パスワードは8文字以上である必要があります。',
        ],

        'rent_fee_value_*' => [
            'between' => ':attributeは0〜999,999,999の間でなければなりません。',
        ],

        'fuel_fee_value_*' => [
            'between' => ':attributeは0〜999,999,999の間でなければなりません。',
        ],

        'cleaning_fee_value_*' => [
            'between' => ':attributeは0〜999,999,999の間でなければなりません。',
        ],

        'cost.*' => [
            'between' => ':attributeは0〜999,999,999の間でなければなりません。',
        ],

        'updated_costs.*.cost' => [
            'between' => ':attributeは0〜999,999,999の間でなければなりません。',
        ],

        'added_costs.*.cost' => [
            'between' => ':attributeは0〜999,999,999の間でなければなりません。',
        ],

        'cost' => [
            'between' => ':attributeは0〜999,999,999の間でなければなりません。',
        ],

        'base_cost.*' => [
            'required_if' => '使用状況が設定されている場合、:attributeは必須です。',
        ],

        'other_cost.*' => [
            'required_if' => '使用状況が設定されている場合、:attributeは必須です。',
        ],

        'initial_cost.*' => [
            'required_if' => '使用状況が設定されている場合、:attributeは必須です。',
        ],

        'optional_cost.*' => [
            'required_if' => '使用状況が設定されている場合、:attributeは必須です。',
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
